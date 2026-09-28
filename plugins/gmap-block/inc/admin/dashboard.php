<?php
/**
 * Gmap Block dashboard.
 *
 * Owns the admin page, its tabs and the Google Maps API key setting.
 * Gmap Pro (and any other add-on) extends it through the filters below.
 *
 * The free plugin renders its maps without a Google Maps API key, so every
 * API key surface here — the Settings tab, the option itself, the key test
 * harness and the status row — only appears once Gmap Pro is active.
 *
 * @since 1.3.0
 * @package GmapBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Gmap_Block_Dashboard' ) ) {

	/**
	 * Gmap Block Dashboard Class
	 *
	 * @since 1.3.0
	 * @package GmapBlock
	 */
	class Gmap_Block_Dashboard {

		/**
		 * Admin page slug.
		 */
		const MENU_SLUG = 'gmap-block';

		/**
		 * Settings group used by the settings tab.
		 */
		const OPTION_GROUP = 'gmap_block_settings';

		/**
		 * Option holding the Google Maps API key.
		 */
		const OPTION_API_KEY = 'gmap_block_api_key';

		/**
		 * Option holding the timestamp of the last API key save.
		 */
		const OPTION_API_KEY_SAVED = 'gmap_block_api_key_saved';

		/**
		 * Singleton instance.
		 *
		 * @var Gmap_Block_Dashboard|null
		 */
		private static $instance = null;

		/**
		 * Resolved page hook suffix.
		 *
		 * @var string
		 */
		private $hook = '';

		/**
		 * Get the singleton instance.
		 *
		 * @return Gmap_Block_Dashboard
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		private function __construct() {
			add_action( 'admin_menu', array( $this, 'register_menu' ) );
			add_action( 'admin_init', array( $this, 'maybe_render_key_test' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_init', array( $this, 'maybe_migrate_legacy_api_key' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			add_action( 'add_option_' . self::OPTION_API_KEY, array( $this, 'remember_save_time' ) );
			add_action( 'update_option_' . self::OPTION_API_KEY, array( $this, 'remember_save_time' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( GMAP__FILE__ ), array( $this, 'plugin_action_links' ) );
		}

		/**
		 * Register the admin page under Settings.
		 *
		 * @return void
		 */
		public function register_menu() {
			$this->hook = add_options_page(
				__( 'Gmap Block', 'gmap-block' ),
				__( 'Gmap Block', 'gmap-block' ),
				'manage_options',
				self::MENU_SLUG,
				array( $this, 'render' )
			);
		}

		/**
		 * Add a Dashboard shortcut to the plugin row.
		 *
		 * Points at the Settings tab when it exists, i.e. when Gmap Pro is
		 * active, and at the dashboard itself otherwise.
		 *
		 * @param array $links Existing action links.
		 * @return array
		 */
		public function plugin_action_links( $links ) {
			$has_settings = gmap_block_is_pro_active();

			$shortcut = sprintf(
				'<a href="%s">%s</a>',
				esc_url( gmap_block_dashboard_url( $has_settings ? 'settings' : 'overview' ) ),
				$has_settings ? esc_html__( 'Settings', 'gmap-block' ) : esc_html__( 'Dashboard', 'gmap-block' )
			);

			array_unshift( $links, $shortcut );

			return $links;
		}

		/**
		 * Render the isolated harness used by the "Test this key" button.
		 *
		 * Runs in a same-origin iframe rather than the settings page itself for
		 * two reasons: the Google Maps API can only be loaded once per browsing
		 * context, so a fresh iframe is what makes repeat tests possible, and a
		 * real same-origin document sends a real referrer, which is the only way
		 * to exercise a key's HTTP-referrer restriction.
		 *
		 * The key is NOT accepted from the query string — it arrives by
		 * postMessage after load, so a credential never lands in a URL, a server
		 * access log, or browser history.
		 *
		 * @return void
		 */
		public function maybe_render_key_test() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Presence check only; nonce verified immediately below.
			if ( empty( $_GET['gmap_key_test'] ) ) {
				return;
			}

			// No API key surface without Gmap Pro, so nothing to test.
			if ( ! gmap_block_is_pro_active() ) {
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( esc_html__( 'You are not allowed to do this.', 'gmap-block' ), '', array( 'response' => 403 ) );
			}

			check_admin_referer( 'gmap_block_key_test' );

			nocache_headers();
			header( 'Content-Type: text/html; charset=utf-8' );
			// Nothing here is user-supplied, but keep the frame inert regardless.
			header( 'X-Frame-Options: SAMEORIGIN' );

			require trailingslashit( GMAP_PLUGIN_DIR ) . 'inc/admin/views/key-test.php';
			exit;
		}

		/**
		 * URL of the key-test harness.
		 *
		 * @return string
		 */
		public function get_key_test_url() {
			return wp_nonce_url(
				add_query_arg( 'gmap_key_test', '1', gmap_block_dashboard_url( 'settings' ) ),
				'gmap_block_key_test'
			);
		}

		/**
		 * Register the Google Maps API key setting.
		 *
		 * Only registered alongside Gmap Pro — the free plugin has no Settings
		 * tab to save it from.
		 *
		 * @return void
		 */
		public function register_settings() {
			if ( ! gmap_block_is_pro_active() ) {
				return;
			}

			register_setting(
				self::OPTION_GROUP,
				self::OPTION_API_KEY,
				array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => '',
					'show_in_rest'      => false,
				)
			);
		}

		/**
		 * Copy the key Gmap Pro used to own into the free plugin's option.
		 *
		 * Runs once: after the copy the free option exists and the check
		 * short-circuits.
		 *
		 * @return void
		 */
		public function maybe_migrate_legacy_api_key() {
			if ( ! gmap_block_is_pro_active() ) {
				return;
			}

			if ( false !== get_option( self::OPTION_API_KEY, false ) ) {
				return;
			}

			$legacy = get_option( 'gmap_pro_api_key', '' );

			if ( ! is_string( $legacy ) || '' === trim( $legacy ) ) {
				return;
			}

			update_option( self::OPTION_API_KEY, sanitize_text_field( $legacy ) );
		}

		/**
		 * Store when the API key was last written.
		 *
		 * @return void
		 */
		public function remember_save_time() {
			update_option( self::OPTION_API_KEY_SAVED, time(), false );
		}

		/**
		 * Load the dashboard stylesheet and script on our page only.
		 *
		 * @param string $hook Current admin page hook suffix.
		 * @return void
		 */
		public function enqueue_assets( $hook ) {
			if ( $hook !== $this->hook ) {
				return;
			}

			wp_enqueue_style(
				'gmap-block-dashboard',
				trailingslashit( GMAP_URL_FILE ) . 'assets/admin/dashboard.css',
				array(),
				GMAP_VERSION
			);

			wp_enqueue_script(
				'gmap-block-dashboard',
				trailingslashit( GMAP_URL_FILE ) . 'assets/admin/dashboard.js',
				array(),
				GMAP_VERSION,
				true
			);

			/**
			 * Fires after the dashboard assets are enqueued.
			 *
			 * Add-ons can enqueue their own tab assets here.
			 *
			 * @since 1.3.0
			 * @param string $tab Current tab slug.
			 */
			do_action( 'gmap_block_dashboard_enqueue_assets', $this->current_tab() );
		}

		/**
		 * Registered dashboard tabs, sorted by priority.
		 *
		 * The Settings tab only holds the Google Maps API key, which the free
		 * plugin does not need, so it is registered for Gmap Pro only.
		 *
		 * @return array
		 */
		public function get_tabs() {
			$tabs = array(
				'overview' => array(
					'label'    => __( 'Overview', 'gmap-block' ),
					'icon'     => 'layout-dashboard',
					'priority' => 10,
					'callback' => array( $this, 'render_overview' ),
				),
			);

			if ( gmap_block_is_pro_active() ) {
				$tabs['settings'] = array(
					'label'    => __( 'Settings', 'gmap-block' ),
					'icon'     => 'settings',
					'priority' => 20,
					'callback' => array( $this, 'render_settings' ),
				);
			}

			/**
			 * Filters the dashboard tabs.
			 *
			 * Each tab is an array keyed by its slug:
			 *   label    (string)   Tab label.
			 *   icon     (string)   Icon name from Gmap_Block_Icons.
			 *   priority (int)      Sort order, lower shows first.
			 *   callback (callable) Renders the tab body.
			 *
			 * @since 1.3.0
			 * @param array $tabs Registered tabs.
			 */
			$tabs = apply_filters( 'gmap_block_dashboard_tabs', $tabs );

			$tabs = array_filter(
				(array) $tabs,
				static function ( $tab ) {
					return is_array( $tab ) && ! empty( $tab['label'] );
				}
			);

			uasort(
				$tabs,
				static function ( $a, $b ) {
					$a_priority = isset( $a['priority'] ) ? (int) $a['priority'] : 50;
					$b_priority = isset( $b['priority'] ) ? (int) $b['priority'] : 50;

					return $a_priority <=> $b_priority;
				}
			);

			return $tabs;
		}

		/**
		 * The tab currently being viewed.
		 *
		 * @return string
		 */
		public function current_tab() {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation state.
			$requested = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'overview';
			$tabs      = $this->get_tabs();

			return isset( $tabs[ $requested ] ) ? $requested : 'overview';
		}

		/**
		 * Plugin status shown in the sidebar card and its summary.
		 *
		 * @return array
		 */
		public function get_status() {
			$items = array(
				'blocks' => array(
					'priority' => 30,
					'ok'       => true,
					'label'    => __( 'Blocks registered in editor', 'gmap-block' ),
				),
			);

			// Free maps load without a key, so only Gmap Pro reports on one.
			if ( gmap_block_is_pro_active() ) {
				$has_key = gmap_block_has_api_key();

				$items['api-key'] = array(
					'priority' => 10,
					'ok'       => $has_key,
					'label'    => $has_key
						? __( 'Google Maps API key connected', 'gmap-block' )
						: __( 'Google Maps API key missing', 'gmap-block' ),
				);
			}

			/**
			 * Filters the rows of the plugin status card.
			 *
			 * Each row: [ 'ok' => bool, 'label' => string, 'priority' => int ].
			 *
			 * @since 1.3.0
			 * @param array $items Status rows.
			 */
			$items = apply_filters( 'gmap_block_dashboard_status_items', $items );

			$items = array_filter(
				(array) $items,
				static function ( $item ) {
					return is_array( $item ) && ! empty( $item['label'] );
				}
			);

			uasort(
				$items,
				static function ( $a, $b ) {
					$a_priority = isset( $a['priority'] ) ? (int) $a['priority'] : 50;
					$b_priority = isset( $b['priority'] ) ? (int) $b['priority'] : 50;

					return $a_priority <=> $b_priority;
				}
			);

			$all_good = true;
			foreach ( $items as $item ) {
				if ( empty( $item['ok'] ) ) {
					$all_good = false;
					break;
				}
			}

			$status = array(
				'ok'       => $all_good,
				'eyebrow'  => __( 'Plugin status', 'gmap-block' ),
				'pill'     => $all_good ? __( 'All good', 'gmap-block' ) : __( 'Action needed', 'gmap-block' ),
				'headline' => $all_good
					? __( 'Your maps are ready to publish.', 'gmap-block' )
					: __( 'A couple of steps left before your maps are ready.', 'gmap-block' ),
				'items'    => $items,
			);

			/**
			 * Filters the whole plugin status card payload.
			 *
			 * @since 1.3.0
			 * @param array $status Status card data.
			 */
			return apply_filters( 'gmap_block_dashboard_status', $status );
		}

		/**
		 * Version chips rendered in the top bar.
		 *
		 * @return array
		 */
		public function get_version_chips() {
			$chips = array(
				'free' => array(
					'priority' => 10,
					'icon'     => 'circle-dashed',
					/* translators: %s: Gmap Block version number. */
					'label'    => sprintf( __( 'Free v%s', 'gmap-block' ), GMAP_VERSION ),
					'accent'   => false,
				),
			);

			/**
			 * Filters the version chips in the dashboard top bar.
			 *
			 * Each chip: [ 'icon' => string, 'label' => string, 'accent' => bool, 'priority' => int ].
			 *
			 * @since 1.3.0
			 * @param array $chips Version chips.
			 */
			$chips = apply_filters( 'gmap_block_dashboard_version_chips', $chips );

			uasort(
				$chips,
				static function ( $a, $b ) {
					$a_priority = isset( $a['priority'] ) ? (int) $a['priority'] : 50;
					$b_priority = isset( $b['priority'] ) ? (int) $b['priority'] : 50;

					return $a_priority <=> $b_priority;
				}
			);

			return $chips;
		}

		/**
		 * The second card on the overview screen.
		 *
		 * Free sites see a "Get Pro" call to action; Gmap Pro swaps it for the
		 * license card through the filter.
		 *
		 * @return array
		 */
		public function get_license_card() {
			if ( gmap_block_is_pro_active() ) {
				$card = array(
					'icon'         => 'badge-check',
					'title'        => __( 'Pro License', 'gmap-block' ),
					'desc'         => __( 'Activate your license to receive automatic updates and support.', 'gmap-block' ),
					'state'        => 'neutral',
					'state_label'  => __( 'Installed', 'gmap-block' ),
					'button_label' => __( 'Open License', 'gmap-block' ),
					'button_url'   => gmap_block_dashboard_url( 'license' ),
					'button_blank' => false,
				);
			} else {
				$card = array(
					'icon'         => 'crown',
					'title'        => __( 'Gmap Block Pro', 'gmap-block' ),
					'desc'         => __( 'Unlock advanced maps, multiple markers, SVG maps, directions and more.', 'gmap-block' ),
					'state'        => 'muted',
					'state_label'  => __( 'Not installed', 'gmap-block' ),
					'button_label' => __( 'Get Pro', 'gmap-block' ),
					'button_url'   => gmap_block_get_pro_url(),
					'button_blank' => true,
				);
			}

			/**
			 * Filters the license / upgrade card on the overview tab.
			 *
			 * @since 1.3.0
			 * @param array $card Card data.
			 */
			return apply_filters( 'gmap_block_dashboard_license_card', $card );
		}

		/**
		 * Cross-sell products listed in the sidebar.
		 *
		 * @return array
		 */
		public function get_products() {
			$products = array(
				array(
					'icon' => 'gallery-horizontal-end',
					'name' => __( 'GutSlider', 'gmap-block' ),
					'desc' => __( 'Create stunning, responsive sliders with ease.', 'gmap-block' ),
					'url'  => 'https://www.gutslider.com',
				),
				array(
					'icon' => 'chevrons-up-down',
					'name' => __( 'Easy Accordion Block', 'gmap-block' ),
					'desc' => __( 'Collapsible content sections for Gutenberg.', 'gmap-block' ),
					'url'  => 'https://accordion.gutenbergkits.com',
				),
				array(
					'icon' => 'layout-template',
					'name' => __( 'GutenLayouts', 'gmap-block' ),
					'desc' => __( 'Ready-made patterns and page templates.', 'gmap-block' ),
					'url'  => 'https://gutenlayouts.com',
				),
			);

			/**
			 * Filters the "More from Gutenbergkits" list.
			 *
			 * @since 1.3.0
			 * @param array $products Product rows.
			 */
			return apply_filters( 'gmap_block_dashboard_products', $products );
		}

		/**
		 * External links used across the dashboard.
		 *
		 * @return array
		 */
		public function get_links() {
			$links = array(
				'docs'      => 'https://gmap.gutenbergkits.com/docs/',
				'tutorials' => 'https://gmap.gutenbergkits.com/tutorials/',
				'website'   => 'https://gmap.gutenbergkits.com/',
				'support'   => 'https://support.gutenbergkits.com/',
				'review'    => 'https://wordpress.org/support/plugin/gmap-block/reviews/#new-post',
				'api_guide' => 'https://gmap.gutenbergkits.com/how-to-create-an-api-key-for-google-maps/',
				'console'   => 'https://console.cloud.google.com/google/maps-apis/start',
				'video_id'  => '1CsZPtvvfWo',
			);

			/**
			 * Filters the outbound links used by the dashboard.
			 *
			 * @since 1.3.0
			 * @param array $links Link map.
			 */
			return apply_filters( 'gmap_block_dashboard_links', $links );
		}

		/**
		 * Render the dashboard shell.
		 *
		 * @return void
		 */
		public function render() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$tabs        = $this->get_tabs();
			$current_tab = $this->current_tab();

			require trailingslashit( GMAP_PLUGIN_DIR ) . 'inc/admin/views/shell.php';
		}

		/**
		 * Render the overview tab.
		 *
		 * @return void
		 */
		public function render_overview() {
			require trailingslashit( GMAP_PLUGIN_DIR ) . 'inc/admin/views/overview.php';
		}

		/**
		 * Render the settings tab.
		 *
		 * @return void
		 */
		public function render_settings() {
			require trailingslashit( GMAP_PLUGIN_DIR ) . 'inc/admin/views/settings.php';
		}

		/**
		 * Render the sidebar shared by the overview and settings tabs.
		 *
		 * @param array $parts Sections to include: status, review, products.
		 * @return void
		 */
		public function render_sidebar( $parts = array( 'status', 'review', 'products' ) ) {
			$status   = $this->get_status();
			$products = $this->get_products();
			$links    = $this->get_links();

			require trailingslashit( GMAP_PLUGIN_DIR ) . 'inc/admin/views/sidebar.php';
		}

		/**
		 * Human readable "last saved" label for the API key.
		 *
		 * @return string
		 */
		public function get_last_saved_label() {
			$saved = (int) get_option( self::OPTION_API_KEY_SAVED, 0 );

			if ( ! $saved ) {
				return __( 'Not saved yet', 'gmap-block' );
			}

			/* translators: %s: Human readable time difference, e.g. "2 hours". */
			return sprintf( __( 'Last saved %s ago', 'gmap-block' ), human_time_diff( $saved, time() ) );
		}
	}
}
