<?php

final class ITSEC_File_Change_Logs {
	public function __construct() {
		add_filter( 'itsec_logs_prepare_file_change_entry_for_list_display', array( $this, 'filter_entry_for_list_display' ), 10, 3 );
		add_filter( 'itsec_logs_prepare_file_change_entry_for_details_display', array( $this, 'filter_entry_for_details_display' ), 10, 4 );
	}

	public function filter_entry_for_list_display( $entry, $code, $code_data ) {
		$entry['module_display'] = esc_html__( 'File Change', 'it-l10n-ithemes-security-pro' );

		if ( 'scan' === $code && 'process-start' === $entry['type'] ) {
			$entry['description'] = esc_html__( 'Scan Performance', 'it-l10n-ithemes-security-pro' );
		} else if ( 'no-changes-found' === $code ) {
			$entry['description'] = esc_html__( 'No Changes Found', 'it-l10n-ithemes-security-pro' );
		} else if ( 'changes-found' === $code ) {
			if ( isset( $code_data[0] ) ) {
				$entry['description'] = sprintf( esc_html__( '%1$d Added, %2$d Removed, %3$d Changed', 'it-l10n-ithemes-security-pro' ), $code_data[0], $code_data[1], $code_data[2] );
			} else {
				$entry['description'] = esc_html__( 'Changes Found', 'it-l10n-ithemes-security-pro' );
			}
		}

		return $entry;
	}

	public function filter_entry_for_details_display( $details, $entry, $code, $code_data ) {
		$entry = $this->filter_entry_for_list_display( $entry, $code, $code_data );

		$details['module']['content'] = $entry['module_display'];
		$details['description']['content'] = $entry['description'];

		if ( 'process-start' !== $entry['type'] ) {
			$details['memory'] = array(
				'header'  => esc_html__( 'Memory Used', 'it-l10n-ithemes-security-pro' ),
				'content' => sprintf( esc_html_x( '%s MB', 'Megabytes of memory used', 'it-l10n-ithemes-security-pro' ), $entry['data']['memory'] ),
			);

			$types = array(
				'added'   => esc_html__( 'Added', 'it-l10n-ithemes-security-pro' ),
				'removed' => esc_html__( 'Removed', 'it-l10n-ithemes-security-pro' ),
				'changed' => esc_html__( 'Changed', 'it-l10n-ithemes-security-pro' ),
			);

			foreach ( $types as $type => $header ) {
				$details[$type] = array(
					'header'  => $header,
					'content' => '<pre>' . implode( "\n", array_keys( $entry['data'][$type] ) ) . '</pre>',
				);
			}
		}

		return $details;
	}
}
new ITSEC_File_Change_Logs();
