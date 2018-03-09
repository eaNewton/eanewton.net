<?php

final class ITSEC_User_Logging {
	private $should_log = null;

	public function run() {
		add_action( 'wp_login', array( $this, 'wp_login' ), 10, 2 );
		add_action( 'wp_logout', array( $this, 'wp_logout' ) );
		add_action( 'itsec-two-factor-successful-authentication', array( $this, 'log_two_factor_authentication' ), 10, 2 );
		add_action( 'transition_post_status', array( $this, 'transition_post_status' ), 10, 3 );
	}

	/**
	 * Determine if the current user should have actions logged per the settings.
	 *
	 * @param bool $user User to check. Optional.
	 *
	 * @return bool True if the user actions should be logged, false otherwise.
	 */
	public function should_log_for_current_user( $user = false ) {
		require_once( ITSEC_Core::get_core_dir() . '/lib/class-itsec-lib-canonical-roles.php' );

		return ITSEC_Lib_Canonical_Roles::is_user_at_least( ITSEC_Modules::get_setting( 'user-logging', 'role' ), $user );
	}

	/**
	 * Log post status transition
	 *
	 * @since 4.2
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 *
	 * @return void
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {
		if ( in_array( $new_status, array( 'auto-draft', 'inherit' ) ) ) {
			// Don't log automated processes as they don't indicate user action.
			return;
		}

		if ( $this->should_log_for_current_user() ) {
			$user_id = get_current_user_id();
			$post_id = $post->ID;
			ITSEC_Log::add_notice( 'user_logging', "post-status-changed::$user_id,$post_id,$old_status,$new_status", compact( 'user_id', 'post_id', 'old_status', 'new_status' ) );
		}
	}

	/**
	 * Log successful user login
	 *
	 * @since 4.1
	 *
	 * @return void
	 */
	public function wp_login( $user_login, $user ) {
		if ( $this->should_log_for_current_user( $user->ID ) ) {
			$user_id = $user->ID;
			ITSEC_Log::add_notice( 'user_logging', "user-logged-in::$user_id", compact( 'user_id' ) );
		}
	}

	/**
	 * Log successful user logout
	 *
	 * @since 4.1
	 *
	 * @return void
	 */
	public function wp_logout() {
		if ( $this->should_log_for_current_user() ) {
			$user_id = get_current_user_id();
			ITSEC_Log::add_notice( 'user_logging', "user-logged-out::$user_id", compact( 'user_id' ) );
		}
	}

	/**
	 * Log successful two-factor login
	 *
	 * @since 4.7.4
	 *
	 * @param string $provider_class Type of two factor authentication.
	 */
	public function log_two_factor_authentication( $user_id, $provider_class ) {
		if ( $this->should_log_for_current_user( $user_id ) ) {
			ITSEC_Log::add_notice( 'user_logging', "user-logged-in::$user_id,two_factor,$provider_class", compact( 'user_id' ) );
		}
	}
}
