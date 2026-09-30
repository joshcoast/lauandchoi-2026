<?php
/**
 * Plugin Name: Lau & Choi Blocks
 * Description: Custom editor blocks for the Lau & Choi site: a Staff Grid made of Staff Cards (photo, name, job title, bio) so staff can be added, removed and reordered without touching layout.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Text Domain: lauandchoi-blocks
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'lauandchoi_blocks_register' );

/**
 * Register every block built into build/. Source lives in src/; run `npm run build` after editing it.
 */
function lauandchoi_blocks_register() {
	foreach ( array( 'staff-grid', 'staff-card' ) as $block ) {
		register_block_type( __DIR__ . '/build/' . $block );
	}
}
