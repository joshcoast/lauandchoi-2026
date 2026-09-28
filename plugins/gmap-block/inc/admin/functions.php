<?php
/**
 * Public helpers for the Gmap Block dashboard.
 *
 * These are the functions add-ons (Gmap Pro) are expected to call.
 *
 * @since 1.3.0
 * @package GmapBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'gmap_block_is_pro_active' ) ) {
	/**
	 * Whether Gmap Pro is installed and running.
	 *
	 * @since 1.3.0
	 * @return bool
	 */
	function gmap_block_is_pro_active() {
		return defined( 'GMAP_PRO_VERSION' );
	}
}

if ( ! function_exists( 'gmap_block_get_api_key' ) ) {
	/**
	 * The Google Maps API key saved on this site.
	 *
	 * Falls back to the legacy key Gmap Pro used to own, so sites that
	 * configured the key before it moved into the free plugin keep working.
	 *
	 * @since 1.3.0
	 * @return string
	 */
	function gmap_block_get_api_key() {
		$key = get_option( 'gmap_block_api_key', '' );

		if ( '' === $key || false === $key ) {
			$key = get_option( 'gmap_pro_api_key', '' );
		}

		return is_string( $key ) ? trim( $key ) : '';
	}
}

if ( ! function_exists( 'gmap_block_has_api_key' ) ) {
	/**
	 * Whether a Google Maps API key is configured.
	 *
	 * @since 1.3.0
	 * @return bool
	 */
	function gmap_block_has_api_key() {
		return '' !== gmap_block_get_api_key();
	}
}

if ( ! function_exists( 'gmap_block_dashboard_url' ) ) {
	/**
	 * URL of a dashboard tab.
	 *
	 * @since 1.3.0
	 * @param string $tab Tab slug. Defaults to the overview tab.
	 * @return string
	 */
	function gmap_block_dashboard_url( $tab = 'overview' ) {
		$url = admin_url( 'options-general.php?page=' . Gmap_Block_Dashboard::MENU_SLUG );

		if ( $tab && 'overview' !== $tab ) {
			$url = add_query_arg( 'tab', sanitize_key( $tab ), $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'gmap_block_get_pro_url' ) ) {
	/**
	 * Marketing URL used by every "Get Pro" call to action.
	 *
	 * @since 1.3.0
	 * @return string
	 */
	function gmap_block_get_pro_url() {
		/**
		 * Filters the upgrade URL shown when Gmap Pro is not installed.
		 *
		 * @since 1.3.0
		 * @param string $url Upgrade URL.
		 */
		return apply_filters( 'gmap_block_pro_url', 'https://gmap.gutenbergkits.com/' );
	}
}

if ( ! function_exists( 'gmap_block_admin_icon' ) ) {
	/**
	 * Render one of the dashboard icons.
	 *
	 * Handy for add-ons rendering content inside a dashboard tab.
	 *
	 * @since 1.3.0
	 * @param string $name  Icon name.
	 * @param int    $size  Size in pixels.
	 * @param string $class Extra class names.
	 * @return void
	 */
	function gmap_block_admin_icon( $name, $size = 16, $class = '' ) {
		Gmap_Block_Icons::render( $name, $size, $class );
	}
}
