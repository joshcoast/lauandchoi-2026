<?php
/**
 * Staff Card front end.
 *
 * The photo is output from the Media Library so it gets srcset, width/height and lazy
 * loading; its alt text is the person's name.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Unused; the card has no inner blocks.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

// Name and job title are plain text. RichText stores them HTML-encoded (& as &amp;),
// so strip any tags with kses rather than re-escaping, which would double-encode.
$lc_name  = trim( wp_kses( $attributes['name'] ?? '', array() ) );
$lc_title = trim( wp_kses( $attributes['jobTitle'] ?? '', array() ) );
$lc_bio   = trim( $attributes['bio'] ?? '' );

if ( '' === $lc_name && '' === $lc_title && '' === $lc_bio && empty( $attributes['mediaId'] ) ) {
	return;
}

$lc_photo = '';

if ( ! empty( $attributes['mediaId'] ) ) {
	$lc_photo = wp_get_attachment_image(
		(int) $attributes['mediaId'],
		'large',
		false,
		array(
			'class' => 'lc-staff-card__photo',
			'alt'   => $lc_name,
			'sizes' => '(max-width: 340px) 100vw, 340px',
		)
	);
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'lc-staff-card' ) ); ?>>
	<?php echo $lc_photo; // phpcs:ignore WordPress.Security.EscapeOutput -- built by wp_get_attachment_image(). ?>
	<div class="lc-staff-card__body">
		<?php if ( '' !== $lc_name ) : ?>
			<h2 class="lc-staff-card__name"><?php echo $lc_name; // phpcs:ignore WordPress.Security.EscapeOutput -- kses-stripped above. ?></h2>
		<?php endif; ?>
		<?php if ( '' !== $lc_title ) : ?>
			<p class="lc-staff-card__title"><?php echo $lc_title; // phpcs:ignore WordPress.Security.EscapeOutput -- kses-stripped above. ?></p>
		<?php endif; ?>
		<?php if ( '' !== $lc_bio ) : ?>
			<p class="lc-staff-card__bio"><?php echo wp_kses_post( $lc_bio ); ?></p>
		<?php endif; ?>
	</div>
</div>
