<?php

final class ITSEC_Two_Factor_Logs {
	private $providers = null;


	public function __construct() {
		add_filter( 'itsec_logs_prepare_two_factor_entry_for_list_display', array( $this, 'filter_entry_for_list_display' ), 10, 3 );
	}

	public function filter_entry_for_list_display( $entry, $code, $data ) {
		$entry['module_display'] = esc_html__( 'Two Factor', 'it-l10n-ithemes-security-pro' );

		if ( 'successful_authentication' === $code ) {
			$entry['description'] = esc_html__( 'Successful Authentication', 'it-l10n-ithemes-security-pro' );
		} else if ( 'failed_authentication' === $code ) {
			$entry['description'] = esc_html__( 'Failed Authentication', 'it-l10n-ithemes-security-pro' );
		}


		if ( isset( $data[0] ) && 0 !== $data[0] ) {
			$entry['user_id'] = $data[0];
			if ( false !== ( $user = get_userdata( $data[0] ) ) ) {
				$username = $user->user_login;
			}
		}

		if ( ! isset( $username ) ) {
			$username = '<b>' . esc_html__( 'Unknown User', 'it-l10n-ithemes-security-pro' ) . '</b>';
		}

		if ( is_null( $this->providers ) ) {
			require_once( ITSEC_Core::get_plugin_dir() . '/pro/two-factor/class-itsec-two-factor-helper.php' );
			$two_factor_helper = ITSEC_Two_Factor_Helper::get_instance();

			$this->providers = $two_factor_helper->get_all_provider_instances();
		}

		if ( ! isset( $data[1] ) ) {
			if ( 'successful_authentication' === $code ) {
				/* translators: 1: Username */
				$entry['description'] = sprintf( esc_html__( '%1$s Authenticated', 'it-l10n-ithemes-security-pro' ), $username );
			} else if ( 'failed_authentication' === $code ) {
				/* translators: 1: Username */
				$entry['description'] = sprintf( esc_html__( '%1$s Failed Authentication', 'it-l10n-ithemes-security-pro' ), $username );
			}
		} else {
			if ( isset( $this->providers[$data[1]] ) ) {
				$provider = $this->providers[$data[1]]->get_label();
			} else {
				$provider = $data[1];
			}

			if ( 'successful_authentication' === $code ) {
				/* translators: 1: Username, 2: Two Factor provider */
				$entry['description'] = sprintf( esc_html__( '%1$s Authenticated Using %2$s', 'it-l10n-ithemes-security-pro' ), $username, $provider );
			} else if ( 'failed_authentication' === $code ) {
				/* translators: 1: Username, 2: Two Factor provider */
				$entry['description'] = sprintf( esc_html__( '%1$s Failed Authentication Using %2$s', 'it-l10n-ithemes-security-pro' ), $username, $provider );
			}
		}

		return $entry;
	}

	private function get_post_status_label( $status ) {
		$status_object = get_post_status_object( $status );

		if ( is_null( $status_object ) ) {
			return $status;
		}

		return $status_object->label;
	}
}
new ITSEC_Two_Factor_Logs();
