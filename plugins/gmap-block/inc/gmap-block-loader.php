<?php
/**
 * Gmap Blocks Main Loader
 *
 * @since 1.0.0
 * @package GmapBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Gmap_Block_Loader' ) ) {

	/**
	 * Gmap Blocks Loader Class
	 *
	 * @since 1.0.0
	 * @package GmapBlock
	 */

	class Gmap_Block_Loader {

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function __construct() {
			$this->includes();
		}

		/**
		 * Include Files
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function includes() {
			require_once trailingslashit( GMAP_PLUGIN_DIR ) . '/inc/classes/register-blocks.php';
			require_once trailingslashit( GMAP_PLUGIN_DIR ) . '/inc/classes/enqueue-assets.php';
			require_once trailingslashit( GMAP_PLUGIN_DIR ) . '/inc/classes/dynamic-style.php';

			// Dashboard. The helpers are loaded everywhere so add-ons and the
			// frontend can read the API key; the screen itself is admin only.
			require_once trailingslashit( GMAP_PLUGIN_DIR ) . '/inc/admin/icons.php';
			require_once trailingslashit( GMAP_PLUGIN_DIR ) . '/inc/admin/functions.php';
			require_once trailingslashit( GMAP_PLUGIN_DIR ) . '/inc/admin/dashboard.php';

			if ( is_admin() ) {
				Gmap_Block_Dashboard::instance();
			}
		}
	}
	
	new Gmap_Block_Loader(); // Initialize the loader class.
}
