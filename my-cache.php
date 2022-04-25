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

MC_Settings::factory();
MC_Advanced_Cache::factory();
