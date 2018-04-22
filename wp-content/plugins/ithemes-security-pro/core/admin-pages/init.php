<?php


final class ITSEC_Admin_Page_Loader {
	private $page_refs = array();
	private $page_id;


	public function __construct() {
		if ( is_multisite() ) {
			add_action( 'network_admin_menu', array( $this, 'add_admin_pages' ) );
		} else {
			add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
		}

		add_action( 'wp_ajax_itsec_settings_page', array( $this, 'handle_ajax_request' ) );
		add_action( 'wp_ajax_itsec_logs_page', array( $this, 'handle_ajax_request' ) );
		add_action( 'wp_ajax_itsec_help_page', array( $this, 'handle_ajax_request' ) );
		add_action( 'wp_ajax_itsec-set-user-setting', array( $this, 'handle_user_setting' ) );

		// Filters for validating user settings
		add_filter( 'itsec-user-setting-valid-itsec-settings-view', array( $this, 'validate_view' ), null, 2 );
	}

	public function add_scripts() {
		ITSEC_Lib::enqueue_util();
	}

	public function add_styles() {
		wp_enqueue_style( 'itsec-settings-page-style', plugins_url( 'css/style.css', __FILE__ ), array(), ITSEC_Core::get_plugin_build() );
	}

	public function add_admin_pages() {
		$capability = ITSEC_Core::get_required_cap();
		$page_refs = array();

		add_menu_page( __( 'Settings', 'it-l10n-ithemes-security-pro' ), __( 'Security', 'it-l10n-ithemes-security-pro' ), $capability, 'itsec', array( $this, 'show_page' ) );
		$page_refs[] = add_submenu_page( 'itsec', __( 'iThemes Security Settings', 'it-l10n-ithemes-security-pro' ), __( 'Settings', 'it-l10n-ithemes-security-pro' ), $capability, 'itsec', array( $this, 'show_page' ) );
		$page_refs[] = add_submenu_page( 'itsec', '', __( 'Security Check', 'it-l10n-ithemes-security-pro' ), $capability, 'itsec-security-check', array( $this, 'show_page' ) );

		$page_refs = apply_filters( 'itsec-admin-page-refs', $page_refs, $capability, array( $this, 'show_page' ) );

		$page_refs[] = add_submenu_page( 'itsec', __( 'iThemes Security Logs', 'it-l10n-ithemes-security-pro' ), __( 'Logs', 'it-l10n-ithemes-security-pro' ), $capability, 'itsec-logs', array( $this, 'show_page' ) );

		if ( ! ITSEC_Core::is_pro() ) {
			$page_refs[] = add_submenu_page( 'itsec', '', '<span style="color:#2EA2CC">' . __( 'Go Pro', 'it-l10n-ithemes-security-pro' ) . '</span>', $capability, 'itsec-go-pro', array( $this, 'show_page' ) );
		}

		foreach ( $page_refs as $page_ref ) {
			add_action( "load-$page_ref", array( $this, 'load' ) );
		}
	}

	private function get_page_id() {
		global $plugin_page;

		if ( isset( $this->page_id ) ) {
			return $this->page_id;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			if ( isset( $_REQUEST['action'] ) && preg_match( '/^itsec_(.+)_page$/', $_REQUEST['action'], $match ) ) {
				$this->page_id = $match[1];
			}
		} else if ( 'itsec-' === substr( $plugin_page, 0, 6 ) ) {
			$this->page_id = substr( $plugin_page, 6 );
		} else if ( 'itsec' === substr( $plugin_page, 0, 5 ) ) {
			$this->page_id = 'settings';
		}

		if ( ! isset( $this->page_id ) ) {
			$this->page_id = '';
		}

		return $this->page_id;
	}

	public function load() {
		add_action( 'admin_print_scripts', array( $this, 'add_scripts' ) );
		add_action( 'admin_print_styles', array( $this, 'add_styles' ) );

		$this->load_file( 'page-%s.php' );
	}

	public function show_page() {
		$page_id = $this->get_page_id();

		if ( 'settings' === $page_id ) {
			$url = network_admin_url( 'admin.php?page=itsec' );
		} else {
			$url = network_admin_url( 'admin.php?page=itsec-' . $this->get_page_id() );
		}

		do_action( 'itsec-page-show', $url );
	}

	public function handle_ajax_request() {
		$this->load_file( 'page-%s.php' );

		do_action( 'itsec-page-ajax' );
	}

	private function load_file( $file ) {
		$id = $this->get_page_id();

		if ( empty( $id ) ) {
			if ( isset( $GLOBALS['pagenow'] ) && 'admin.php' === $GLOBALS['pagenow'] && isset( $_GET['page'] ) && 'itsec-' === substr( $_GET['page'], 0, 6 ) ) {
				$id = substr( $_GET['page'], 6 );
			} else {
				return;
			}
		}

		$id = str_replace( '_', '-', $id );

		$file = dirname( __FILE__ ) . '/' . sprintf( $file, $id );
		$file = apply_filters( "itsec-admin-page-file-path-$id", $file );

		if ( is_file( $file ) ) {
			require_once( $file );
		}
	}

	public function handle_user_setting() {
		$whitelist_settings = array(
			'itsec-settings-view'
		);

		if ( in_array( $_REQUEST['setting'], $whitelist_settings ) ) {
			$_REQUEST['setting'] = sanitize_title_with_dashes( $_REQUEST['setting'] );

			// Verify nonce is valid and for this setting, and allow a filter to
			if ( wp_verify_nonce( $_REQUEST['itsec-user-setting-nonce'], 'set-user-setting-' . $_REQUEST['setting'] ) &&
				apply_filters( 'itsec-user-setting-valid-' . $_REQUEST['setting'], true, $_REQUEST['value'] ) ) {

				if ( false !== update_user_meta( get_current_user_id(), $_REQUEST['setting'], $_REQUEST['value'] ) ) {
					wp_send_json_success();
				}

			}
		}
		wp_send_json_error();
	}

	public function validate_view( $valid, $view ) {
		return in_array( $view, array( 'grid', 'list' ) );
	}
}

new ITSEC_Admin_Page_Loader();
