<?php

require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( dirname( __FILE__ ) . '/lib/chunk-scanner.php' );
require_once( dirname( __FILE__ ) . '/lib/hash-comparator.php' );
require_once( dirname( __FILE__ ) . '/lib/hash-comparator-loadable.php' );
require_once( dirname( __FILE__ ) . '/lib/hash-comparator-chain.php' );
require_once( dirname( __FILE__ ) . '/lib/hash-comparator-core.php' );
require_once( dirname( __FILE__ ) . '/lib/hash-comparator-managed-files.php' );
require_once( dirname( __FILE__ ) . '/lib/hash-loading-failed-exception.php' );
require_once( dirname( __FILE__ ) . '/lib/package.php' );
require_once( dirname( __FILE__ ) . '/lib/package-core.php' );
require_once( dirname( __FILE__ ) . '/lib/package-factory.php' );
require_once( dirname( __FILE__ ) . '/lib/package-plugin.php' );
require_once( dirname( __FILE__ ) . '/lib/package-system.php' );
require_once( dirname( __FILE__ ) . '/lib/package-theme.php' );
require_once( dirname( __FILE__ ) . '/lib/package-unknown.php' );

do_action( 'itsec_load_file_change_scanner' );

class ITSEC_File_Change_Scanner {

	// Also used in ITSEC_File_Change::health_check();
	const STORAGE = 'itsec_file_change_scan_progress';
	const DESTROYED = 'itsec_file_change_scan_destroyed';
	const FILE_LIST = 'itsec_file_list';

	const C_ADMIN = 'admin';
	const C_INCLUDES = 'includes';
	const C_CONTENT = 'content';
	const C_UPLOADS = 'uploads';
	const C_THEMES = 'themes';
	const C_PLUGINS = 'plugins';
	const C_OTHERS = 'others';

	const S_NONE = 0;
	const S_NORMAL = 1;
	const S_BAD_CHANGE = 2;
	const S_UNKNOWN_FILE = 3;

	const T_ADDED = 'a';
	const T_CHANGED = 'c';
	const T_REMOVED = 'r';

	/** @var ITSEC_File_Change_Hash_Comparator */
	private $comparator;

	/** @var ITSEC_File_Change_Package_Factory */
	private $package_factory;

	/** @var array */
	private $settings;

	/** @var array */
	private $chunk_order;

	/** @var ITSEC_File_Change_Chunk_Scanner */
	private $chunk_scanner;

	/**
	 * ITSEC_New_File_Change_Scanner constructor.
	 *
	 * @param ITSEC_File_Change_Chunk_Scanner   $chunk_scanner
	 * @param ITSEC_File_Change_Hash_Comparator $comparator
	 * @param ITSEC_File_Change_Package_Factory $package_factory
	 */
	public function __construct( ITSEC_File_Change_Chunk_Scanner $chunk_scanner = null, ITSEC_File_Change_Hash_Comparator $comparator = null, ITSEC_File_Change_Package_Factory $package_factory = null ) {
		$this->chunk_scanner   = $chunk_scanner;
		$this->comparator      = $comparator;
		$this->package_factory = $package_factory;
		$this->settings        = ITSEC_Modules::get_settings( 'file-change' );

		$this->chunk_order = array(
			self::C_ADMIN,
			self::C_INCLUDES,
			self::C_CONTENT,
			self::C_UPLOADS,
			self::C_THEMES,
			self::C_PLUGINS,
			self::C_OTHERS,
		);
	}

	/**
	 * Schedule a scan to start.
	 *
	 * @param bool            $user_initiated
	 * @param ITSEC_Scheduler $scheduler
	 *
	 * @return bool|WP_Error
	 */
	public static function schedule_start( $user_initiated = true, $scheduler = null ) {

		$scheduler = $scheduler ? $scheduler : ITSEC_Core::get_scheduler();

		if ( self::is_running( $scheduler ) ) {
			return new WP_Error( 'itsec-file-change-scan-already-running', __( 'A File Change scan is currently in progress.', 'it-l10n-ithemes-security-pro' ) );
		}

		if ( $user_initiated ) {
			$id   = 'file-change-fast';
			$opts = array( 'fire_at' => ITSEC_Core::get_current_time_gmt() );
		} else {
			$id   = 'file-change';
			$opts = array();
		}

		$scheduler->schedule_loop( $id, array(
			'step'  => 'get-files',
			'chunk' => self::C_ADMIN,
		), $opts );

		return true;
	}

