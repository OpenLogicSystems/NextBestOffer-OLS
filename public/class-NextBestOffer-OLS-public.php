<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wordpress.org/plugins/nextbestoffer-ols/
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
		add_action( 'woocommerce_thankyou', [ $this, 'display_recommendations' ], 10, 1 );
		add_action( 'woocommerce_email_after_order_table', [ $this, 'add_product_grid_specific_email' ], 20, 4 );

		#Replace default related products by woocomerce with mdm recoms
		add_filter( 'woocommerce_related_products', [ $this, 'get_related_recommendations' ], 10, 3 );

		#disable shuffling of predictions
		add_filter( 'woocommerce_output_related_products_args', array($this,'sort_related_products'));
		add_filter( 'woocommerce_product_related_posts_shuffle', '__return_false' );

		add_filter( 'woocommerce_product_related_products_heading', array($this, 'change_header'));

		add_filter( 'post_class', array( $this, 'add_custom_class' ) );

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

	/**add_product_grid_specific_email
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
		 *woocommerce_thankyou
		 * The NextBestOffer_OLS_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/NextBestOffer-OLS-public.js', array( 'jquery' ), $this->version, false );

	}

	function sort_related_products( $args ) {
		#disable shuffling of predictions
		$args['orderby'] = 'none';
		$args['order'] = 'ASC';
		return $args;
	 }

	function change_header() {
		return esc_html__('Customers also bought', 'nextbestoffer-ols');
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

		$recom_mode = get_option( 'NextBestOffer_OLS_recom_mode' );
		
		if ( !empty($recommended_products) ) {
			if ($recom_mode == 'overwrite') {
				global $nbo_current_recommended_product_ids;
				$nbo_current_recommended_product_ids = $recommended_products;
				return $recommended_products;
			} else if ($recom_mode == 'no_overwrite') {
				global $nbo_current_recommended_product_ids;
				$merged_recommendations = array_merge($cross_sell_ids, $recommended_products);
				$recommendations = array_unique($merged_recommendations);
				$recommendations = array_slice($recommendations, 0, 4);
				$nbo_current_recommended_product_ids = $recommendations;
				return $recommendations;
			}
		}
		
		return $cross_sell_ids;
	}

	public function get_related_recommendations( $related_posts, $product_id, $args ) {
        $kunde_case_id = get_option( 'NextBestOffer_OLS_use_case');
        $api_key = get_option( 'NextBestOffer_OLS_api_key' );

		$product_id_array = [$product_id];

		$recommended_products = NextBestOffer_OLS_MDM_Calls::get_recommendations( $kunde_case_id, $api_key, $product_id_array );
		
		$recom_mode = get_option( 'NextBestOffer_OLS_recom_mode' );

		if ( !empty($recommended_products) ) {
			if ($recom_mode == 'overwrite') {
				global $nbo_current_recommended_product_ids;
				$nbo_current_recommended_product_ids = $recommended_products;
				return $recommended_products;
			} else if ($recom_mode == 'no_overwrite') {
				global $nbo_current_recommended_product_ids;
				$merged_recommendations = array_merge($recommended_products, $related_posts);
				$recommendations = array_unique($merged_recommendations);
				$nbo_current_recommended_product_ids = $recommendations;
				return $recommendations;
			}
		}
	
		return $related_posts;
    }

	public function add_custom_class( $classes ) {
		global $nbo_current_recommended_product_ids;
	
		if ( !empty($nbo_current_recommended_product_ids) && in_array( get_the_ID(), $nbo_current_recommended_product_ids ) ) {
			$classes[] = 'NBO-recom';
			unset($nbo_current_recommended_product_ids);
		}
		
		return $classes;
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
		
		return $recommended_products;
	}

	public function display_recommendations( $order_id ) {

		$recommendations = $this->get_recommendations( $order_id );
		if (empty($recommendations)) {
			#error_log("Keine Produktempfehlungen gefunden");
		} else {

			$selected_partial = get_option('NextBestOffer_OLS_selected_partial', 'partial-1');
			
			switch ($selected_partial) {
				case 'none':
					$partial_path = 'none';
					break;
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

			if ($partial_path !== 'none' && file_exists($partial_path)) {
				include $partial_path;
			} else {
				if ($partial_path !== 'none') {
					esc_html_e('Error loading the recommendations.', 'nextbestoffer-ols');
				}
			}
		}
	}

	public function add_product_grid_specific_email( $order, $sent_to_admin, $plain_text, $email ) {

		$selected_option = get_option('NextBestOffer_OLS_email_recommendations');
		
		if ($selected_option === 'disabled') {
			return;
		}

		$order_id = $order->get_order_number();
		$recommendations = $this->get_recommendations( $order_id );
    
		if ( $email->id == 'customer_processing_order' ) {
			
		   echo '<h2>Related Products</h2>';
			
		   $html = '';
		   $col = 1;
		   $cols = 2;
		   $limit = 3;
		   $html .= '<div><table style="table-layout:fixed;width:100%;"><tbody>';     
		   foreach ( $recommendations as $product_id ) {
			if ($col === $limit) {
				break;
			}
			  $product = wc_get_product( $product_id );
			  $html .= ( $col + $cols - 1 ) % $cols === 0 ? '<tr>' : '';
			  $html .= '<td style="text-align:center;vertical-align:bottom">';
			  $html .= wp_kses_post($product->get_image());
			  $html .= '<h3 style="text-align:center">' . esc_html($product->get_title()) . '</h3>';
			  $html .= '<p>' . wp_kses_post($product->get_price_html()) . '</p>';
			  $html .= '<p><a href="' . esc_url(get_permalink($product_id)) . '">' . esc_html__('Read more', 'woocommerce') . '</a></p></td>';
			  $html .= $col % $cols === 0 ? '</tr>' : '';
			  $col++;
		   }
		   $html .= '</tbody></table></div>';
			
		   echo wp_kses_post($html);
			
		}
	}
}
