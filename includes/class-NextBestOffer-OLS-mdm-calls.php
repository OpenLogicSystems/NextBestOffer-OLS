<?php
/**
 * The file that defines the external recommendations API class
 *
 * @link       https://wordpress.org/plugins/nextbestoffer-ols/
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
		$original_time_limit = ini_get('max_execution_time');
		set_time_limit(300); //increase php time limit (default 30 sec)

		$api_url = MDM_SERVICE_URL . '/addData';
	
		$usecaseID = get_option('NextBestOffer_OLS_use_case');
		$apiKey = get_option('NextBestOffer_OLS_api_key');
		$customer_related_recommendations = get_option('NextBestOffer_OLS_training_mode') === 'customer_related';
	
		// anzahl der Aufträge ermitteln
		$order_query = new WC_Order_Query(
			array(
				'limit' => -1,
				'type' => 'shop_order',
				'status' => array('wc-completed'),
				'return' => 'ids',
			)
		);
		$order_ids = $order_query->get_orders();

		// Prüfen, ob Bestellungen vorhanden sind
		if (empty($order_ids)) {
			return 'no_orders';
		}
	
		// Batch größe
		$batch_size = get_option('NextBestOffer_OLS_batch_size');

		// Validierung
		$batch_size = (int) $batch_size;
		if ($batch_size < 500) {
			$batch_size = 500;
		} elseif ($batch_size > 4000) {
			$batch_size = 4000;
		}

		// Anzahl Batches
		$batches = array_chunk($order_ids, $batch_size);
	
		// iterate through order arrays with batch size
		foreach ($batches as $index => $batch) {
			$orders_with_items = array();
		
			// iterate through current order array (batch)
			foreach ($batch as $order_id) {
				$order = wc_get_order($order_id); //retrieve order with id
	
				// order preprocessing
				$order_with_items = self::processOrder($order);
	
				if (!empty($order_with_items)) {
					$orders_with_items[] = $order_with_items;
				}
	
			}
	
			if (!empty($orders_with_items)) {
				$params = array(
					'usecaseID' => $usecaseID,
					'apiKey' => $apiKey,
					'customer_related_recommendations' => $customer_related_recommendations,
					'isFirstBatch' => $index === 0 ? true : false
				);
			
				$url = add_query_arg($params, $api_url);

				$args = array(
					'body' => gzencode(json_encode($orders_with_items, JSON_UNESCAPED_UNICODE), 9),
					'headers' => array(
						'Content-Encoding' => 'gzip',
						'Content-Type' => 'application/json'
					),
					'timeout' => 90,
				);
		
				$response = wp_remote_post($url, $args);
		
				if (is_wp_error($response)) {
					return false;
				}
		
				if ($response['response']['code'] == 500) {
					$response_body = json_decode(wp_remote_retrieve_body($response), true);
		
					if (isset($response_body['detail']) && $response_body['detail'] == 'Training is currently running') {
						return 'training_running';
					}
		
					return false;
				}
			}
			wp_cache_flush();
		}

		//start training with new data
		$training_response = self::startTraining();
		set_time_limit($original_time_limit); //reset php time limit
		return $training_response;
	}
	
	private static function processOrder($order) {
		//skip order if one method is not available
		if (!method_exists($order, 'get_id')
			|| !method_exists($order, 'get_customer_id')
			|| !method_exists($order, 'get_items')) {
			return;
		}
	
		$order_id = $order->get_id();
		$customer_id = $order->get_customer_id();
		$items = $order->get_items();
	
		//check if a variable is empty string, NULL, integer 0, or an array with no elements
		if (empty($order_id) || empty($customer_id)|| empty($items)) {
			error_log("one empty order property");
			return;
		}
	
		$order_with_items = array(
			'id' => $order_id,
			'customer_id' => $customer_id,
			'line_items' => array()
		);
	
		foreach ($items as $item) {
			// line item preprocessing
			$line_item = self::processItem($item);
	
			if (!empty($line_item)) {
				$order_with_items['line_items'][] = $line_item;
			}
		}
	
		return $order_with_items;
	}
	
	private static function processItem($item) {
		//skip item if method is not available
		if (!method_exists($item, 'get_product_id')) {
			return;
		}
		$product_id = $item->get_product_id();

		//check if a variable is empty string, NULL, integer 0, or an array with no elements
		if (empty($product_id)) {
			return;
		}
	
		$line_item = array(
			'product_id' => $product_id
		);
	
		return $line_item;
	}	

	private static function startTraining() {
		$api_url = MDM_SERVICE_URL . '/trainModel';

		$domain = parse_url(site_url(), PHP_URL_HOST);

		$hashed_domain = hash('sha256', $domain);
		
		$params = array(
			'usecaseID' => get_option('NextBestOffer_OLS_use_case'),
			'apiKey' => get_option('NextBestOffer_OLS_api_key'),
			'domain' => $hashed_domain
		);
		
		$url = add_query_arg($params, $api_url);
		
		$response = wp_remote_post($url);
		
		if (is_wp_error($response)) {
			return false;
		}
		
		if ($response['response']['code'] == 500) {
			$response_body = json_decode(wp_remote_retrieve_body($response), true);
			
			if (isset($response_body['detail']) && $response_body['detail'] == 'Training is already running') {
				return 'training_running';
			}
			
			if (isset($response_body['detail']) && $response_body['detail'] == 'No data available for training') {
				return 'no_orders';
			}
			
			return false;
		}
		
		return true;
	}	

	public static function get_recommendations( $case_id, $api_key, $item_ids ) {
		
		$temp_str = implode("_", $item_ids);
		if (get_transient('NextBestOffer_OLS_temporary_recommendations' . $temp_str) !== false) { //there is already a prediction
			return get_transient(('NextBestOffer_OLS_temporary_recommendations' . $temp_str));
		} else {
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
				set_transient('NextBestOffer_OLS_temporary_recommendations' . $temp_str, $limited_consequents, 600); // Expires in 10 minutes
				return $limited_consequents;
			}

			return false;
			}
			
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

	public static function report_bug($email, $reportText) {
		$api_url = MDM_SERVICE_URL . '/reportBug';
	
		$report_data = array(
			'email' => $email,
			'reportText' => $reportText,
		);

		$args = array(
			'body' => json_encode($report_data),
			'headers' => array(
				'Content-Type' => 'application/json',
			),
		);
	
		$response = wp_remote_post( $api_url, $args);
	
		if ( !is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) == 200 ) {
			return true;
		}
		return false;
	}
}