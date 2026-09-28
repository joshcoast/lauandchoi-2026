<?php
/**
 * Dashboard shell: top bar, tab bar and the active tab body.
 *
 * @since 1.3.0
 * @package GmapBlock
 *
 * @var Gmap_Block_Dashboard $this        Dashboard instance.
 * @var array                $tabs        Registered tabs.
 * @var string               $current_tab Active tab slug.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$gmapb_links   = $this->get_links();
$gmapb_chips   = $this->get_version_chips();
$gmapb_has_pro = gmap_block_is_pro_active();
?>
<div class="gmapb-app">

	<header class="gmapb-topbar">
		<div class="gmapb-topbar__brand">
			<span class="gmapb-logo"><?php Gmap_Block_Icons::render( 'map-pin', 18 ); ?></span>
			<span class="gmapb-brand-name"><?php esc_html_e( 'Gmap Block', 'gmap-block' ); ?></span>
			<?php if ( $gmapb_has_pro ) : ?>
				<span class="gmapb-pill gmapb-pill--pro"><?php esc_html_e( 'PRO', 'gmap-block' ); ?></span>
			<?php endif; ?>
		</div>

		<div class="gmapb-topbar__meta">
			<?php foreach ( $gmapb_chips as $gmapb_chip ) : ?>
				<span class="gmapb-chip<?php echo ! empty( $gmapb_chip['accent'] ) ? ' is-accent' : ''; ?>">
					<?php
					if ( ! empty( $gmapb_chip['icon'] ) ) {
						Gmap_Block_Icons::render( $gmapb_chip['icon'], 13 );
					}
					?>
					<span><?php echo esc_html( $gmapb_chip['label'] ); ?></span>
				</span>
			<?php endforeach; ?>

			<span class="gmapb-topbar__divider" aria-hidden="true"></span>

			<?php if ( ! $gmapb_has_pro ) : ?>
				<a class="gmapb-btn gmapb-btn--brand gmapb-btn--sm" href="<?php echo esc_url( gmap_block_get_pro_url() ); ?>" target="_blank" rel="noopener noreferrer">
					<?php Gmap_Block_Icons::render( 'crown', 15 ); ?>
					<span><?php esc_html_e( 'Get Pro', 'gmap-block' ); ?></span>
				</a>
			<?php endif; ?>

			<a class="gmapb-btn gmapb-btn--ghost gmapb-btn--sm" href="<?php echo esc_url( $gmapb_links['support'] ); ?>" target="_blank" rel="noopener noreferrer">
				<?php Gmap_Block_Icons::render( 'life-buoy', 15 ); ?>
				<span><?php esc_html_e( 'Get Support', 'gmap-block' ); ?></span>
			</a>
		</div>
	</header>

	<nav class="gmapb-tabs" aria-label="<?php esc_attr_e( 'Gmap Block dashboard', 'gmap-block' ); ?>">
		<?php foreach ( $tabs as $gmapb_slug => $gmapb_tab ) : ?>
			<a
				class="gmapb-tab<?php echo $gmapb_slug === $current_tab ? ' is-active' : ''; ?>"
				href="<?php echo esc_url( gmap_block_dashboard_url( $gmapb_slug ) ); ?>"
				<?php echo $gmapb_slug === $current_tab ? ' aria-current="page"' : ''; ?>
			>
				<?php
				if ( ! empty( $gmapb_tab['icon'] ) ) {
					Gmap_Block_Icons::render( $gmapb_tab['icon'], 16 );
				}
				?>
				<span><?php echo esc_html( $gmapb_tab['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="gmapb-body">
		<?php
		/**
		 * Fires at the top of the dashboard body, before the tab renders.
		 *
		 * @since 1.3.0
		 * @param string $current_tab Active tab slug.
		 */
		do_action( 'gmap_block_dashboard_before_content', $current_tab );

		if ( isset( $tabs[ $current_tab ]['callback'] ) && is_callable( $tabs[ $current_tab ]['callback'] ) ) {
			call_user_func( $tabs[ $current_tab ]['callback'], $current_tab );
		}

		/**
		 * Fires at the bottom of the dashboard body, after the tab renders.
		 *
		 * @since 1.3.0
		 * @param string $current_tab Active tab slug.
		 */
		do_action( 'gmap_block_dashboard_after_content', $current_tab );
		?>
	</div>
</div>
