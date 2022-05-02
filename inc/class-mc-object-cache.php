<?php
/**
 * Object cache functionality
 * 
 * @package my-cache
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wrap object caching functionality
 */
class MC_Object_Cache {
    /**
     * Delete file for clean up
     * 
     * @since 1.0
     * @return bool
     */
    public function clean_up() {

        $file = untrailingslashit( WP_CONTENT_DIR ) . '/object-cache.php';

        if ( ! @unlink( $file ) ) {
            return false;
        }

        return true;
    }
}