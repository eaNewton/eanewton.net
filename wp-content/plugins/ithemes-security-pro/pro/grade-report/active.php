<?php

final class ITSEC_Grading_System_Active {
	private static $instance = false;


	private function __construct() {
		$this->add_hooks();
	}

	public static function init() {
		if ( self::$instance ) {
			return;
		}

		self::$instance = new self();
	}

	private function add_hooks() {
		add_action( 'wp_ajax_itsec_grade_report_page', array( $this, 'handle_ajax_request' ) );
		add_filter( 'itsec-admin-page-file-path-grade-report', array( $this, 'get_admin_page_file' ) );
		add_filter( 'itsec-admin-page-refs', array( $this, 'filter_admin_page_refs' ), 10, 3 );
	}

	public function get_admin_page_file( $file ) {
		return dirname( __FILE__ ) . '/admin-page/page.php';
	}

	public function filter_admin_page_refs( $page_refs, $capability, $callback ) {
		$page_refs[] = add_submenu_page( 'itsec', '', __( 'Grade Report', 'it-l10n-ithemes-security-pro' ), $capability, 'itsec-grade-report', $callback );

		return $page_refs;
	}

	public function handle_ajax_request() {
		do_action( 'wp_ajax_itsec_settings_page' );
	}
}

ITSEC_Grading_System_Active::init();
