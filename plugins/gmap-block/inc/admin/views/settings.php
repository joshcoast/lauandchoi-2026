<?php
/**
 * Settings tab: the Google Maps API key.
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
$gmapb_api_key = gmap_block_get_api_key();
$gmapb_has_key = '' !== $gmapb_api_key;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only flag set by options.php after a successful save.
$gmapb_saved = isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'];

$gmapb_steps = array(
	array(
		'title' => __( 'Create a project', 'gmap-block' ),
		'desc'  => __( 'Sign in to Google Cloud Console and start a new project.', 'gmap-block' ),
	),
	array(
		'title' => __( 'Enable the APIs', 'gmap-block' ),
		'desc'  => __( 'Turn on Maps JavaScript API and Places API for that project.', 'gmap-block' ),
	),
	array(
		'title' => __( 'Copy your key', 'gmap-block' ),
		'desc'  => __( 'Create credentials, copy the key and paste it above.', 'gmap-block' ),
	),
);
?>

<div class="gmapb-page-head">
	<h1 class="gmapb-page-title"><?php esc_html_e( 'Settings', 'gmap-block' ); ?></h1>
	<p class="gmapb-page-sub"><?php esc_html_e( 'Configure the Google Maps API key used by every Gmap block on your site.', 'gmap-block' ); ?></p>
</div>

<?php if ( $gmapb_saved ) : ?>
	<div class="gmapb-notice gmapb-notice--success">
		<?php Gmap_Block_Icons::render( 'circle-check', 17 ); ?>
		<span><?php esc_html_e( 'Settings saved.', 'gmap-block' ); ?></span>
	</div>
<?php endif; ?>

<div class="gmapb-columns">
	<div class="gmapb-main">

		<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" class="gmapb-card gmapb-apikey">
			<?php settings_fields( Gmap_Block_Dashboard::OPTION_GROUP ); ?>

			<div class="gmapb-apikey__head">
				<div class="gmapb-apikey__head-copy">
					<h2 class="gmapb-card-title"><?php esc_html_e( 'Google Maps API Key', 'gmap-block' ); ?></h2>
					<p class="gmapb-card-caption"><?php esc_html_e( 'Stored securely on your site and never shared with anyone.', 'gmap-block' ); ?></p>
				</div>
				<span class="gmapb-state gmapb-state--<?php echo $gmapb_has_key ? 'good' : 'muted'; ?>">
					<i class="gmapb-dot" aria-hidden="true"></i>
					<?php echo $gmapb_has_key ? esc_html__( 'Connected', 'gmap-block' ) : esc_html__( 'Not set', 'gmap-block' ); ?>
				</span>
			</div>

			<div class="gmapb-apikey__field">
				<div class="gmapb-label-row">
					<label class="gmapb-label" for="gmapb-api-key"><?php esc_html_e( 'API key', 'gmap-block' ); ?></label>
					<span class="gmapb-badge"><?php esc_html_e( 'Required', 'gmap-block' ); ?></span>
				</div>

				<div class="gmapb-input">
					<?php Gmap_Block_Icons::render( 'key-round', 16, 'gmapb-input__icon' ); ?>
					<input
						type="password"
						id="gmapb-api-key"
						name="<?php echo esc_attr( Gmap_Block_Dashboard::OPTION_API_KEY ); ?>"
						value="<?php echo esc_attr( $gmapb_api_key ); ?>"
						placeholder="<?php esc_attr_e( 'Paste your Google Maps API key', 'gmap-block' ); ?>"
						autocomplete="off"
						spellcheck="false"
					/>
					<button type="button" class="gmapb-input__toggle" data-gmapb-toggle="gmapb-api-key" aria-label="<?php esc_attr_e( 'Show API key', 'gmap-block' ); ?>" aria-pressed="false">
						<span class="gmapb-eye gmapb-eye--off"><?php Gmap_Block_Icons::render( 'eye-off', 17 ); ?></span>
						<span class="gmapb-eye gmapb-eye--on"><?php Gmap_Block_Icons::render( 'eye', 17 ); ?></span>
					</button>
				</div>

				<p class="gmapb-helper">
					<?php esc_html_e( 'Free to create in the Google Cloud Console.', 'gmap-block' ); ?>
					<a href="<?php echo esc_url( $gmapb_links['api_guide'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'How to create an API key', 'gmap-block' ); ?></a>
				</p>

				<div class="gmapb-keytest" data-gmapb-keytest data-harness="<?php echo esc_url( $this->get_key_test_url() ); ?>">
					<button type="button" class="gmapb-btn gmapb-btn--ghost gmapb-btn--sm" data-gmapb-keytest-run>
						<?php Gmap_Block_Icons::render( 'shield-check', 15 ); ?>
						<span><?php esc_html_e( 'Test this key', 'gmap-block' ); ?></span>
					</button>
					<span class="gmapb-keytest__hint"><?php esc_html_e( 'Loads Google Maps with this key from this site and reports what worked.', 'gmap-block' ); ?></span>
					<div class="gmapb-keytest__out" data-gmapb-keytest-out hidden></div>
				</div>
			</div>

			<div class="gmapb-apikey__foot">
				<span class="gmapb-save-note">
					<?php Gmap_Block_Icons::render( 'history', 14 ); ?>
					<?php echo esc_html( $this->get_last_saved_label() ); ?>
				</span>
				<button type="submit" class="gmapb-btn gmapb-btn--brand">
					<span><?php esc_html_e( 'Save Settings', 'gmap-block' ); ?></span>
					<?php Gmap_Block_Icons::render( 'arrow-right', 15 ); ?>
				</button>
			</div>
		</form>

		<section class="gmapb-card gmapb-steps">
			<div class="gmapb-section-head">
				<h2 class="gmapb-section-title"><?php esc_html_e( 'Get an API key in 3 steps', 'gmap-block' ); ?></h2>
				<a class="gmapb-link" href="<?php echo esc_url( $gmapb_links['console'] ); ?>" target="_blank" rel="noopener noreferrer">
					<span><?php esc_html_e( 'Open Google Cloud Console', 'gmap-block' ); ?></span>
					<?php Gmap_Block_Icons::render( 'arrow-up-right', 14 ); ?>
				</a>
			</div>

			<div class="gmapb-steps__row">
				<?php foreach ( $gmapb_steps as $gmapb_index => $gmapb_step ) : ?>
					<div class="gmapb-step">
						<span class="gmapb-step__num"><?php echo esc_html( number_format_i18n( $gmapb_index + 1 ) ); ?></span>
						<h3 class="gmapb-step__title"><?php echo esc_html( $gmapb_step['title'] ); ?></h3>
						<p class="gmapb-step__desc"><?php echo esc_html( $gmapb_step['desc'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="gmapb-tip">
				<?php Gmap_Block_Icons::render( 'shield-check', 17 ); ?>
				<span><?php esc_html_e( 'Restrict your key to your own domain in the Google Cloud Console to keep it safe from misuse.', 'gmap-block' ); ?></span>
			</div>
		</section>

		<?php
		/**
		 * Fires at the end of the settings tab's main column.
		 *
		 * @since 1.3.0
		 */
		do_action( 'gmap_block_dashboard_settings_main' );
		?>
	</div>

	<?php $this->render_sidebar( array( 'status', 'products' ) ); ?>
</div>
