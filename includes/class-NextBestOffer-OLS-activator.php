<?php

/**
 * Fired during plugin activation
 *
 * @link       https://wordpress.org/plugins/nextbestoffer-ols/
 * @since      1.0.0
 *
 * @package    NextBestOffer_OLS
 * @subpackage NextBestOffer_OLS/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    NextBestOffer_OLS
 * @subpackage NextBestOffer_OLS/includes
 * @author     Open Logic Systems <info@open-ls.de>
 */
class NextBestOffer_OLS_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() 	{
		//set default values for settings
		add_option( 'NextBestOffer_OLS_use_case', '' );
		add_option( 'NextBestOffer_OLS_api_key', '' );
		
		add_option( 'NextBestOffer_OLS_max_rule_length', 5 );
    	add_option( 'NextBestOffer_OLS_min_support', 0.5 );
    	add_option( 'NextBestOffer_OLS_min_confidence', 0.8 );
		add_option( 'NextBestOffer_OLS_training_mode', 'transaction_related' );
		add_option( 'NextBestOffer_OLS_batch_size', 500 );

		add_option( 'NextBestOffer_OLS_selected_partial', '' );

		add_option( 'NextBestOffer_OLS_logs', '' );
		
	}
}