	/**
	 * Check if a scan is running.
	 *
	 * @param ITSEC_Scheduler
	 *
	 * @return bool
	 */
	public static function is_running( $scheduler = null ) {

		$scheduler = $scheduler ? $scheduler : ITSEC_Core::get_scheduler();

		if ( $scheduler->is_single_scheduled( 'file-change-fast', null ) ) {
			return true;
		}

		if ( false !== get_site_option( self::STORAGE ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the scan status.
	 *
	 * @param bool $is_running
	 *
	 * @return array
	 */
	public static function get_status( $is_running = true ) {
		$scheduler = ITSEC_Core::get_scheduler();

		$progress = get_site_option( self::STORAGE );

		if ( $progress ) {
			switch ( $progress['step'] ) {
				case 'get-files':
					switch ( $progress['chunk'] ) {
						case self::C_ADMIN:
							$message = esc_html__( 'Scanning admin files...', 'it-l10n-ithemes-security-pro' );
							break;
						case self::C_INCLUDES:
							$message = esc_html__( 'Scanning includes files...', 'it-l10n-ithemes-security-pro' );
							break;
						case self::C_THEMES:
							$message = esc_html__( 'Scanning theme files...', 'it-l10n-ithemes-security-pro' );
							break;
						case self::C_PLUGINS:
							$message = esc_html__( 'Scanning plugin files...', 'it-l10n-ithemes-security-pro' );
							break;
						case self::C_CONTENT:
							$message = esc_html__( 'Scanning content files...', 'it-l10n-ithemes-security-pro' );
							break;
						case self::C_UPLOADS:
							$message = esc_html__( 'Scanning media files...', 'it-l10n-ithemes-security-pro' );
							break;
						case self::C_OTHERS:
						default:
							$message = esc_html__( 'Scanning files...', 'it-l10n-ithemes-security-pro' );
							break;
					}
					break;
				case 'compare-files':
					$message = esc_html__( 'Comparing files...', 'it-l10n-ithemes-security-pro' );
					break;
				case 'check-hashes':
					$message = esc_html__( 'Verifying file changes...', 'it-l10n-ithemes-security-pro' );
					break;
				case 'scan-files':
					$message = esc_html__( 'Checking for malware...', 'it-l10n-ithemes-security-pro' );
					break;
				case 'complete':
					$message = esc_html__( 'Wrapping up...', 'it-l10n-ithemes-security-pro' );
					break;
				default:
					$message = esc_html__( 'Scanning...', 'it-l10n-ithemes-security-pro' );
					break;
			}

			$status = array(
				'running' => true,
				'step'    => $progress['step'],
				'chunk'   => $progress['chunk'],
				'health'  => $progress['health_check'],
				'message' => $message,
			);
		} elseif ( get_site_option( self::DESTROYED ) ) {
			delete_site_option( self::DESTROYED );
			$status = array(
				'running' => false,
				'aborted' => true,
				'message' => esc_html__( 'Scan could not be completed. Please contact support if this error persists.', 'it-l10n-ithemes-security-pro' ),
			);
		} elseif ( self::is_running( $scheduler ) ) {
			$status = array(
				'running' => true,
				'message' => esc_html__( 'Preparing...', 'it-l10n-ithemes-security-pro' ),
			);
		} elseif ( $is_running ) {
			$status = array(
				'running'       => false,
				'complete'      => true,
				'message'       => esc_html__( 'Complete!', 'it-l10n-ithemes-security-pro' ),
				'found_changes' => ITSEC_Modules::get_setting( 'file-change', 'last_scan' ),
			);
		} else {
			$status = array(
				'running' => false,
				'message' => '',
			);
		}

		return $status;
	}

	/**
	 * Recover from a failed health check.
	 *
	 * @return bool Whether the scan was recovered. Will return false if aborted.
	 */
	public static function recover() {

		ITSEC_Lib::get_lock( 'file-change-recover' );

		$storage   = self::all_storage();
		$scheduler = ITSEC_Core::get_scheduler();

		ITSEC_Log::add_debug( 'file_change', 'attempting-recovery', array(
			'storage' => array(
				'step'         => $storage['step'],
				'chunk'        => $storage['chunk'],
				'id'           => $storage['id'],
				'data'         => $storage['data'],
				'memory'       => $storage['memory'],
				'memory_peak'  => $storage['memory_peak'],
				'health_check' => $storage['health_check'],
			)
		) );

		if ( empty( $storage['step'] ) ) {
			ITSEC_Log::add_debug( 'file_change', 'recovery-failed-no-step' );

			self::abort();

			return false;
		}

		$job_data          = $storage['data'];
		$job_data['step']  = $storage['step'];
		$job_data['chunk'] = $storage['chunk'];

		$job = new ITSEC_Job( $scheduler, $storage['id'], $job_data, array( 'single' => true ) );

		if ( 5 < $job->is_retry() ) {
			ITSEC_Log::add_debug( 'file_change', 'recovery-failed-too-many-retries' );

			self::abort();

			ITSEC_Lib::release_lock( 'file-change-recover' );

			return false;
		}

		$job->reschedule_in( 30 );

		ITSEC_Log::add_debug( 'file_change', 'recovery-scheduled', compact( 'job' ) );
		ITSEC_Lib::release_lock( 'file-change-recover' );

		return true;
	}

	/**
	 * Abort an in-progress scan.
	 */
	public static function abort() {
		$storage = self::all_storage();

		if ( 'file-change' === $storage['id'] ) {
			ITSEC_Core::get_scheduler()->unschedule_single( 'file-change', null );
			self::schedule_start( false );
		} else {
			ITSEC_Core::get_scheduler()->unschedule_single( 'file-change-fast', null );
		}

		if ( ! empty( $storage['process'] ) ) {
			ITSEC_Log::add_process_stop( $storage['process'], array( 'aborted' => true ) );
		}

		ITSEC_Log::add_fatal_error( 'file_change', 'file-scan-aborted', array(
			'id'    => $storage['id'],
			'step'  => $storage['step'],
			'chunk' => $storage['chunk'],
		) );

		self::clear_storage();
		update_site_option( self::DESTROYED, ITSEC_Core::get_current_time_gmt() );
	}

	/**
	 * Handle a Job.
	 *
	 * @param ITSEC_Job $job
	 */
	public function run( ITSEC_Job $job ) {

		$data = $job->get_data();

		if ( empty( $data['step'] ) ) {
			self::recover();

			return;
		}

		if ( ! $this->allow_to_run( $job ) ) {
			$job->reschedule_in( 10 * MINUTE_IN_SECONDS );

			return;
		}

		ITSEC_Lib::set_minimum_memory_limit( '512M' );
		@set_time_limit( 0 );

		if ( ! defined( 'ITSEC_DOING_FILE_CHECK' ) ) {
			define( 'ITSEC_DOING_FILE_CHECK', true );
		}

		if ( 1 === $data['loop_item'] ) {
			$settings = $this->settings;
			unset( $settings['latest_changes'] );

			$process = ITSEC_Log::add_process_start( 'file_change', 'scan', array(
				'settings'       => $settings,
				'scheduled_call' => 'file-change' === $job->get_id(),
			) );
			$this->update_storage( 'process', $process );
			$this->update_storage( 'id', $job->get_id() );
			delete_site_option( self::DESTROYED );
		}

		$this->update_storage( 'data', $data );
		$this->update_storage( 'step', $data['step'] );

		$memory_used = @memory_get_peak_usage();

		switch ( $data['step'] ) {
			case 'get-files':
				$this->get_files( $job );
				break;
			case 'compare-files':
				$this->compare_files( $job );
				break;
			case 'check-hashes':
				$this->check_hashes( $job );
				break;
			case 'complete':
				$this->complete( $job );
				break;
		}

		if ( ! self::has_storage() ) {
			return;
		}

		$check_memory = @memory_get_peak_usage();

		if ( $check_memory > $memory_used ) {
			$memory_used = $check_memory - $memory_used;
		}

		if ( $memory_used > $this->get_storage( 'memory' ) ) {
			$this->update_storage( 'memory', $memory_used );
			$this->update_storage( 'memory_peak', $check_memory );
		}
	}

	/**
	 * Should we allow a scan to be run now.
	 *
	 * This is used to block a scheduled scan from running while a user initiated scan is currently processing.
	 *
	 * @param ITSEC_Job $job
	 *
	 * @return bool
	 */
	private function allow_to_run( ITSEC_Job $job ) {

		if ( 'file-change' !== $job->get_id() ) {
			return true;
		}

		if ( ITSEC_Core::get_scheduler()->is_single_scheduled( 'file-change-fast', null ) ) {
			return false;
		}

		$data = $job->get_data();

		// Don't allow starting a slow file change scan if one is already in progress and running.
		if ( 1 === $data['loop_item'] && get_site_option( self::STORAGE ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the hashes and date modify times for all files in the requested chunk.
	 *
	 * This will write the file list to step storage and schedule the next chunk.
	 * If last chunk, will schedule the compare-files step.
	 *
	 * @param ITSEC_Job $job
	 */
	private function get_files( ITSEC_Job $job ) {

		$data = $job->get_data();
		$this->update_storage( 'chunk', $data['chunk'] );

		ITSEC_Log::add_process_update( $this->get_storage( 'process' ), array(
			'status' => 'get_chunk_files',
			'chunk'  => $data['chunk'],
		) );

		$file_list = $this->get_chunk_scanner()->scan( $data['chunk'] );

		$this->merge_storage( 'file_list', $file_list );

		$pos = array_search( $data['chunk'], $this->chunk_order, true );

		if ( isset( $this->chunk_order[ $pos + 1 ] ) ) {
			$job->schedule_next_in_loop( array(
				'chunk' => $this->chunk_order[ $pos + 1 ],
			) );
		} else {
			ITSEC_Log::add_process_update( $this->get_storage( 'process' ), array( 'status' => 'file_scan_complete' ) );
			$job->schedule_next_in_loop( array(
				'step' => 'compare-files'
			) );
		}
	}

	/**
	 * Compare the list of file hashes to determine what files have been added/changed/removed.
	 *
	 * If there are no file changes, the scan will be completed. Otherwise it will schedule a job
	 * to check the hashes.
	 *
	 * @param ITSEC_Job $job
	 */
	private function compare_files( ITSEC_Job $job ) {

		$excludes = array();

		foreach ( $this->settings['file_list'] as $file ) {
			$cleaned              = untrailingslashit( get_home_path() . ltrim( $file, '/' ) );
			$excludes[ $cleaned ] = 1;
		}

		$types = array_flip( $this->settings['types'] );

		ITSEC_Log::add_process_update( $this->get_storage( 'process' ), array( 'status' => 'file_comparisons_start', 'excludes' => $excludes, 'types' => $types ) );

		$current_files = $this->get_storage( 'file_list' );
		$prev_files    = self::get_file_list_to_compare();

		$report = array();

		foreach ( $current_files as $file => $attr ) {
			if ( ! isset( $prev_files[ $file ] ) ) {
				$attr['t']       = self::T_ADDED;
				$report[ $file ] = $attr;
			} elseif ( $prev_files[ $file ]['h'] !== $attr['h'] ) {
				$attr['t']       = self::T_CHANGED;
				$report[ $file ] = $attr;
			}

			unset( $prev_files[ $file ] );
		}

		foreach ( $prev_files as $file => $attr ) {

			if ( isset( $excludes[ $file ] ) ) {
				continue;
			}

			foreach ( $excludes as $exclude => $_ ) {
				if ( 0 === strpos( $file, trailingslashit( $exclude ) ) ) {
					continue 2;
				}
			}

			$extension = '.' . pathinfo( $file, PATHINFO_EXTENSION );

			if ( isset( $types[ $extension ] ) ) {
				continue;
			}

			$attr['t']       = self::T_REMOVED;
			$report[ $file ] = $attr;
		}

		ITSEC_Log::add_process_update( $this->get_storage( 'process' ), array( 'status' => 'file_comparisons_complete' ) );

		if ( ! $report ) {
			ITSEC_Log::add_process_update( $this->get_storage( 'process' ), array( 'status' => 'file_comparisons_complete_no_changes' ) );
			$this->complete( $job );

			return;
		}

		$this->update_storage( 'files', $report );
		$job->schedule_next_in_loop( array( 'step' => 'check-hashes' ) );
	}

	/**
	 * Check the file changes with each package's hashes to determine whether the change was expected or not.
	 *
	 * @param ITSEC_Job $job
	 */
	private function check_hashes( ITSEC_Job $job ) {

		ITSEC_Log::add_process_update( $this->get_storage( 'process' ), array( 'status' => 'hash_comparisons_start' ) );

		do_action( 'itsec-file-change-start-hash-comparisons' );

		$factory    = $this->get_package_factory();
		$comparator = $this->get_comparator();
		$packages   = $factory->find_packages_for_files( $this->get_storage( 'files' ) );

		foreach ( $packages as $root => $group ) {
			/** @var ITSEC_File_Change_Package $package */
			$package = $group['package'];
			$files   = $group['files'];

			if ( ! $comparator->supports_package( $package ) ) {
				$packages[ $root ]['files'] = $this->set_default_severity( $files );
				continue;
			}

			if ( $comparator instanceof ITSEC_File_Change_Hash_Comparator_Loadable ) {
				try {
					$comparator->load( $package );
				} catch ( ITSEC_File_Change_Hash_Loading_Failed_Exception $e ) {
					$packages[ $root ]['files'] = $this->set_default_severity( $files );
					ITSEC_Log::add_process_update( $this->get_storage( 'process' ), array( 'status' => 'hash_load_failed', 'e' => (string) $e ) );
					continue;
				}
			}

			// $file is a relative path to the package.
			// $attr contains 'h' for the hash, and 'd' for the date modified.
			foreach ( $files as $file => $attr ) {
				switch ( $attr['t'] ) {
					case self::T_ADDED:
						if ( ! $comparator->has_hash( $file, $package ) ) {
							$attr['s'] = self::S_UNKNOWN_FILE;
							break;
						}

						if ( ! $comparator->hash_matches( $attr['h'], $file, $package ) ) {
							// This isn't exactly an unknown file, or a bad change, but it fits more with bad change,
							// and is unlikely to occur so not worth a separate report type.
							$attr['s'] = self::S_BAD_CHANGE;
							break;
						}

						$attr['s'] = self::S_NONE;
						break;
					case self::T_CHANGED:
						if ( ! $comparator->has_hash( $file, $package ) ) {
							break;
						}

						if ( ! $comparator->hash_matches( $attr['h'], $file, $package ) ) {
							$attr['s'] = self::S_BAD_CHANGE;
							break;
						}
						$attr['s'] = self::S_NONE;
						break;
					case self::T_REMOVED:
						if ( ! $comparator->has_hash( $file, $package ) ) {
							$attr['s'] = self::S_NONE;
						}
						break;
				}

				if ( ! isset( $attr['s'] ) ) {
					$attr['s'] = self::S_NORMAL;
				}

				$files[ $file ] = $attr;
			}

			$packages[ $root ]['files'] = $files;
		}

		do_action( 'itsec-file-change-end-hash-comparisons' );

		ITSEC_Log::add_process_update( $this->get_storage( 'process' ), array( 'status' => 'hash_comparisons_complete' ) );

		$this->update_storage( 'packaged', $packages );
		$job->schedule_next_in_loop( array( 'step' => 'complete' ) );
	}

	/**
	 * Run the completion routine.
	 *
	 * @param ITSEC_Job $job
	 */
	private function complete( ITSEC_Job $job ) {

		$storage = self::all_storage();
		self::record_file_list( $storage['file_list'] );

		$list = $this->build_change_list( $storage['packaged'] );

		$list['memory']      = round( ( $storage['memory'] / 1000000 ), 2 );
		$list['memory_peak'] = round( ( $storage['memory_peak'] / 1000000 ), 2 );

		$c_added   = count( $list['added'] );
		$c_changed = count( $list['changed'] );
		$c_removed = count( $list['removed'] );

		$found_changes = $c_added || $c_changed || $c_removed;

		if ( $found_changes ) {

			$severity = $this->get_max_severity( $storage['packaged'] );

			if ( $severity > self::S_UNKNOWN_FILE ) {
				$method = 'add_critical_issue';
			} else {
				$method = 'add_warning';
			}

			$id = ITSEC_Log::$method( 'file_change', "changes-found::{$c_added},{$c_removed},{$c_changed}", $list );
		} else {
			$id = ITSEC_Log::add_notice( 'file_change', 'no-changes-found', $list );
		}

		ITSEC_Modules::set_setting( 'file-change', 'last_scan', $found_changes ? $id : 0 );
		ITSEC_Modules::set_setting( 'file-change', 'latest_changes', $list );

		if ( $found_changes && $this->settings['notify_admin'] ) {
			ITSEC_Modules::set_setting( 'file-change', 'show_warning', true );
		}

		ITSEC_Log::add_process_stop( $this->get_storage( 'process' ) );

		self::clear_storage();

		if ( 'file-change' === $job->get_id() ) {
			$job->schedule_new_loop( array(
				'step'  => 'get-files',
				'chunk' => self::C_ADMIN,
			) );
		}

		$this->send_notification_email( array( $c_added, $c_removed, $c_changed, $list ) );
	}

	/**
	 * Get the comparator to use to check if changes are expected.
	 *
	 * Handles lazily setting the comparator since it is not needed for all stages of the file change scan.
	 *
	 * @return ITSEC_File_Change_Hash_Comparator
	 */
	private function get_comparator() {
		if ( ! $this->comparator ) {
			$comparators = array(
				new ITSEC_File_Change_Hash_Comparator_Managed_Files(),
				new ITSEC_File_Change_Hash_Comparator_Core(),
			);

			/**
			 * Filter the list of comparators to use.
			 */
			$comparators = apply_filters( 'itsec_file_change_comparators', $comparators );

			$this->comparator = new ITSEC_File_Change_Hash_Comparator_Chain( $comparators );
		}

		return $this->comparator;
	}

	/**
	 * Get the Package factory.
	 *
	 * @return ITSEC_File_Change_Package_Factory
	 */
	private function get_package_factory() {
		if ( ! $this->package_factory ) {
			$this->package_factory = new ITSEC_File_Change_Package_Factory();
		}

		return $this->package_factory;
	}

	/**
	 * Get the Chunk Scanner.
	 *
	 * @return ITSEC_File_Change_Chunk_Scanner
	 */
	private function get_chunk_scanner() {
		if ( ! $this->chunk_scanner ) {
			$this->chunk_scanner = new ITSEC_File_Change_Chunk_Scanner( $this->settings );
		}

		return $this->chunk_scanner;
	}

	/**
	 * Set the default severity for a list of files.
	 *
	 * @param array $files
	 *
	 * @return array
	 */
	private function set_default_severity( $files ) {
		foreach ( $files as $file => $attr ) {
			$files[ $file ]['s'] = self::S_NORMAL;
		}

		return $files;
	}

	/**
	 * Get the maximum severity level of a file change.
	 *
	 * @param array $packaged
	 *
	 * @return int
	 */
	private function get_max_severity( $packaged ) {

		$severity = self::S_NONE;

		foreach ( $packaged as $root => $group ) {
			foreach ( $group['files'] as $attr ) {
				if ( $attr['s'] > $severity ) {
					$severity = $attr['s'];
				}
			}
		}

		return $severity;
	}

	/**
	 * Convert a list of packages and their files to a list of the file change types.
	 *
	 * @param array $packaged
	 *
	 * @return array
	 */
	private function build_change_list( $packaged ) {

		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		$home = get_home_path();

		$list = array(
			'added'   => array(),
			'removed' => array(),
			'changed' => array(),
		);

		foreach ( $packaged as $root => $group ) {
			/** @var ITSEC_File_Change_Package $package */
			$package = $group['package'];

			foreach ( $group['files'] as $file => $attr ) {
				if ( $attr['s'] > self::S_NONE && ! empty( $attr['t'] ) ) {
					$path = $package->get_root_path() . $file;

					if ( 0 === strpos( $path, $home ) ) {
						$path = substr( $path, strlen( $home ) );
					}

					$attr['p'] = (string) $package;

					switch ( $attr['t'] ) {
						case self::T_ADDED:
							$list['added'][ $path ] = $attr;
							break;
						case self::T_CHANGED:
							$list['changed'][ $path ] = $attr;
							break;
						case self::T_REMOVED:
							$list['removed'][ $path ] = $attr;
					}
				}
			}
		}

		return $list;
	}

	/**
	 * Merge the new value with any existing values currently in storage.
	 *
	 * @param string $key
	 * @param array  $val
	 */
	private function merge_storage( $key, $val ) {
		$storage         = self::all_storage();
		$storage[ $key ] = array_merge( $storage[ $key ], $val );
		$this->set_storage( $storage );
	}

	/**
	 * Update a single item in storage.
	 *
	 * @param string $key
	 * @param mixed  $val
	 */
	private function update_storage( $key, $val ) {
		$storage         = self::all_storage();
		$storage[ $key ] = $val;
		$this->set_storage( $storage );
	}

	/**
	 * Set the entire storage array to a new value.
	 *
	 * @param array $storage
	 *
	 * @return bool
	 */
	private function set_storage( $storage ) {

		$storage['health_check'] = ITSEC_Core::get_current_time_gmt();

		return update_site_option( self::STORAGE, $storage );
	}

	/**
	 * Get an item from storage.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	private function get_storage( $key ) {
		$storage = self::all_storage();

		return $storage[ $key ];
	}

	/**
	 * Get the whole storage array.
	 *
	 * @return array
	 */
	private static function all_storage() {

		$storage = get_site_option( self::STORAGE, array() );

		if ( ! is_array( $storage ) ) {
			$storage = array();
		}

		return wp_parse_args( $storage, array(
			'step'         => '', // The current step.
			'chunk'        => '', // The current chunk being processed, when getting files.
			'id'           => '', // The job ID.
			'data'         => array(), // The job data.
			'memory'       => 0, // Maximum amount of memory used so far.
			'memory_peak'  => 0, // Maximum amount of memory used so far.
			'health_check' => 0, // Time storage was last updated, to help determine if the process crashed.
			'process'      => array(), // Process data for process logs.
			'file_list'    => array(), // Raw list of files and hashes / change times.
			'files'        => array(), // Processed list of files that have been added, changed, or removed.
			'packaged'     => array(), // Files grouped into their packages.
		) );
	}

	/**
	 * Do we have a storage bucket setup.
	 *
	 * @return bool
	 */
	private static function has_storage() {
		return false !== get_site_option( self::STORAGE );
	}

	/**
	 * Clear the entire storage list.
	 *
	 * @return bool
	 */
	private static function clear_storage() {
		return delete_site_option( self::STORAGE );
	}

	/**
	 * Record a list of file hashes and change times.
	 *
	 * This should not be done until the whole scan process is complete.
	 *
	 * @param array $file_list
	 *
	 * @return bool
	 */
	public static function record_file_list( $file_list ) {

		$list = get_site_option( self::FILE_LIST, array() );

		if ( ! is_array( $list ) || ! $list ) {
			$list = array(
				'home'  => '',
				'files' => array(),
			);
		}

		$list['home'] = get_home_path();

		$files = $list['files'];

		if ( count( $files ) >= 2 ) {
			array_shift( $files );
		}

		$list['files'][ ITSEC_Core::get_current_time_gmt() ] = $file_list;

		return update_site_option( self::FILE_LIST, $list );
	}

	/**
	 * Get the file list we want to compare our newly compared files to.
	 *
	 * This is in effect the last change list recorded.
	 *
	 * @return array
	 */
	public static function get_file_list_to_compare() {

		$list = get_site_option( self::FILE_LIST, array() );

		if ( ! $list || ! is_array( $list ) ) {
			return array();
		}

		$compare = end( $list['files'] );

		if ( ! is_array( $compare ) ) {
			return array();
		}

		$home = $list['home'];

		if ( get_home_path() !== $home ) {
			$new_home = get_home_path();
			$updated  = array();

			foreach ( $compare as $file => $attr ) {
				$updated[ ITSEC_Lib::replace_prefix( $file, $list['home'], $new_home ) ] = $attr;
			}

			update_site_option( self::FILE_LIST, array(
				'home'  => $new_home,
				'files' => array(
					ITSEC_Core::get_current_time_gmt() => $updated,
				),
			) );

			return $updated;
		}

		return $compare;
	}

	/**
	 * Builds and sends notification email
	 *
	 * Sends the notication email too all applicable administrative users notifying them
	 * that file changes have been detected
	 *
	 * @since  4.0.0
	 *
	 * @access private
	 *
	 * @param array $email_details array of details for the email messge
	 *
	 * @return void
	 */
	private function send_notification_email( $email_details ) {

		$changed = $email_details[0] + $email_details[1] + $email_details[2];

		if ( ! $changed ) {
			return;
		}

		$nc = ITSEC_Core::get_notification_center();

		if ( $nc->is_notification_enabled( 'digest' ) ) {
			$nc->enqueue_data( 'digest', array( 'type' => 'file-change' ) );
		}

		if ( $nc->is_notification_enabled( 'file-change' ) ) {
			$mail = $this->generate_notification_email( $email_details );
			$nc->send( 'file-change', $mail );
		}
	}

	/**
	 * Generate the notification email.
	 *
	 * @param array $email_details
	 *
	 * @return ITSEC_Mail
	 */
	private function generate_notification_email( $email_details ) {
		$mail = ITSEC_Core::get_notification_center()->mail();

		$mail->add_header(
			esc_html__( 'File Change Warning', 'it-l10n-ithemes-security-pro' ),
			sprintf( esc_html__( 'File Scan Report for %s', 'it-l10n-ithemes-security-pro' ), '<b>' . date_i18n( get_option( 'date_format' ) ) . '</b>' )
		);
		$mail->add_text( esc_html__( 'A file (or files) on your site have been changed. Please review the report below to verify changes are not the result of a compromise.', 'it-l10n-ithemes-security-pro' ) );

		$mail->add_section_heading( esc_html__( 'Scan Summary', 'it-l10n-ithemes-security-pro' ) );
		$mail->add_file_change_summary( $email_details[0], $email_details[1], $email_details[2] );

		$mail->add_section_heading( esc_html__( 'Scan Details', 'it-l10n-ithemes-security-pro' ) );

		$headers = array( esc_html__( 'File', 'it-l10n-ithemes-security-pro' ), esc_html__( 'Modified', 'it-l10n-ithemes-security-pro' ), esc_html__( 'File Hash', 'it-l10n-ithemes-security-pro' ) );

		if ( $email_details[0] ) {
			$mail->add_large_text( esc_html__( 'Added Files', 'it-l10n-ithemes-security-pro' ) );
			$mail->add_table( $headers, $this->generate_email_rows( $email_details[3]['added'] ) );
		}

		if ( $email_details[1] ) {
			$mail->add_large_text( esc_html__( 'Removed Files', 'it-l10n-ithemes-security-pro' ) );
			$mail->add_table( $headers, $this->generate_email_rows( $email_details[3]['removed'] ) );
		}

		if ( $email_details[2] ) {
			$mail->add_large_text( esc_html__( 'Changed Files', 'it-l10n-ithemes-security-pro' ) );
			$mail->add_table( $headers, $this->generate_email_rows( $email_details[3]['changed'] ) );
		}

		$mail->add_footer();

		return $mail;
	}

	/**
	 * Generate email report rows for a series of files.
	 *
	 * @param array $files
	 *
	 * @return array
	 */
	private function generate_email_rows( $files ) {
		$rows = array();

		foreach ( $files as $item => $attr ) {
			$time = isset( $attr['mod_date'] ) ? $attr['mod_date'] : $attr['d'];

			$rows[] = array(
				$item,
				ITSEC_Lib::date_format_i18n_and_local_timezone( $time ),
				isset( $attr['hash'] ) ? $attr['hash'] : $attr['h']
			);
		}

		return $rows;
	}
}