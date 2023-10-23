<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wordpress.org/plugins/nextbestoffer-ols/
 * @since             1.0.0
 * @package           NextBestOffer-OLS
 *
 * @wordpress-plugin
 * Plugin Name:       NextBestOffer-OLS
 * Plugin URI:        https://wordpress.org/plugins/nextbestoffer-ols/
 * Description:       This WordPress WooCommerce extension utilizes Artificial Intelligence (AI) to generate precise, personalized product suggestions based on purchase history. 
 * Version:           1.1.0
 * Author:            Open Logic Systems
 * Author URI:        https://open-ls.de/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nextbestoffer-ols
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'NextBestOffer-OLS_VERSION', '1.1.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-NextBestOffer-OLS-activator.php
 */
function activate_NextBestOffer_OLS() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-NextBestOffer-OLS-activator.php';
	NextBestOffer_OLS_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-NextBestOffer-OLS-deactivator.php
 */
function deactivate_NextBestOffer_OLS() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-NextBestOffer-OLS-deactivator.php';
	NextBestOffer_OLS_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_NextBestOffer_OLS' );
register_deactivation_hook( __FILE__, 'deactivate_NextBestOffer_OLS' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-NextBestOffer-OLS.php';

/**
 * Central config file
 */
require_once plugin_dir_path( __FILE__ )  . 'config.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_NextBestOffer_OLS() {

	$plugin = new NextBestOffer_OLS();
	$plugin->run();

}
run_NextBestOffer_OLS();