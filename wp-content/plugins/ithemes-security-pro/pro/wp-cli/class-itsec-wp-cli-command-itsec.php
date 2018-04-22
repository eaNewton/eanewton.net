<?php

/**
 * Manage iThemes Security Pro functionality
 *
 * Provides command line access via WP-CLI: http://wp-cli.org/
 */
class ITSEC_WP_CLI_Command_ITSEC extends WP_CLI_Command {

	/**
	 * Run the upgrade routine.
	 *
	 * ## OPTIONS
	 *
	 * [--build=<build>]
	 * : Manually specify the build number to upgrade from. Otherwise, will pull from current version.
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function upgrade( $args, $assoc_args ) {

		$build = ! empty( $assoc_args['build'] ) ? $assoc_args['build'] : false;

		ITSEC_Core::get_instance()->handle_upgrade( $build );

		WP_CLI::success( __( 'Upgrade routine completed.', 'it-l10n-ithemes-security-pro' ) );
	}

	/**
	 * Performs a file change scan
	 */
	public function filescan() {
		WP_CLI::error( 'Deprecated. See wp itsec file-change scan' );
	}

	/**
	 * Retrieve active lockouts
	 *
	 * @since 1.12
	 *
	 * @return void
	 */
	public function getlockouts() {

		/** @var ITSEC_Lockout $itsec_lockout */
		global $itsec_lockout;

		$host_locks = $itsec_lockout->get_lockouts( 'host' );
		$user_locks = $itsec_lockout->get_lockouts( 'user' );

		if ( empty( $host_locks ) && empty( $user_locks ) ) {

			WP_CLI::success( __( 'There are no current lockouts', 'it-l10n-ithemes-security-pro' ) );

		} else {

			if ( ! empty( $host_locks ) ) {

				foreach ( $host_locks as $index => $lock ) {

					$host_locks[ $index ]['type']           = __( 'host', 'it-l10n-ithemes-security-pro' );
					$host_locks[ $index ]['lockout_expire'] = isset( $lock['lockout_expire'] ) ? human_time_diff( ITSEC_Core::get_current_time(), strtotime( $lock['lockout_expire'] ) ) : __( 'N/A', 'it-l10n-ithemes-security-pro' );

				}

			}

			if ( ! empty( $user_locks ) ) {

				foreach ( $user_locks as $index => $lock ) {

					$user_locks[ $index ]['type']           = __( 'user', 'it-l10n-ithemes-security-pro' );
					$user_locks[ $index ]['lockout_expire'] = isset( $lock['lockout_expire'] ) ? human_time_diff( ITSEC_Core::get_current_time(), strtotime( $lock['lockout_expire'] ) ) : __( 'N/A', 'it-l10n-ithemes-security-pro' );

				}

			}

			$lockouts = array_merge( $host_locks, $user_locks );

			WP_CLI\Utils\format_items( 'table', $lockouts, array( 'lockout_id', 'type', 'lockout_host', 'lockout_username', 'lockout_expire' ) );

		}

	}

	/**
	 * Release a lockout using one or more ID's provided by getlockouts.
	 *
	 * ## OPTIONS
	 *
	 * [<id>...]
	 * : One or more active lockout ID's.
	 *
	 * [--id=<id>]
	 * : An active lockout ID.
	 *
	 * ## EXAMPLES
	 *
	 *     wp itsec releaselockout 14 21
	 *     wp itsec releaselockout --id=83
	 *
	 * @since 1.12
	 *
	 * @param array $args
	 * @param array $assoc_args
	 *
	 * @return void
	 */
	public function releaselockout( $args, $assoc_args ) {

		/** @var ITSEC_Lockout $itsec_lockout */
		global $itsec_lockout;

		$ids = array();

		//make sure they provided a valid ID
		if ( isset( $assoc_args['id'] ) ) {
			$ids[] = $assoc_args['id'];
		} else {
			$ids = $args;
		}

		if ( empty( $ids ) ) {
			WP_CLI::error( __( 'You must supply one or more lockout ID\'s to release.', 'it-l10n-ithemes-security-pro' ) );
		}

		foreach ( $ids as $id ) {
			if ( '' === $id ) {
				WP_CLI::error( __( 'Skipping empty ID.', 'it-l10n-ithemes-security-pro' ) );
			} else if ( (string) intval( $id ) !== (string) $id ) {
				WP_CLI::error( sprintf( __( 'Skipping invalid ID "%s". Please supply a valid ID.', 'it-l10n-ithemes-security-pro' ), $id ) );
			} else if ( ! $itsec_lockout->release_lockout( $id ) ) {
				WP_CLI::error( sprintf( __( 'Unable to remove lockout "%s".', 'it-l10n-ithemes-security-pro' ), $id ) );
			} else {
				WP_CLI::success( sprintf( __( 'Successfully removed lockout "%d".', 'it-l10n-ithemes-security-pro' ), $id ) );
			}
		}
	}

	/**
	 * List the most recent log items
	 *
	 * ## OPTIONS
	 *
	 * [<count>]
	 * : The number of log items to display.
	 * ---
	 * default: 10
	 * ---
	 *
	 * [--count=<count>]
	 * : The number of log items to display.
	 * ---
	 * default: 10
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp itsec getrecent 20
	 *     wp itsec getrecent --count=50
	 *
	 * @since 1.12
	 *
	 * @param array $args
	 * @param array $assoc_args
	 *
	 * @return void
	 */
	public function getrecent( $args, $assoc_args ) {
		if ( isset( $assoc_args['count'] ) && 10 != $assoc_args['count'] ) {
			$count = intval( $assoc_args['count'] );
		} else if ( isset( $args[0] ) && 10 != $args[0] ) {
			$count = intval( $args[0] );
		} else {
			$count = 10;
		}

		$entries = ITSEC_Log::get_entries( array(), $count );

		if ( ! is_array( $entries ) || empty( $entries ) ) {

			WP_CLI::success( __( 'The Security logs are empty.', 'it-l10n-ithemes-security-pro' ) );

		} else {

			foreach ( $entries as $index => $entry ) {
				if ( '' === $entry['user_id'] ) {
					$username = '';
				} else {
					$user = get_user_by( 'id', $entry['user_id'] );

					if ( false === $user ) {
						$username = '';
					} else {
						$username = $user->user_login;
					}
				}

				$entries[$index] = array(
					'Time'     => sprintf( esc_html__( '%s ago', 'it-l10n-ithemes-security-pro' ), human_time_diff( ITSEC_Core::get_current_time_gmt(), strtotime( $entry['timestamp'] ) ) ),
					'Code'     => $entry['code'],
					'Type'     => $entry['type'],
					'IP'       => $entry['remote_ip'],
					'Username' => $username,
					'URL'      => $entry['url'],
				);

			}

			WP_CLI\Utils\format_items( 'table', $entries, array( 'Time', 'Code', 'Type', 'IP', 'Username', 'URL' ) );

		}

	}

}

