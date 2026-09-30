<?php
/**
 * Staff Grid front end. The cards inside render themselves; this only supplies the grid wrapper.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered Staff Cards.
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

if ( '' === trim( $content ) ) {
	return;
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'lc-staff-grid' ) ); ?>>
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput -- rendered inner blocks. ?>
</div>
