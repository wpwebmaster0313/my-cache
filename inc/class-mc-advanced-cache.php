<?php
/**
 * Page caching functionality
 * 
 * @package my-cache
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wrapper for advanced cache functionality
 */
class MC_Advanced_Cache {

    /**
	 * Setup hooks/filters
	 *
	 * @since 1.0
	 */
	public function setup() {
		add_action( 'pre_post_update', array( $this, 'purge_post_on_update' ), 10, 1 );
		add_action( 'save_post', array( $this, 'purge_post_on_update' ), 10, 1 );
		add_action( 'wp_trash_post', array( $this, 'purge_post_on_update' ), 10, 1 );
		add_action( 'wp_set_comment_status', array( $this, 'purge_post_on_comment_status_change' ), 10 );
		add_action( 'set_comment_cookies', array( $this, 'set_comment_cookie_exceptions' ), 10 );
	}

    /**
	 * When user posts a comment, set a cookie so we don't show them page cache
	 *
	 * @param  WP_Comment $comment Comment to check.
	 * @since  1.3
	 */
	public function set_comment_cookie_exceptions( $comment ) {
		$config = MC_Config::factory()->get();

		// File based caching only.
		if ( ! empty( $config['enable_page_caching'] ) && empty( $config['enable_in_memory_object_caching'] ) ) {
			$post_id = $comment->comment_post_ID;

			setcookie( 'mc_commented_posts[' . $post_id . ']', wp_parse_url( get_permalink( $post_id ), PHP_URL_PATH ), ( time() + HOUR_IN_SECONDS * 24 * 30 ) );
		}
	}

    /**
	 * Every time a comments status changes, purge it's parent posts cache
	 *
	 * @param  int $comment_id Comment ID.
	 * @since  1.3
	 */
	public function purge_post_on_comment_status_change( $comment_id ) {
		$config = MC_Config::factory()->get();

		// File based caching only.
		if ( ! empty( $config['enable_page_caching'] ) && empty( $config['enable_in_memory_object_caching'] ) ) {
			$comment = get_comment( $comment_id );
			$post_id = $comment->comment_post_ID;

			$path = mc_get_cache_path() . '/' . preg_replace( '#https?://#i', '', get_permalink( $post_id ) );

			@unlink( untrailingslashit( $path ) . '/index.html' );
			@unlink( untrailingslashit( $path ) . '/index.gzip.html' );
		}
	}

    /**
	 * Purge post cache when there is a new approved comment
	 *
	 * @param  int   $comment_id Comment ID.
	 * @param  int   $approved Comment approved status.
	 * @param  array $commentdata Comment data array.
	 * @since  1.3
	 */
	public function purge_post_on_comment( $comment_id, $approved, $commentdata ) {
		if ( empty( $approved ) ) {
			return;
		}

		$config = MC_Config::factory()->get();

		// File based caching only.
		if ( ! empty( $config['enable_page_caching'] ) && empty( $config['enable_in_memory_object_caching'] ) ) {
			$post_id = $commentdata['comment_post_ID'];

			$path = mc_get_cache_path() . '/' . preg_replace( '#https?://#i', '', get_permalink( $post_id ) );

			@unlink( untrailingslashit( $path ) . '/index.html' );
			@unlink( untrailingslashit( $path ) . '/index.gzip.html' );
		}
	}

    /**
	 * Automatically purge all file based page cache on post changes
	 *
	 * @param  int $post_id Post id.
	 * @since  1.3
	 */
	public function purge_post_on_update( $post_id ) {
		$post = get_post( $post_id );

		// Do not purge the cache if it's an autosave or it is updating a revision.
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || 'revision' === $post->post_type ) {
			return;

			// Do not purge the cache if the user cannot edit the post.
		} elseif ( ! current_user_can( 'edit_post', $post_id ) && ( ! defined( 'DOING_CRON' ) || ! DOING_CRON ) ) {
			return;

			// Do not purge the cache if the user is editing an unpublished post.
		} elseif ( 'draft' === $post->post_status ) {
			return;
		}

		$config = MC_Config::factory()->get();

		// File based caching only.
		if ( ! empty( $config['enable_page_caching'] ) && empty( $config['enable_in_memory_object_caching'] ) ) {
			mc_cache_flush();
		}
	}

	/**
	 * Delete file for clean up
	 * 
	 * @since 1.0
	 * @return bool
	 */
	public function clean_up() {

		$file = untrailingslashit( WP_CONTENT_DIR ) . '/advanced-cache.php';

		$ret = true;

		if ( ! @unlink( $file ) ) {
			$ret = false;
		}

		$folder = untrailingslashit( WP_CONTENT_DIR ) . '/cache/simple-cache';

		if ( ! @unlink( $folder, true ) ) {
			$ret = false;
		}

		return $ret;
	}


    /**
     * Return an instance of the current class, create one if it doesn't exist
     * 
     * @since 1.0
     * @return object
     */
    public static function factory() {

        static $instance;

        if ( ! $instance ) {
            $instance = new self();
            $instance->setup();
        }

        return $instance;
    }
}