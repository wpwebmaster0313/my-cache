<?php
/**
 * Handle plugin config
 * 
 * @package my-cache
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class wrapping config functionality
 */
class MC_Config {

    /**
     * Setup object
     * 
     * @since 1.0
     * @var array
     */
    public $defaults = array();

    /**
     * Set config defauls
     * 
     * @since 1.0
     */
    public function __construct() {

        $this->defaults = array(
            'enable_page_caching' => array(
                'default'   => false,
                'sanitizer' => array( $this, 'boolval' ),
            ),
        );
    }

    /**
     * Return defaults
     * 
     * @since 1.0
     * @return array
     */
    public function get_defaults() {

		$defaults = array();

		foreach ( $this->defaults as $key => $default ) {
			$defaults[ $key ] = $default['default'];
		}

		return $defaults;
	}

    /**
     * Write config to file
     * 
     * @since 1.0
     * @param array $config Configuration array.
     * @return bool
     */
    public function write( $config ) {
        $config = wp_parse_args( $config, $this->get_defaults() );
        return true;
    }

    /**
     * Get config from file or cache
     * 
     * @since 1.0
     * @return array
     */
    public function get() {
        $config = get_option( 'mc_my_cache', $this->get_defaults() );

        return wp_parse_args( $config, $this->get_defaults() );
    }

    /**
     * Return an instance of the current class, create one if it doesn't exist
     * 
     * @since 1.0
     * @return MC_Config
     */
    public static function factory() {

        static $instance;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }
}
