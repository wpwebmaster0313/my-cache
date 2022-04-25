<?php
/**
 * Utility functions for plugin
 *
 * @package  simple-cache
 */

/**
 * Clear the cache
 *
 * @param  bool $network_wide Flush all site caches
 * @since  1.4
 */
function mc_cache_flush( $network_wide = false ) {
	$paths = array();

	$url_parts = wp_parse_url( home_url() );

    $path = mc_get_cache_dir() . '/' . untrailingslashit( $url_parts['host'] ) . '/';

    if ( ! empty( $url_parts['path'] ) && '/' !== $url_parts['path'] ) {
        $path .= trim( $url_parts['path'], '/' );
    }

    $paths[] = $path;

	foreach ( $paths as $rm_path ) {
		mc_rrmdir( $rm_path );
	}

	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}
}

/**
 * Verify we can write to the file system
 *
 * @since  1.7
 * @return array|boolean
 */
function mc_verify_file_access() {
	if ( function_exists( 'clearstatcache' ) ) {
		@clearstatcache();
	}

	$errors = array();

	if ( ! apply_filters( 'mc_disable_auto_edits', false ) ) {
		// First check wp-config.php.
		if ( ! @is_writable( ABSPATH . 'wp-config.php' ) && ! @is_writable( ABSPATH . '../wp-config.php' ) ) {
			$errors[] = 'wp-config';
		}

		// Now check wp-content
		if ( ! @is_writable( untrailingslashit( WP_CONTENT_DIR ) ) ) {
			$errors[] = 'wp-content';
		}

		// Make sure config directory or parent is writeable
		if ( file_exists( mc_get_config_dir() ) ) {
			if ( ! @is_writable( mc_get_config_dir() ) ) {
				$errors[] = 'config';
			}
		} else {
			if ( file_exists( dirname( mc_get_config_dir() ) ) ) {
				if ( ! @is_writable( dirname( mc_get_config_dir() ) ) ) {
					$errors[] = 'config';
				}
			} else {
				$errors[] = 'config';
			}
		}
	}

	// Make sure cache directory or parent is writeable
	if ( file_exists( mc_get_cache_dir() ) ) {
		if ( ! @is_writable( mc_get_cache_dir() ) ) {
			$errors[] = 'cache';
		}
	} else {
		if ( file_exists( dirname( mc_get_cache_dir() ) ) ) {
			if ( ! @is_writable( dirname( mc_get_cache_dir() ) ) ) {
				$errors[] = 'cache';
			}
		} else {
			if ( file_exists( dirname( dirname( mc_get_cache_dir() ) ) ) ) {
				if ( ! @is_writable( dirname( dirname( mc_get_cache_dir() ) ) ) ) {
					$errors[] = 'cache';
				}
			} else {
				$errors[] = 'cache';
			}
		}
	}

	if ( ! empty( $errors ) ) {
		return $errors;
	}

	return true;
}

/**
 * Remove directory and all it's contents
 *
 * @param  string $dir Directory
 * @since  1.7
 */
function mc_rrmdir( $dir ) {
	if ( is_dir( $dir ) ) {
		$objects = mcandir( $dir );

		foreach ( $objects as $object ) {
			if ( '.' !== $object && '..' !== $object ) {
				if ( is_dir( $dir . '/' . $object ) ) {
					mc_rrmdir( $dir . '/' . $object );
				} else {
					unlink( $dir . '/' . $object );
				}
			}
		}

		rmdir( $dir );
	}
}
