<?php
/**
 * Overview tab.
 *
 * @since 1.3.0
 * @package GmapBlock
 *
 * @var Gmap_Block_Dashboard $this Dashboard instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gmapb_links   = $this->get_links();
$gmapb_has_pro = gmap_block_is_pro_active();

$gmapb_cards = array();

// The free plugin renders its maps without a key, so the API key card only
// shows up alongside Gmap Pro — where the Settings tab it links to exists.
if ( $gmapb_has_pro ) {
	$gmapb_has_key = gmap_block_has_api_key();

	$gmapb_cards[] = array(
		'icon'         => 'key-round',
		'title'        => __( 'Google Maps API Key', 'gmap-block' ),
		'desc'         => __( 'Add your key to unlock advanced maps, custom markers and styles.', 'gmap-block' ),
		'state'        => $gmapb_has_key ? 'good' : 'muted',
		'state_label'  => $gmapb_has_key ? __( 'Connected', 'gmap-block' ) : __( 'Not set', 'gmap-block' ),
		'button_label' => __( 'Open Settings', 'gmap-block' ),
		'button_url'   => gmap_block_dashboard_url( 'settings' ),
		'button_blank' => false,
	);
}

$gmapb_cards[] = $this->get_license_card();

$gmapb_quick_links = array(
	array(
		'icon' => 'book-open',
		'name' => __( 'Documentation', 'gmap-block' ),
		'desc' => __( 'Guides and block reference', 'gmap-block' ),
		'url'  => $gmapb_links['docs'],
	),
	array(
		'icon' => 'youtube',
		'name' => __( 'Video tutorials', 'gmap-block' ),
		'desc' => __( 'Watch the full playlist', 'gmap-block' ),
		'url'  => $gmapb_links['tutorials'],
	),
	array(
		'icon' => 'globe',
		'name' => __( 'Visit website', 'gmap-block' ),
		'desc' => __( 'Demos, pricing and add-ons', 'gmap-block' ),
		'url'  => $gmapb_links['website'],
	),
);
?>

<div class="gmapb-page-head">
	<h1 class="gmapb-page-title">
		<?php
		echo $gmapb_has_pro
			? esc_html__( 'Welcome to Gmap Block Pro', 'gmap-block' )
			: esc_html__( 'Welcome to Gmap Block', 'gmap-block' );
		?>
	</h1>
	<p class="gmapb-page-sub"><?php esc_html_e( 'Everything you need to build interactive Google Maps inside the block editor.', 'gmap-block' ); ?></p>
</div>

<div class="gmapb-columns">
	<div class="gmapb-main">

		<div class="gmapb-connect-cards">
			<?php foreach ( $gmapb_cards as $gmapb_card ) : ?>
				<div class="gmapb-card gmapb-connect-card">
					<div class="gmapb-connect-card__top">
						<span class="gmapb-iconbox"><?php Gmap_Block_Icons::render( $gmapb_card['icon'], 19 ); ?></span>
						<span class="gmapb-state gmapb-state--<?php echo esc_attr( $gmapb_card['state'] ); ?>">
							<i class="gmapb-dot" aria-hidden="true"></i>
							<?php echo esc_html( $gmapb_card['state_label'] ); ?>
						</span>
					</div>

					<div class="gmapb-connect-card__copy">
						<h2 class="gmapb-card-title"><?php echo esc_html( $gmapb_card['title'] ); ?></h2>
						<p class="gmapb-card-desc"><?php echo esc_html( $gmapb_card['desc'] ); ?></p>
					</div>

					<a
						class="gmapb-btn gmapb-btn--navy"
						href="<?php echo esc_url( $gmapb_card['button_url'] ); ?>"
						<?php echo ! empty( $gmapb_card['button_blank'] ) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
					>
						<span><?php echo esc_html( $gmapb_card['button_label'] ); ?></span>
						<?php Gmap_Block_Icons::render( empty( $gmapb_card['button_blank'] ) ? 'arrow-right' : 'arrow-up-right', 15 ); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="gmapb-getting-started">
			<div class="gmapb-section-head">
				<h2 class="gmapb-section-title"><?php esc_html_e( 'Getting started', 'gmap-block' ); ?></h2>
				<a class="gmapb-link" href="<?php echo esc_url( $gmapb_links['tutorials'] ); ?>" target="_blank" rel="noopener noreferrer">
					<span><?php esc_html_e( 'All tutorials', 'gmap-block' ); ?></span>
					<?php Gmap_Block_Icons::render( 'arrow-up-right', 14 ); ?>
				</a>
			</div>

			<div class="gmapb-card gmapb-tutorial">
				<button
					type="button"
					class="gmapb-tutorial__thumb"
					data-gmapb-video="<?php echo esc_attr( $gmapb_links['video_id'] ); ?>"
					style="background-image: linear-gradient(90deg, rgba(15,26,77,.4) 0%, rgba(15,26,77,.85) 100%), url('<?php echo esc_url( 'https://i.ytimg.com/vi/' . $gmapb_links['video_id'] . '/maxresdefault.jpg' ); ?>');"
				>
					<span class="screen-reader-text"><?php esc_html_e( 'Play the getting started video', 'gmap-block' ); ?></span>
					<span class="gmapb-play"><?php Gmap_Block_Icons::render( 'play', 22 ); ?></span>
				</button>

				<div class="gmapb-tutorial__copy">
					<span class="gmapb-badge gmapb-badge--brand"><?php esc_html_e( '3 min watch', 'gmap-block' ); ?></span>
					<h3 class="gmapb-tutorial__title"><?php esc_html_e( 'Install, activate and license Gmap Pro', 'gmap-block' ); ?></h3>
					<p class="gmapb-tutorial__desc"><?php esc_html_e( 'A short walkthrough covering setup, API key creation and adding your first map block to a page.', 'gmap-block' ); ?></p>
				</div>
			</div>

			<div class="gmapb-quicklinks">
				<?php foreach ( $gmapb_quick_links as $gmapb_link ) : ?>
					<a class="gmapb-card gmapb-tile" href="<?php echo esc_url( $gmapb_link['url'] ); ?>" target="_blank" rel="noopener noreferrer">
						<span class="gmapb-iconbox gmapb-iconbox--sm"><?php Gmap_Block_Icons::render( $gmapb_link['icon'], 18 ); ?></span>
						<span class="gmapb-tile__copy">
							<span class="gmapb-tile__name"><?php echo esc_html( $gmapb_link['name'] ); ?></span>
							<span class="gmapb-tile__desc"><?php echo esc_html( $gmapb_link['desc'] ); ?></span>
						</span>
						<?php Gmap_Block_Icons::render( 'chevron-right', 16, 'gmapb-tile__arrow' ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>

		<?php
		/**
		 * Fires at the end of the overview tab's main column.
		 *
		 * @since 1.3.0
		 */
		do_action( 'gmap_block_dashboard_overview_main' );
		?>
	</div>

	<?php $this->render_sidebar( array( 'status', 'review', 'products' ) ); ?>
</div>
