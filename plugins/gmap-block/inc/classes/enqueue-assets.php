<?php
/**
 * Gmap Blocks Enqueue Assets
 *
 * @package GmapBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Gmap_Block_Assets' ) ) {

	/**
	 * Gmap Blocks Enqueue Assets Class
	 *
	 * @since 1.0.0
	 * @package GmapBlock
	 */

	class Gmap_Block_Assets {

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
			add_action( 'init', array( $this, 'register_editor_assets' ) ); // Register handles early.
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ), 2 ); // Editor Assets.
		}

		/**
		 * Register Editor Assets
		 *
		 * Registration is split out from enqueueing so the handles exist as soon
		 * as blocks are registered. Add-ons (Gmap Pro) declare
		 * `gmap-block-module-script` as a dependency in their block.json, which
		 * is what guarantees `window.gmapModules` is defined before their block
		 * bundles evaluate.
		 *
		 * @since 1.3.0
		 * @return void
		 */
		public function register_editor_assets() {

			// global
			$global_asset = trailingslashit( GMAP_PLUGIN_DIR ) . 'build/global/index.asset.php';
			if ( file_exists( $global_asset ) ) {
				$global_dependencies = require $global_asset;
				wp_register_script(
					'gmap-block-global-script',
					trailingslashit( GMAP_URL_FILE ) . 'build/global/index.js',
					$global_dependencies['dependencies'],
					GMAP_VERSION,
					false
				);
				wp_register_style(
					'gmap-block-global-style',
					trailingslashit( GMAP_URL_FILE ) . 'build/global/index.css',
					array(),
					GMAP_VERSION,
					'all'
				);
			}

			// modules
			$module_asset = trailingslashit( GMAP_PLUGIN_DIR ) . 'build/modules/index.asset.php';
			if ( file_exists( $module_asset ) ) {
				$module_dependencies = require $module_asset;
				wp_register_script(
					'gmap-block-module-script',
					trailingslashit( GMAP_URL_FILE ) . 'build/modules/index.js',
					$module_dependencies['dependencies'],
					GMAP_VERSION,
					false
				);
			}
		}

		/**
		 * Enqueue Editor Assets
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function enqueue_editor_assets() {

			wp_enqueue_script( 'gmap-block-global-script' );
			wp_enqueue_style( 'gmap-block-global-style' );
			wp_enqueue_script( 'gmap-block-module-script' );

			wp_localize_script( 'gmap-block-global-script', 'gmapBlockPro', array(
				'has_pro' => defined( 'GMAP_PRO_VERSION' ) ? true : false,
			) );
		}

	}

	new Gmap_Block_Assets(); // Initialize the class.
}

