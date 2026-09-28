<?php
/**
 * Plugin Name: Lau & Choi Accessibility
 * Description: WCAG 2.2 AA fixes for the GeneratePress / GP Premium front end, plus the IvyForms contact form and GTranslate switcher. Kept out of the theme so GeneratePress and GP Premium can update safely.
 * Version: 1.1.0
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Text Domain: lauandchoi-accessibility
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'lauandchoi_a11y_enqueue', 100 );

/**
 * Load the fixes after the theme and plugin styles they override.
 */
function lauandchoi_a11y_enqueue() {
	$dir = plugin_dir_path( __FILE__ ) . 'assets/';
	$url = plugin_dir_url( __FILE__ ) . 'assets/';

	wp_enqueue_style( 'lauandchoi-a11y', $url . 'a11y.css', array(), filemtime( $dir . 'a11y.css' ) );

	wp_enqueue_script(
		'lauandchoi-a11y',
		$url . 'a11y.js',
		array(),
		filemtime( $dir . 'a11y.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}

add_filter( 'render_block', 'lauandchoi_a11y_strip_image_titles', 10, 2 );

/**
 * Remove title attributes from images as blocks render.
 *
 * GenerateBlocks copies the Media Library title (usually the file name) into
 * every image it inserts. The alt text is what describes an image; a title only
 * adds a mouse-only tooltip that screen readers may announce after the alt.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block.
 * @return string
 */
function lauandchoi_a11y_strip_image_titles( $block_content, $block ) {
	$image_blocks = array( 'core/image', 'generateblocks/media', 'generateblocks/image' );

	if ( ! in_array( $block['blockName'], $image_blocks, true ) || false === stripos( $block_content, 'title=' ) ) {
		return $block_content;
	}

	$tags = new WP_HTML_Tag_Processor( $block_content );

	while ( $tags->next_tag( 'img' ) ) {
		$tags->remove_attribute( 'title' );
	}

	return $tags->get_updated_html();
}
