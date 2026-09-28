<?php
/**
 * Gmap Blocks - Generate Dynamic Styles
 *
 * @since 1.0.0
 * @package GmapBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Gmap_Block_Dynamic_Style' ) ) {

	/**
	 * Gmap Blocks Dynamic Style Class
	 *
	 * @since 1.0.0
	 * @package GmapBlock
	 */
	class Gmap_Block_Dynamic_Style {


		private $dynamic_styles = '';

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function __construct() {
			$this->init();
		}

		/**
		 * Initialize the Class
		 *
		 * @since 1.0.0
		 * @return void
		 */
		private function init() {
			add_filter( 'render_block', array( $this, 'generate_dynamic_styles' ), 10, 2 );

			// Enqueue Dynamic Styles
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_dynamic_styles' ) );
		}

		/**
		 * Generate Dynamic Styles
		 *
		 * @since 1.0.0
		 * @param string $block_content Block Content.
		 * @param array  $block Block Attributes.
		 * @return string
		 */
		public function generate_dynamic_styles( $block_content, $block ) {
			if ( isset( $block['blockName'] ) && str_contains( $block['blockName'], 'gmap/' ) ) {
				if ( isset( $block['attrs']['blockStyle'] ) ) {
					$style = $block['attrs']['blockStyle'];
					
					// Ensure style is a string
					if ( is_array( $style ) ) {
						// If it's an array, skip processing or convert to string if needed
						return $block_content;
					}
					
					$this->enqueue_dynamic_styles( $style );
				}
			}
			return $block_content;
		}

		/**
		 * Enqueue Dynamic Styles
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function enqueue_dynamic_styles( $style ) {
			if ( ! empty( $style ) && is_string( $style ) ) {
				$handle = 'gmap-block-inline-style-' . wp_rand( 100, 10000 );
				wp_register_style( $handle, false, array(), GMAP_VERSION, 'all' );
				wp_enqueue_style( $handle, false, array(), GMAP_VERSION, 'all' );
				wp_add_inline_style( $handle, $this->sanitize_css( $style ) );
			}
		}

		/**
		 * Sanitize CSS method
		 *
		 * @since 1.0.0
		 * @param string $css CSS to Sanitize.
		 * @return string
		 */
		private function sanitize_css( $css ) {
			// Validate UTF-8 encoding
			$css = wp_check_invalid_utf8( $css );

			if ( empty( $css ) ) {
				return '';
			}

			// Normalize whitespace to prevent obfuscation tricks
			$css = preg_replace( '/\s+/', ' ', $css );

			// Remove CSS comments (can hide payloads: /* expression */background:url() */)
			$css = preg_replace( '!/\*.*?\*/!s', '', $css );

			// Remove backslash escapes used to bypass keyword filters (e.g. \65 xpression)
			$css = preg_replace( '/\\\\[0-9a-fA-F]{0,6}\s?/', '', $css );

			// Block dangerous CSS functions and protocols
			// Covers: expression(), url(), javascript:, vbscript:, data:, behavior
			if ( preg_match(
				'/expression\s*\(
				| url\s*\(
				| javascript\s*:
				| vbscript\s*:
				| data\s*:
				| @import
				| behavior\s*:
				| -moz-binding\s*:
				| content\s*:/ix',
				$css
			) ) {
				return '';
			}

			// Block HTML tags that could escape the <style> context
			if ( preg_match( '/<\s*\/?\s*(script|style|link|meta|object|embed|iframe)/i', $css ) ) {
				return '';
			}

			// Trim and return
			return trim( $css );
		}
	}

	new Gmap_Block_Dynamic_Style(); // Initialize the Dynamic Style class.
}

