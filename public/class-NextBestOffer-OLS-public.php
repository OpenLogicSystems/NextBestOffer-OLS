<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wordpress.org/plugins/NextBestOffer-OLS
 * @since      1.0.0
 *
 * @package    NextBestOffer_OLS
 * @subpackage NextBestOffer_OLS/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    NextBestOffer_OLS
 * @subpackage NextBestOffer_OLS/public
 * @author     Open Logic Systems <info@open-ls.de>
 */
class NextBestOffer_OLS_Public {

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
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		#Add Callback after something was purchased
		add_action( 'woocommerce_thankyou', [ $this, 'get_recommendations' ], 10, 1 );

		#Replace default related products by woocomerce with mdm recoms
		add_filter( 'woocommerce_related_products', [ $this, 'get_related_recommendations' ], 10, 3 );

		#Add Callback after clicking on cart and get mdm recoms
		add_filter( 'woocommerce_cart_crosssell_ids', [ $this, 'get_cart_recommendations' ], 10, 1 );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/NextBestOffer-OLS-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/NextBestOffer-OLS-public.js', array( 'jquery' ), $this->version, false );

	}

	public function get_cart_recommendations( $cross_sell_ids ) {
		$cart = WC()->cart->get_cart();
		
		$cart_item_ids = [];
		foreach ( $cart as $cart_item_key => $cart_item ) {
			$product_id = $cart_item['product_id'];
			$cart_item_ids[] = $product_id;
		}
		
		$kunde_case_id = get_option( 'NextBestOffer_OLS_use_case' );
		$api_key = get_option( 'NextBestOffer_OLS_api_key' );
		
		$recommended_products = NextBestOffer_OLS_MDM_Calls::get_recommendations( $kunde_case_id, $api_key, $cart_item_ids );
		
		if ( !empty($recommended_products) ) {
			return $recommended_products;
		}
		
		return $cross_sell_ids;
	}

	public function get_related_recommendations( $related_posts, $product_id, $args ) {
        $kunde_case_id = get_option( 'NextBestOffer_OLS_use_case' );
        $api_key = get_option( 'NextBestOffer_OLS_api_key' );

		$product_id_array = [$product_id];

		$recommended_products = NextBestOffer_OLS_MDM_Calls::get_recommendations( $kunde_case_id, $api_key, $product_id_array );
	
		if ( !empty($recommended_products) ) {
			return $recommended_products;
		}
	
		return $related_posts;
    }

	public function get_recommendations( $order_id ) {
		if ( ! $order_id ) {
			return;
		}
		$order = wc_get_order( $order_id );
		$items = $order->get_items();

		$item_ids = [];
		foreach ( $items as $item ) {
			$product_id = $item->get_product_id();
			$item_ids[] = $product_id;
		}

		$kunde_case_id = get_option( 'NextBestOffer_OLS_use_case' );
		$api_key = get_option( 'NextBestOffer_OLS_api_key' );

		$recommended_products = NextBestOffer_OLS_MDM_Calls::get_recommendations( $kunde_case_id, $api_key, $item_ids );
		
		$this->display_recommendations($recommended_products);
	}

	public function display_recommendations($recommendations) {
		if (empty($recommendations)) {
			#error_log("Keine Produktempfehlungen gefunden");
		} else {

			$selected_partial = get_option('NextBestOffer_OLS_selected_partial', 'partial-1');
			
			switch ($selected_partial) {
				case 'partial-1':
					$partial_path = plugin_dir_path(__FILE__) . 'partials/NextBestOffer-OLS-public-display.php';
					break;
				case 'partial-2':
					$partial_path = plugin_dir_path(__FILE__) . 'partials/NextBestOffer-OLS-public-display-2.php';
					break;
				default:
					$partial_path = plugin_dir_path(__FILE__) . 'partials/NextBestOffer-OLS-public-display.php';
					break;
			}

			if (file_exists($partial_path)) {
				include $partial_path;
			} else {
				_e('Fehler beim Laden der Empfehlungen.', 'NextBestOffer-OLS');
			}
		}
	}
}