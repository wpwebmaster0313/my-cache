<?php
/**
 * Settings class
 * 
 * @package my-cache
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class containing settings hooks
 */
class MC_Settings {

    /**
     * Setup the plugin
     * 
     * @since 1.0
     */
    public function setup() {
        
        add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts_styles' ) );

        add_action( 'load-settings_page_my-cache', array( $this, 'update' ) );

        add_action( 'admin_menu', array( $this, 'action_admin_menu' ) );

    }

    /**
     * Enqueue settings screen js/css 
     * 
     * @since 1.0
     */
    public function action_admin_enqueue_scripts_styles() {

        global $pagenow;

        if ( ( 'options-general.php' === $pagenow || 'settings.php' === $pagenow ) && ! empty( $_GET['page'] ) && 'my-cache' === $_GET['page'] ) {
            wp_enqueue_script( 'mc-settings', plugins_url( 'assets/js/settings.js', dirname( __FILE__ ) ), array( 'jquery' ), MC_VERSION, true );
        }
    }

    /**
     * Add options page
     * 
     * @since 1.0
     */
    public function action_admin_menu() {
        add_submenu_page( 'options-general.php', esc_html__( 'My Cache', 'my-cache' ), esc_html__( 'My Cache', 'my-cache' ), 'manage_options', 'my-cache', array( $this, 'screen_options' ) );
    }

    /**
     * Handle setting changes
     * 
     * @since 1.0
     */
    public function update() {
        
        if ( ! empty( $_REQUEST['action'] ) && 'mc_update' === $_REQUEST['action'] ) {

            if ( ! current_user_can( 'manage_options' ) || empty( $_REQUEST['mc_settings_nonce'] ) || ! wp_verify_nonce( $_REQUEST['mc_settings_nonce'], 'mc_update_settings' ) ) {
                wp_die( esc_html__( 'You need a higher level of permission.', 'my-cache' ) );
            }

            $defaults       = MC_Config::factory()->defaults;
            $current_config = MC_Config::factory()->get();

            foreach ( $defaults as $key => $default ) {
                $clean_config[ $key ] = $current_config[ $key ];

                if ( isset( $_REQUEST['mc_my_cache'][ $key ] ) ) {
                    // $clean_config[ $key ] = call_user_func( $default['sanitizer'], $_REQUEST['mc_my_cache'][ $key ] );
                    if ( 1 == $_REQUEST['mc_my_cache'][$key] ) {
                        $clean_config[ $key ] = true;
                    } else {
                        $clean_config[ $key ] = false;
                    }
                    
                }
            }

            // Back up configuration in options
            update_option( 'mc_my_cache', $clean_config );

            MC_Config::factory()->write( $clean_config );
        }
    }

    /**
     * Output settings
     * 
     * @since 1.0
     */
    public function screen_options() {

        $config = MC_Config::factory()->get();
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'My Cache Settings', 'my-cache' ); ?></h1>

            <form action="" method="post">
                <?php wp_nonce_field( 'mc_update_settings', 'mc_settings_nonce' ); ?>
                <input type="hidden" name="action" value="mc_update">
                <input type="hidden" name="wp_http_referer" value="<?php echo esc_attr( wp_unslash( $_SERVER['REQUEST_URI'] ) ); ?>'"/>

                <table class="form-table mc-my-mode-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="sc_enable_page_caching_my"><span class="settings-highlight">*</span><?php esc_html_e( 'Enable Caching', 'my-cache' ); ?></label></th>
                            <td>
                                <select name="mc_my_cache[enable_page_caching]" id="mc_enable_page_caching_my">
                                    <option value="0"><?php esc_html_e('No', 'my-cache' ); ?></option>
                                    <option <?php selected( $config['enable_page_caching'], true ); ?>
                                    value="1"><?php esc_html_e( 'Yes', 'my-cache' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e( 'Save Changes', 'my-cache' ); ?>">
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Return an instance of the current class, create one if it doesn't exist
     * 
     * @since 1.0
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
