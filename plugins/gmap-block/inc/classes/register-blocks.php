<?php
/**
 * Gmap Blocks Registration
 *
 * @since 1.0.0
 * @package GmapBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Gmap_Block_Registration' ) ) {

	/**
	 * Gmap Blocks Registration Class
	 *
	 * @since 1.0.0
	 * @package GmapBlock
	 */
	class Gmap_Block_Registration {

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
			add_action( 'init', array( $this, 'register_blocks' ) );
		}

		/**
		 * Register Blocks
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function register_blocks() {
			$blocks = ['gmap']; 
			if ( ! empty( $blocks ) and is_array( $blocks ) ) {
				foreach ( $blocks as $block ) {
					register_block_type( trailingslashit( GMAP_PLUGIN_DIR ) . '/build/blocks/' . $block );
				}
			}
		}
	}

	new Gmap_Block_Registration(); // Initialize the class.
}

