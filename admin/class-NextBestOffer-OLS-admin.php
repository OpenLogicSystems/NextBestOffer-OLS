<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wordpress.org/plugins/nextbestoffer-ols/
 * @since      1.0.0
 *
 * @package    NextBestOffer_OLS
 * @subpackage NextBestOffer_OLS/admin
 * @author     Open Logic Systems <info@open-ls.de>
 */
class NextBestOffer_OLS_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *handle_start_training
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action(	'admin_menu', array( $this, 'add_confirmation_submenu'));
		add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );
		add_action( 'admin_init', array( $this, 'handle_confirm_start_training' ) );
		add_action( 'admin_init', array( $this, 'start_training' ) );
		add_action( 'admin_init', array( $this, 'get_logs' ) );
		add_action( 'updated_option', array( $this, 'on_option_updated' ), 10, 3 );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in NextBestOffer_OLS_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The NextBestOffer_OLS_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/NextBestOffer-OLS-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in NextBestOffer_OLS_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The NextBestOffer_OLS_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/NextBestOffer-OLS-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function add_admin_menu() {
		add_options_page(
			'NextBestOffer',
			'NextBestOffer Plugin',
			'manage_options',
			'NextBestOffer_OLS_options',
			array($this, 'display_nextbestoffer_ols_menu'),
			30
		);
	}

	public function add_confirmation_submenu() {
		add_submenu_page(
			null, // Kein Elternelement, also wird es nicht im Menü angezeigt
			'Training Confirmation',
			'Training Confirmation',
			'manage_options',
			'nextbestoffer_ols_confirmation',
			array($this, 'display_confirmation_page')
		);
	}

	public function display_nextbestoffer_ols_menu() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/NextBestOffer-OLS-admin-display.php';
	}

	public function display_confirmation_page() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/NextBestOffer-OLS-admin-confirm.php';
	}

	public function register_plugin_settings() {
		register_setting( 'NextBestOffer_OLS_credentials', 'NextBestOffer_OLS_use_case', 'sanitize_text_field' );
		register_setting( 'NextBestOffer_OLS_credentials', 'NextBestOffer_OLS_api_key', 'sanitize_text_field' );

		register_setting( 'NextBestOffer_OLS_model_settings', 'NextBestOffer_OLS_max_rule_length', 'sanitize_text_field' );
		register_setting( 'NextBestOffer_OLS_model_settings', 'NextBestOffer_OLS_min_support', 'sanitize_text_field' );
		register_setting( 'NextBestOffer_OLS_model_settings', 'NextBestOffer_OLS_min_confidence', 'sanitize_text_field' );
		register_setting( 'NextBestOffer_OLS_model_settings', 'NextBestOffer_OLS_training_mode', array( $this, 'sanitize_training_mode' ) );
		register_setting( 'NextBestOffer_OLS_model_settings', 'NextBestOffer_OLS_batch_size', array( $this, 'sanitize_batch_size' ) );
		register_setting( 'NextBestOffer_OLS_model_settings', 'NextBestOffer_OLS_email_recommendations', array( $this, 'sanitize_email_recommendations' ) );

		register_setting( 'NextBestOffer_OLS_partial_selection', 'NextBestOffer_OLS_selected_partial' );
	}	

	public function sanitize_batch_size($input) {
		$input = intval($input); 
	
		if ($input < 500) {
			return 500;
		} elseif ($input > 4000) {
			return 4000;
		}
	
		return $input;
	}

	public function sanitize_training_mode( $mode ) {
		if ( $mode === 'transaction_related' || $mode === 'customer_related' ) {
			return $mode;
		}
		return 'transaction_related';
	}

	public function sanitize_email_recommendations( $option) {
		if ( $option === 'enabled' || $option === 'disabled') {
			return $option;
		}
		return 'enabled';
	}

	public function on_option_updated( $option, $old_value, $new_value ) {
		if ( 'NextBestOffer_OLS_use_case' === $option || 'NextBestOffer_OLS_api_key' === $option ) {
			if ( $new_value !== $old_value ) {
				/* translators: 1: option name 2: new value */
				$this->add_success( sprintf( esc_html__( '%1$s Value updated to %2$s', 'nextbestoffer-ols' ), $option, $new_value ) );
			}
		}

		if ( 'NextBestOffer_OLS_max_rule_length' === $option || 'NextBestOffer_OLS_min_support' === $option || 'NextBestOffer_OLS_min_confidence' === $option ) {
			if ( $new_value !== $old_value ) {
				$response = NextBestOffer_OLS_MDM_Calls::update_config();
				if ( $response ) {
					/* translators: 1: option name 2: new value */
					$this->add_success( sprintf( esc_html__( '%1$s Value updated to %2$s', 'nextbestoffer-ols' ), $option, $new_value ) );
				} else {
					$this->add_error( esc_html__( 'An error occurred.', 'nextbestoffer-ols' ) );
				}
			}	
		}

		if ( 'NextBestOffer_OLS_training_mode' === $option ) {
			if ( $new_value !== $old_value ) {
				/* translators: 1: New value of the training mode. */
				$this->add_success( sprintf( esc_html__( 'Training mode changed to %1$s', 'nextbestoffer-ols' ), $new_value ) );
			}
		}

		if ( 'NextBestOffer_OLS_email_recommendations' === $option ) {
			if ( $new_value !== $old_value ) {
				/* translators: 1: New value of the training mode. */
				$this->add_success( sprintf( esc_html__( 'Email recommendations changed to %1$s', 'nextbestoffer-ols' ), $new_value ) );
			}
		}
	}
	public function handle_confirm_start_training() {
		// Check for the existence of the specific form submit action and nonce verification
		if (isset($_POST['confirm_start_training']) && isset($_POST['default_nonce']) && wp_verify_nonce($_POST['default_nonce'], 'default_action')) {
			
			// Check user capability
			if (!current_user_can('manage_options')) {
				$this->add_error(esc_html__('You do not have sufficient permissions to access this page.', 'nextbestoffer-ols'));
				return;
			}
	
			// Check if training is already running
			if (get_transient('NextBestOffer_OLS_training_status') === 'running') {
				$this->add_error(esc_html__('Training is already running. Please try again later (max. 10 minutes).', 'nextbestoffer-ols'));
				return;
			} else {
				wp_redirect(admin_url('admin.php?page=nextbestoffer_ols_confirmation'));
				exit;
			}
	
		} else if (isset($_POST['confirm_start_training'])) {
			$this->add_error(esc_html__('Nonce verification failed.', 'nextbestoffer-ols'));
		}
	}

	public function start_training() {
		if ( isset( $_POST['start_training'] ) && isset( $_POST['start_training_nonce'] ) && wp_verify_nonce( $_POST['start_training_nonce'], 'start_training_action' ) ) {

			if ( ! current_user_can( 'manage_options' ) ) {
				$this->add_error( esc_html__( 'You do not have sufficient permissions to access this action.', 'nextbestoffer-ols' ) );
				return;
			}
		
			//does not work because notice are only displayed when php process is completed
			//$this->add_info( esc_html__( 'Attempting to send data and start training. Please do not close this page and wait for a notification.', 'nextbestoffer-ols' ) );
		
			if ( get_option( 'NextBestOffer_OLS_use_case' ) && get_option( 'NextBestOffer_OLS_api_key' )) {
				set_transient('NextBestOffer_OLS_training_status', 'running', 600); // Expires in 10 minutes
				$response = NextBestOffer_OLS_MDM_Calls::addDataAndTrain();
			} else {
				error_log('Option "api key" or "use_case" missing');
				$response = false;
			}
		
			if ( $response === 'training_running' ) {
				$this->add_error( esc_html__( 'Training is already running. Please try again later.', 'nextbestoffer-ols' ) );
			} elseif ( $response === 'no_orders' ) {
				$this->add_error( esc_html__( 'No Orders to train the model.', 'nextbestoffer-ols' ) );
			} elseif ( $response ) {
				$this->add_success( esc_html__( 'Training started', 'nextbestoffer-ols' ) );
			} else {
				$this->add_error( esc_html__( 'An error has occurred. Please check your customer ID and API key.', 'nextbestoffer-ols' ) );
			}
			wp_redirect(admin_url('admin.php?page=NextBestOffer_OLS_options'));
			delete_transient('NextBestOffer_OLS_training_status');
			exit;
		
		} else if ( isset( $_POST['start_training'] ) ) {
			$this->add_error( esc_html__( 'Nonce verification failed.', 'nextbestoffer-ols' ) );
		}		
	}

	public function get_logs() {
		if ( isset( $_POST['get_logs'] ) && isset( $_POST['logs_nonce'] ) && wp_verify_nonce( $_POST['logs_nonce'], 'logs_action' ) ) {
	
			if ( ! current_user_can( 'manage_options' ) ) {
				$this->add_error( esc_html__( 'You do not have sufficient permissions to access this page.', 'nextbestoffer-ols' ) );
				return;
			}
	
			if ( get_option( 'NextBestOffer_OLS_use_case' ) && get_option( 'NextBestOffer_OLS_api_key' )) {
				$logs = NextBestOffer_OLS_MDM_Calls::get_logs();
				if ($logs !== false) {
					update_option( 'NextBestOffer_OLS_logs', $logs );
				} else {
					update_option( 'NextBestOffer_OLS_logs', esc_html__( 'Error retrieving the logs.', 'nextbestoffer-ols' ) );
				}
			} else {
				$this->add_error( esc_html__( 'Customer ID or API key missing.', 'nextbestoffer-ols' ) );
			}
	
		} else if ( isset( $_POST['get_logs'] ) ) {
			$this->add_error( esc_html__( 'Nonce verification failed.', 'nextbestoffer-ols' ) );
		}
	}

	private function add_error($msg) {
		add_settings_error(
			'NextBestOffer_OLS',
			'NextBestOffer_OLS_api_error',
			$msg,
			'error'
		);
	}

	private function add_success($msg) {
		add_settings_error(
			'NextBestOffer_OLS',
			'NextBestOffer_OLS_api_success',
			$msg,
			'success'
		);
	}

	private function add_info($msg) {
		add_settings_error(
			'NextBestOffer_OLS',
			'NextBestOffer_OLS_api_info',
			$msg,
			'info'
		);
	}

}
