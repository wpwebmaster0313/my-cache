<?php
/**
 * Plugin Name: My Cache
 * Plugin URI: 
 * Description: A simple caching plugin that just works.
 * Author: wpwebmaster0313
 * Version: 1.0.0
 * Text Domain: my-cache
 * Domain Path: /languages
 * Author URI: 
 *
 * @package  my-cache
 */
defined( 'ABSPATH' ) || exit;
    
define( 'MC_VERSION', '1.0.0' );
define( 'MC_PATH', dirname( __FILE__ ) );

require_once MC_PATH . '/inc/pre-wp-functions.php';
require_once MC_PATH . '/inc/functions.php';
require_once MC_PATH . '/inc/class-mc-settings.php';
require_once MC_PATH . '/inc/class-mc-config.php';
require_once MC_PATH . '/inc/class-mc-advanced-cache.php';
require_once MC_PATH . '/inc/class-mc-object-cache.php';

MC_Settings::factory();
MC_Advanced_Cache::factory();


/**
 * Add settings link to plugin actions
 * 
 * @param array $plugin_actions Each action is HTML.
 * @param string $plugin_file Path to plugin file.
 * @since 1.0
 * @return array
 */
function mc_filter_plugin_action_links( $plugin_actions, $plugin_file ) {

    $new_actions = array();

    if ( basename( dirname( __FILE__ ) ) . '/my-cache.php' === $plugin_file ) {
        /* translators: Param 1 is link to settings page. */
        $new_actions['mc_settings'] = '<a href="' . esc_url( admin_url( 'options-general.php?page="my-cache' ) ) . '">' . esc_html__( 'Settings', 'my-cache' ) . '</a>';
    }

    return array_merge( $new_actions, $plugin_actions );
}
add_filter( 'plugin_action_links', 'mc_filter_plugin_action_links', 10, 2 );
