<?php
/**
 * The file that defines the external recommendations API class
 *
 * @link       https://wordpress.org/plugins/NextBestOffer-OLS
 * @since      1.0.0
 *
 * @package    NextBestOffer_OLS
 * @subpackage NextBestOffer_OLS/includes
 */

/**
 * The external recommendations API class.
 *
 * This class is responsible for interacting with the external recommendations API.
 *
 * @since      1.0.0
 * @package    NextBestOffer_OLS
 * @subpackage NextBestOffer_OLS/includes
 * @author     Open Logic Systems <info@open-ls.de>
 */
class NextBestOffer_OLS_MDM_Calls {

	public static function addDataAndTrain() {
		$api_url = MDM_SERVICE_URL . '/addDataAndTrain';

		$query = new WC_Order_Query(
			array(
				'limit' => -1,
				'type' => 'shop_order',
				'status' => array('wc-completed')
			)
		);
		$orders = $query->get_orders();

		$orders_with_items = array();
		foreach ($orders as $order) {
			//skip order if one method is not available
			if (!method_exists($order, 'get_id')
				|| !method_exists($order, 'get_customer_id')
				|| !method_exists($order, 'get_date_created')
				|| !method_exists($order, 'get_items')) {
				continue;
			}

			$order_id = $order->get_id();
			$customer_id = $order->get_customer_id();
			$date_created = $order->get_date_created();
			$items = $order->get_items();

			//check if a variable is empty string, NULL, integer 0, or an array with no elements
			if (empty($order_id) || empty($customer_id) || empty($date_created) || empty($items)) {
				error_log("one empty order property");
				continue;
			}
			$date_created_gmt = $date_created->format('Y-m-d\TH:i:s');

			$order_with_items = array(
				'id' => $order_id,
				'customer_id' => $customer_id,
				'line_items' => array(),
				'date_created_gmt' => $date_created_gmt
			);

			foreach ($items as $item) {
				//skip item if one method is not available
				if (!method_exists($item, 'get_product_id')
					|| !method_exists($item, 'get_product')) {
					continue;
				}
				$product_id = $item->get_product_id();
				$product = $item->get_product();

				//check if a variable is empty string, NULL, integer 0, or an array with no elements
				if (empty($product_id) || empty($product)) {
					continue;
				}

				if (!method_exists($product, 'get_name')) {
					continue;
				}
				$item_name = $product->get_name();

				//check if a variable is empty string, NULL, integer 0, or an array with no elements
				if (empty($item_name)) {
					continue;
				}

				$line_item = array(
					'name' => $item_name,
					'product_id' => $product_id
				);

				$order_with_items['line_items'][] = $line_item;
			}

			//add only orders with line_items
			if (!empty($order_with_items['line_items'])) {
				$orders_with_items[] = $order_with_items;
			}
		}

		if (empty($orders_with_items)) {
			return 'no_orders';
		}

		$params = array(
			'usecaseID' => get_option( 'NextBestOffer_OLS_use_case' ),
			'apiKey' => get_option( 'NextBestOffer_OLS_api_key' )
		);

		if ( get_option( 'NextBestOffer_OLS_training_mode' ) ) {
			if (get_option( 'NextBestOffer_OLS_training_mode' ) === 'customer_related') {
				$params['customer_related_recommendations'] = true;
			}
			else {
				$params['customer_related_recommendations'] = false;
			}
		}

		$url = add_query_arg( $params, $api_url );
		$body = json_encode($orders_with_items, JSON_UNESCAPED_UNICODE);
		$args = array(
			'body' => $body,
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'timeout' => 90,
		);

		$response = wp_remote_post($url, $args);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( $response['response']['code'] == 500 ) {
			$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
			if (isset($response_body['detail']) && $response_body['detail'] == 'Training is currently running') {
				// return special error code or message
				return 'training_running';
			}
			return false;
		}
	
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
	
		if ( isset( $response_body['message'] ) ) {
			//Message "started Training vom mdm booster
			return true;
		}
	
		return false;
	}

	public static function get_recommendations( $case_id, $api_key, $item_ids ) {
		$api_url = MDM_SERVICE_URL . '/predict';

		$params = array(
			'usecaseID' => $case_id,
			'apiKey' => $api_key,
		);

		$url = add_query_arg( $params, $api_url );

		$body = json_encode($item_ids, JSON_UNESCAPED_UNICODE);
		$args = array(
			'body' => $body,
			'headers' => array(
				'Content-Type' => 'application/json',
			),
		);

		$response = wp_remote_post($url, $args);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		if (isset($response_body['consequents'])) {
			// max. 4 elements
			$limited_consequents = array_slice($response_body['consequents'], 0, 4);
			return $limited_consequents;
		}

		return false;
	}

	public static function update_config() {
		$api_url = MDM_SERVICE_URL . '/updateConfig';
	
		$params = array(
			'usecaseID' => get_option( 'NextBestOffer_OLS_use_case' ),
			'apiKey' => get_option( 'NextBestOffer_OLS_api_key' ),
		);
	
		// Config Data
		$config_data = array(
			'max_rule_length' => get_option( 'NextBestOffer_OLS_max_rule_length' ),
			'min_support' => get_option( 'NextBestOffer_OLS_min_support' ),
			'min_confidence' => get_option( 'NextBestOffer_OLS_min_confidence' ),
		);
	
		$url = add_query_arg( $params, $api_url );

		$args = array(
			'body' => json_encode($config_data),
			'headers' => array(
				'Content-Type' => 'application/json',
			),
		);
	
		$response = wp_remote_post( $url, $args );
	
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $response_body['message'] ) ) {
			//Message "ok" -> config updated
			return true;
		}

		return false;
	}

	public static function get_logs() {
		$api_url = MDM_SERVICE_URL . '/getLogs';
	
		$params = array(
			'usecaseID' => get_option( 'NextBestOffer_OLS_use_case' ),
			'apiKey' => get_option( 'NextBestOffer_OLS_api_key' ),
		);
	
		$url = add_query_arg( $params, $api_url );

		$response = wp_remote_get( $url );
	
		if ( is_wp_error( $response ) ) {
			return false;
		}
	
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
	
		if (isset($response_body['logs'])) {
			return $response_body['logs'];
		}
	
		return false;
	}
}