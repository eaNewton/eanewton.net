<?php

/**
 * Class ITSEC_Online_Files_Utility
 */
class ITSEC_Online_Files_Utility {

	/**
	 * Check the cache for whether this plugin slug corresponds to a WordPress.org plugin.
	 *
	 * @param string $slug
	 *
	 * @return bool|null
	 */
	public static function is_cached_wporg_plugin( $slug ) {

		$plugins = ITSEC_Modules::get_setting( 'online-files', 'valid_wporg_plugins', array() );

		if ( is_array( $plugins ) && isset( $plugins[ $slug ] ) ) {

			if ( $plugins[ $slug ]['checked_at'] + WEEK_IN_SECONDS < ITSEC_Core::get_current_time_gmt() ) {
				ITSEC_Core::get_scheduler()->schedule_soon( 'confirm-valid-wporg-plugin', compact( 'slug' ) );
			}

			return $plugins[ $slug ]['valid'];
		}

		return null;
	}

	/**
	 * Make an API request to check if the given plugin slug corresponds to a WordPress.org plugin.
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public static function query_is_wporg_plugin( $slug ) {

		if ( ! self::is_valid_wporg_slug( $slug ) ) {
			return false;
		}

		$is_valid = self::query_plugin_exists( $slug );

		if ( is_wp_error( $is_valid ) ) {
			return false;
		}

		$plugins = ITSEC_Modules::get_setting( 'online-files', 'valid_wporg_plugins', array() );

		if ( ! is_array( $plugins ) ) {
			$plugins = array();
		}

		$plugins[ $slug ] = array(
			'valid'      => $is_valid,
			'checked_at' => ITSEC_Core::get_current_time_gmt(),
		);

		ITSEC_Modules::set_setting( 'online-files', 'valid_wporg_plugins', $plugins );

		return $is_valid;
	}

	/**
	 * Query the WordPress.org Plugin Information API to determine if the given slug
	 * exists on WordPress.org.
	 *
	 * @param string $slug
	 *
	 * @return bool|WP_Error
	 */
	private static function query_plugin_exists( $slug ) {

		$url = 'https://api.wordpress.org/plugins/info/1.0/';

		$response = wp_remote_post( $url, array(
			'timeout' => 15,
			'body'    => array(
				'action'  => 'plugin_information',
				'request' => serialize( (object) compact( 'slug' ) ),
			),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( 'N;' === $body ) {
			return false;
		}

		$data = maybe_unserialize( $body );

		if ( ! is_object( $data ) && ! is_array( $data ) ) {
			return new WP_Error();
		}

		return true;
	}

	/**
	 * Clear whether the given plugin slug is a valid WordPress.org plugin.
	 *
	 * @param string $slug
	 */
	public static function clear_is_wporg_plugin( $slug ) {

		$plugins = ITSEC_Modules::get_setting( 'online-files', 'valid_wporg_plugins', array() );

		if ( is_array( $plugins ) && isset( $plugins[ $slug ] ) ) {
			unset( $plugins[ $slug ] );

			ITSEC_Modules::set_setting( 'online-files', 'valid_wporg_plugins', $plugins );
		}
	}

	/**
	 * Get the cached hashes for a plugin on WordPress.org.
	 *
	 * @param string $slug
	 * @param string $version
	 *
	 * @return array
	 */
	public static function get_cached_wporg_plugin_hashes( $slug, $version ) {

		$all_hashes = ITSEC_Modules::get_setting( 'online-files', 'wporg_plugin_hashes', array() );

		if ( ! isset( $all_hashes[ $slug ][ $version ] ) ) {
			$hashes = self::load_wporg_plugin_hashes( $slug, $version );
		} else {
			$hashes     = $all_hashes[ $slug ][ $version ]['hashes'];
			$checked_at = $all_hashes[ $slug ][ $version ]['checked_at'];

			// If the hashes are expired, schedule a background update. If no hashes were found, we check sooner.
			if (
				( empty( $hashes ) && $checked_at + HOUR_IN_SECONDS < ITSEC_Core::get_current_time_gmt() ) ||
				$checked_at + WEEK_IN_SECONDS < ITSEC_Core::get_current_time_gmt()
			) {
				ITSEC_Core::get_scheduler()->schedule_soon( 'preload-plugin-hashes', compact( 'slug', 'version' ) );
			}
		}

		return $hashes;
	}

	/**
	 * Fetch hashes for a WordPress.org plugin and store them for later use.
	 *
	 * @param string $slug
	 * @param string $version
	 *
	 * @return array
	 */
	public static function load_wporg_plugin_hashes( $slug, $version ) {

		if ( ! self::is_valid_wporg_slug( $slug ) ) {
			return array();
		}

		$all_hashes = ITSEC_Modules::get_setting( 'online-files', 'wporg_plugin_hashes', array() );

		$hashes = self::query_wporg_plugin_hashes( $slug, $version );

		if ( isset( $all_hashes[ $slug ] ) ) {
			$plugin_hashes = $all_hashes[ $slug ];
		} else {
			$plugin_hashes = array();
		}

		if ( $plugin_hashes ) {
			uksort( $plugin_hashes, 'version_compare' );
			array_shift( $plugin_hashes );
		}

		$plugin_hashes[ $version ] = array(
			'hashes'     => $hashes,
			'checked_at' => ITSEC_Core::get_current_time_gmt(),
		);

		$all_hashes[ $slug ] = $plugin_hashes;
		ITSEC_Modules::set_setting( 'online-files', 'wporg_plugin_hashes', $all_hashes );

		return $hashes;
	}

	/**
	 * Clear the stored hashes for a WordPress.org plugin.
	 *
	 * @param string $slug
	 */
	public static function clear_wporg_plugin_hashes( $slug ) {

		$all_hashes = ITSEC_Modules::get_setting( 'online-files', 'wporg_plugin_hashes', array() );

		if ( isset( $all_hashes[ $slug ] ) ) {
			unset( $all_hashes[ $slug ] );
			ITSEC_Modules::set_setting( 'online-files', 'wporg_plugin_hashes', $all_hashes );
		}
	}

	/**
	 * Query the WordPress.org checksum API.
	 *
	 * @param string $slug
	 * @param string $version
	 *
	 * @return array
	 */
	private static function query_wporg_plugin_hashes( $slug, $version ) {

		if ( ! self::is_valid_wporg_slug( $slug ) ) {
			return array();
		}

		$url = "https://downloads.wordpress.org/plugin-checksums/{$slug}/{$version}.json";

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( 404 === (int) $code ) {
			ITSEC_Core::get_scheduler()->schedule_soon( 'confirm-valid-wporg-plugin', compact( 'slug' ) );

			return array();
		}

		$body = wp_remote_retrieve_body( $response );

		if ( ! $body ) {
			return array();
		}

		$data = json_decode( $body, true );

		if ( ! $data || empty( $data['files'] ) ) {
			return array();
		}

		return wp_list_pluck( $data['files'], 'md5' );
	}

	/**
	 * Check if the slug is a valid WordPress.org slug.
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	private static function is_valid_wporg_slug( $slug ) {
		return ! empty( $slug ) && '.' !== $slug;
	}
}