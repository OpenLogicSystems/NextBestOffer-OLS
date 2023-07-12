<?php

/**
 * Fired during plugin activation
 *
 * @link       https://wordpress.org/plugins/NextBestOffer-OLS
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
		update_option( 'NextBestOffer_OLS_max_rule_length', 5 );
    	update_option( 'NextBestOffer_OLS_min_support', 0.5 );
    	update_option( 'NextBestOffer_OLS_min_confidence', 0.8 );
	}
}