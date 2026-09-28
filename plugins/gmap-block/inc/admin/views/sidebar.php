<?php
/**
 * Dashboard sidebar: plugin status, review prompt and cross-sell products.
 *
 * @since 1.3.0
 * @package GmapBlock
 *
 * @var Gmap_Block_Dashboard $this     Dashboard instance.
 * @var array                $parts    Sections to render.
 * @var array                $status   Plugin status payload.
 * @var array                $products Cross-sell products.
 * @var array                $links    Outbound links.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<aside class="gmapb-side">

	<?php if ( in_array( 'status', $parts, true ) ) : ?>
		<section class="gmapb-status">
			<div class="gmapb-status__top">
				<span class="gmapb-status__eyebrow"><?php echo esc_html( $status['eyebrow'] ); ?></span>
				<span class="gmapb-status__pill<?php echo empty( $status['ok'] ) ? ' is-warn' : ''; ?>">
					<i class="gmapb-dot" aria-hidden="true"></i>
					<?php echo esc_html( $status['pill'] ); ?>
				</span>
			</div>

			<h2 class="gmapb-status__headline"><?php echo esc_html( $status['headline'] ); ?></h2>

			<ul class="gmapb-status__list">
				<?php foreach ( $status['items'] as $gmapb_item ) : ?>
					<li class="gmapb-status__row<?php echo empty( $gmapb_item['ok'] ) ? ' is-pending' : ''; ?>">
						<?php Gmap_Block_Icons::render( empty( $gmapb_item['ok'] ) ? 'circle-alert' : 'circle-check', 16 ); ?>
						<span><?php echo esc_html( $gmapb_item['label'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<?php if ( in_array( 'review', $parts, true ) ) : ?>
		<section class="gmapb-review">
			<div class="gmapb-review__top">
				<div class="gmapb-review__head">
					<span class="gmapb-stars" aria-hidden="true">
						<?php for ( $gmapb_star = 0; $gmapb_star < 5; $gmapb_star++ ) : ?>
							<?php Gmap_Block_Icons::render( 'star', 20 ); ?>
						<?php endfor; ?>
					</span>
					<span class="gmapb-badge gmapb-badge--amber"><?php esc_html_e( '5.0 on WP.org', 'gmap-block' ); ?></span>
				</div>

				<h2 class="gmapb-review__title"><?php esc_html_e( 'Enjoying Gmap Block?', 'gmap-block' ); ?></h2>
				<p class="gmapb-review__desc"><?php esc_html_e( 'A 5-star review on WordPress.org takes 30 seconds and helps a small team keep building.', 'gmap-block' ); ?></p>

				<a class="gmapb-btn gmapb-btn--brand gmapb-btn--block" href="<?php echo esc_url( $links['review'] ); ?>" target="_blank" rel="noopener noreferrer">
					<?php Gmap_Block_Icons::render( 'star', 17 ); ?>
					<span><?php esc_html_e( 'Leave a review', 'gmap-block' ); ?></span>
					<?php Gmap_Block_Icons::render( 'arrow-up-right', 15, 'gmapb-icon--soft' ); ?>
				</a>
			</div>

			<div class="gmapb-review__foot">
				<span class="gmapb-review__foot-left">
					<?php Gmap_Block_Icons::render( 'life-buoy', 16 ); ?>
					<?php esc_html_e( 'Stuck on something?', 'gmap-block' ); ?>
				</span>
				<a class="gmapb-link" href="<?php echo esc_url( $links['support'] ); ?>" target="_blank" rel="noopener noreferrer">
					<span><?php esc_html_e( 'Get support', 'gmap-block' ); ?></span>
					<?php Gmap_Block_Icons::render( 'arrow-right', 14 ); ?>
				</a>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( in_array( 'products', $parts, true ) && ! empty( $products ) ) : ?>
		<section class="gmapb-products">
			<h2 class="gmapb-section-title"><?php esc_html_e( 'More from Gutenbergkits', 'gmap-block' ); ?></h2>

			<div class="gmapb-products__card">
				<?php foreach ( $products as $gmapb_product ) : ?>
					<a class="gmapb-product" href="<?php echo esc_url( $gmapb_product['url'] ); ?>" target="_blank" rel="noopener noreferrer">
						<span class="gmapb-product__icon"><?php Gmap_Block_Icons::render( $gmapb_product['icon'], 18 ); ?></span>
						<span class="gmapb-product__copy">
							<span class="gmapb-product__name"><?php echo esc_html( $gmapb_product['name'] ); ?></span>
							<span class="gmapb-product__desc"><?php echo esc_html( $gmapb_product['desc'] ); ?></span>
						</span>
						<?php Gmap_Block_Icons::render( 'arrow-up-right', 16, 'gmapb-product__arrow' ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	/**
	 * Fires at the end of the dashboard sidebar.
	 *
	 * @since 1.3.0
	 * @param array $parts Sections rendered on this screen.
	 */
	do_action( 'gmap_block_dashboard_sidebar', $parts );
	?>
</aside>
