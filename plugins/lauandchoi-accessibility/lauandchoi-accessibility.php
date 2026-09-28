<?php
/**
 * Plugin Name: Lau & Choi Accessibility
 * Description: WCAG 2.2 AA fixes for the GeneratePress / GP Premium front end, plus the IvyForms contact form and GTranslate switcher. Kept out of the theme so GeneratePress and GP Premium can update safely.
 * Version: 1.0.0
 * Requires at least: 6.0
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
