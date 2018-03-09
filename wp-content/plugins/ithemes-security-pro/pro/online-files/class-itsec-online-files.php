<?php

/**
 * Online File Scan Execution
 *
 * Handles all online file scan execution once the feature has been
 * enabled by the user.
 *
 * @since   1.10.0
 *
 * @package iThemes_Security
 */
class ITSEC_Online_Files {
	function run() {
		add_action( 'itsec-file-change-start-hash-comparisons', array( $this, 'load' ) );
		add_action( 'itsec-file-change-settings-form', array( $this, 'render_settings' ) );
		
		add_filter( 'itsec-file-change-sanitize-settings', array( $this, 'sanitize_settings' ) );
		add_action( 'itsec_scheduled_confirm-valid-wporg-plugin', array( $this, 'confirm_valid_wporg_plugin' ) );
		add_action( 'itsec_scheduled_preload-plugin-hashes', array( $this, 'preload_plugin_hashes' ) );

		if ( ITSEC_Modules::get_setting( 'online-files', 'compare_file_hashes' ) ) {
			add_action( 'activated_plugin', array( $this, 'on_plugin_activate' ) );
			add_action( 'deleted_plugin', array( $this, 'clear_hashes_on_delete' ), 10, 2 );
			add_filter( 'upgrader_post_install', array( $this, 'preload_hashes_on_upgrade' ), 100, 3 );
		}
	}
	
	public function load() {
		if ( ! ITSEC_Modules::get_setting( 'online-files', 'compare_file_hashes' ) ) {
			return;
		}
		
		require_once( dirname( __FILE__ ) . '/comparison-engine.php' );
	}
	
	public function render_settings( $form ) {
		require_once( dirname( __FILE__ ) . '/custom-settings.php' );
		
		ITSEC_Online_Files_Custom_Settings::render_settings( $form );
	}
	
	public function sanitize_settings( $settings ) {
		require_once( dirname( __FILE__ ) . '/custom-settings.php' );
		
		return ITSEC_Online_Files_Custom_Settings::sanitize_settings( $settings );
	}

	/**
	 * When a plugin is activated, clear the flag specifying whether it is
	 * a WordPress.org plugin or not. Then, try to preload the plugin's hashes.
	 *
	 * This allows for later installing a .org plugin or non-repo plugin after
	 * having used the previous one.
	 *
	 * @param string $file
	 */
	public function on_plugin_activate( $file ) {

		require_once( dirname( __FILE__ ) . '/class-itsec-online-files-utility.php' );
		ITSEC_Online_Files_Utility::clear_wporg_plugin_hashes( dirname( $file ) );

		ITSEC_Core::get_scheduler()->schedule_soon( 'preload-plugin-hashes', compact( 'file' ) );
	}

	/**
	 * After a package has been installed, schedule an event to fetch the hashes.
	 *
	 * @param bool $success
	 * @param array $data
	 *
	 * @return bool
	 */
	public function preload_hashes_on_upgrade( $success, $data ) {

		if ( ! $success || is_wp_error( $success ) ) {
			return $success;
		}

		if ( empty( $data['plugin'] ) ) {
			return $success;
		}

		ITSEC_Core::get_scheduler()->schedule_soon( 'preload-plugin-hashes', array( 'file' => $data['plugin'] ) );

		return $success;
	}

	/**
	 * When a plugin is uninstalled, remove its hashes from storage.
	 *
	 * @param string $file
	 * @param bool   $deleted
	 */
	public function clear_hashes_on_delete( $file, $deleted ) {
		if ( $deleted ) {
			$slug = dirname( $file );

			require_once( dirname( __FILE__ ) . '/class-itsec-online-files-utility.php' );
			ITSEC_Online_Files_Utility::clear_wporg_plugin_hashes( $slug );
		}
	}

	/**
	 * When downloading plugin hashes during a file scan, we might come across
	 * a 404. Instead of wasting execution time during the lengthy file scan,
	 * we schedule an event later to confirm whether the 404 was due to the plugin
	 * not existing, or just the version being non-existent or a temporary error.
	 *
	 * @param ITSEC_Job $job
	 */
	public function confirm_valid_wporg_plugin( $job ) {

		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}

		if ( ! function_exists( 'plugins_api' ) ) {
			return;
		}

		$data = $job->get_data();
		$slug = $data['slug'];

		require_once( dirname( __FILE__ ) . '/class-itsec-online-files-utility.php' );
		ITSEC_Online_Files_Utility::query_is_wporg_plugin( $slug );
	}

	/**
	 * Preload the hashes of a plugin after upgrade or install.
	 *
	 * @param ITSEC_Job $job
	 */
	public function preload_plugin_hashes( $job ) {

		$data = $job->get_data();

		if ( ! isset( $data['version'] ) ) {
			if ( ! isset( $data['file'] ) ) {
				return;
			}

			$file_data = get_file_data( trailingslashit( WP_PLUGIN_DIR ) . $data['file'], array( 'version' => 'Version' ) );
			$version   = $file_data['version'];
		} else {
			$version = $data['version'];
		}

		if ( isset( $data['slug'] ) ) {
			$slug = $data['slug'];
		} elseif ( isset( $data['file'] ) ) {
			$slug = dirname( $data['file'] );
		} else {
			return;
		}

		require_once( dirname( __FILE__ ) . '/class-itsec-online-files-utility.php' );
		ITSEC_Online_Files_Utility::load_wporg_plugin_hashes( $slug, $version );
	}
}
