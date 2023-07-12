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
				'limit' => -1
			));
		$orders = $query->get_orders();

		$orders_with_items = array();
		foreach ($orders as $order) {
			$order_id = $order->get_id();
			$customer_id = $order->get_customer_id();
			$date_created_gmt = $order->get_date_created()->format('Y-m-d\TH:i:s');
			$items = $order->get_items();

			$order_with_items = array(
				'id' => $order_id,
				'customer_id' => $customer_id,
				'line_items' => array(),
				'date_created_gmt' => $date_created_gmt
			);

			foreach ($items as $item) {
				$product_id = $item->get_product_id();
				$product = $item->get_product();
				$item_name = $product->get_name();
				$quantity = $item->get_quantity();
				$subtotal = $item->get_subtotal();
				$sku = $product->get_sku();
				$price = $product->get_price();

				$line_item = array(
					'name' => $item_name,
					'product_id' => $product_id,
					'quantity' => $quantity,
					'subtotal' => $subtotal,
					'sku' => $sku,
					'price' => $price
				);

				$order_with_items['line_items'][] = $line_item;
			}

			$orders_with_items[] = $order_with_items;
		}

		$params = array(
			'usecaseID' => get_option( 'NextBestOffer_OLS_use_case' ),
			'apiKey' => get_option( 'NextBestOffer_OLS_api_key' ),
		);

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