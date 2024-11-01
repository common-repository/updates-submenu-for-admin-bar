<?php

class ASU_Admin {

    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @since   1.0.0
     *
     * @var     string
     */
    const VERSION = '1.0.0';

    /**
     *
     * Unique identifier for the plugin.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    public $plugin_slug = 'updates-submenu-adminbar';

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Plugin basename
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected static $plugin_basename = null;

    /**
     * Stored options in database
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $option_slug = 'asu_opts';

    /**
     * Slug of the plugin screen.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $plugin_screen_hook_suffix = null;

    /**
     * Initialize the plugin by setting appropriate hooks and loading scripts
     * and styles.
     *
     * @since     1.0.0
     */
    private function __construct() {

        // Activate plugin when new blog is added
        add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

        // Add the options page and menu item.
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

        // Register plugin settings
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Add an action link pointing to the options page.
        self::$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
        add_filter( 'plugin_action_links_' . self::$plugin_basename, array( $this, 'add_action_links' ) );

        // Add the submenu to Updates on Admin Bar
        if ( is_admin_bar_showing() ) {
            add_action( 'admin_bar_menu', array( $this, 'admin_bar_submenu' ) );
        }

    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;

    }

    /**
     * Fired when the plugin is activated.
     *
     * @since    1.0.0
     *
     * @param    boolean    $network_wide    True if WPMU superadmin uses
     *                                       "Network Activate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       activated on an individual blog.
     */
    public static function activate( $network_wide ) {

        if ( function_exists('is_multisite') && is_multisite() ) {

            if ( $network_wide  ) {

                // Get all blog ids
                $blog_ids = self::get_blog_ids();

                foreach ( $blog_ids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    self::single_activate();
                }

                restore_current_blog();

            } else {
                self::single_activate();
            }

        } else {
            self::single_activate();
        }

    }

    /**
     * Fired when the plugin is deactivated.
     *
     * @since    1.0.0
     *
     * @param    boolean    $network_wide    True if WPMU superadmin uses
     *                                       "Network Deactivate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       deactivated on an individual blog.
     */
    public static function deactivate( $network_wide ) {

        if ( function_exists('is_multisite') && is_multisite() ) {

            if ( $network_wide ) {

                // Get all blog ids
                $blog_ids = self::get_blog_ids();

                foreach ( $blog_ids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    self::single_deactivate();
                }

                restore_current_blog();

            } else {
                self::single_deactivate();
            }

        } else {
            self::single_deactivate();
        }

    }

    /**
     * Fired when a new site is activated with a WPMU environment.
     *
     * @since    1.0.0
     *
     * @param    int    $blog_id    ID of the new blog.
     */
    public function activate_new_site( $blog_id ) {

        if ( 1 !== did_action('wpmu_new_blog') ) {
            return;
        }

        switch_to_blog( $blog_id );
        self::single_activate();
        restore_current_blog();

    }

    /**
     * Get all blog ids of blogs in the current network that are:
     * - not archived
     * - not spam
     * - not deleted
     *
     * @since    1.0.0
     *
     * @return   array|false    The blog ids, false if no matches.
     */
    private static function get_blog_ids() {

        global $wpdb;

        // get an array of blog ids
        $sql = "SELECT blog_id FROM $wpdb->blogs
            WHERE archived = '0' AND spam = '0'
            AND deleted = '0'";

        return $wpdb->get_col( $sql );

    }

    /**
     * Fired for each blog when the plugin is activated.
     *
     * @since    1.0.0
     */
    private static function single_activate() {
        //TODO: Define activation functionality here
    }

    /**
     * Fired for each blog when the plugin is deactivated.
     *
     * @since    1.0.0
     */
    private static function single_deactivate() {
        //TODO: Define deactivation functionality here
    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        $this->plugin_screen_hook_suffix = add_options_page(
            'Updates Submenu for Admin Bar',
            'Updates Submenu for Admin Bar',
            'manage_options',
            $this->plugin_slug,
            array( $this, 'display_plugin_settings_page' )
        );
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_settings_page() {
        include_once( plugin_dir_path( __DIR__ ) . 'views/settings.php' );
    }

    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     */
    public function add_action_links( $links ) {
        return array_merge(
            array(
                'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">Settings</a>'
            ),
            $links
        );
    }

    /**
     * Register all needed settings
     *
     * @since    1.0.0
     */
    public function register_settings() {

        register_setting(
            $this->plugin_slug . '-settings',
            ASU_Admin::$option_slug
        );

        add_settings_section(
            $this->plugin_slug . '-settings-general',
            'General Settings',
            array( $this, 'section_general_cb' ),
            $this->plugin_slug
        );

    }

    /**
     * General section output
     *
     * @since    1.0.0
     */
    public function section_general_cb() {
        $opts = ASU_Admin::get_option();
        ?>
            <p>Specify where to link for each sections in submenu</p>
            <table class="form-table">
                <tr>
                    <th scope="row">Core Updates Link</th>
                    <td>
                        <fieldset><legend class="screen-reader-text"><span>Core Updates Link</span></legend>
                        <label title="Updates page">
                            <input type="radio" name="<?php echo ASU_Admin::$option_slug ?>[core]" value="page" <?php checked( 'page', $opts['core'] ); ?>>
                            <span>Dashboard > Updates page</span>
                        </label><br>
                        <label title="Zip file">
                            <input type="radio" name="<?php echo ASU_Admin::$option_slug ?>[core]" value="file" <?php checked( 'file', $opts['core'] ); ?>>
                            <span>Direct link to the ZIP file</span>
                        </label><br>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Plugin Updates Link</th>
                    <td>
                        <fieldset><legend class="screen-reader-text"><span>Plugin Updates Link</span></legend>
                        <label title="Plugin updates page">
                            <input type="radio" name="<?php echo ASU_Admin::$option_slug ?>[plugin]" value="page" <?php checked( 'page', $opts['plugin'] ); ?>>
                            <span>Plugin Updates page</span>
                        </label><br>
                        <label title="Zip file">
                            <input type="radio" name="<?php echo ASU_Admin::$option_slug ?>[plugin]" value="file" <?php checked( 'file', $opts['plugin'] ); ?>>
                            <span>Direct link to the ZIP file</span>
                        </label><br>
                        <label title="Update Link">
                            <input type="radio" name="<?php echo ASU_Admin::$option_slug ?>[plugin]" value="direct" <?php checked( 'direct', $opts['plugin'] ); ?>>
                            <span>Link to direct update</span>
                        </label><br>
                    </td>
                </tr>
                    <th scope="row">Theme Updates Link</th>
                    <td>
                        <fieldset><legend class="screen-reader-text"><span>Theme Updates Link</span></legend>
                        <label title="Theme details page">
                            <input type="radio" name="<?php echo ASU_Admin::$option_slug ?>[theme]" value="page" <?php checked( 'page', $opts['theme'] ); ?>>
                            <span>Theme details page</span>
                        </label><br>
                        <label title="Zip file">
                            <input type="radio" name="<?php echo ASU_Admin::$option_slug ?>[theme]" value="file" <?php checked( 'file', $opts['theme'] ); ?>>
                            <span>Direct link to the ZIP file</span>
                        </label><br>
                        <label title="Update Link">
                            <input type="radio" name="<?php echo ASU_Admin::$option_slug ?>[theme]" value="direct" <?php checked( 'direct', $opts['theme'] ); ?>>
                            <span>Link to direct update</span>
                        </label><br>
                    </td>
                </tr>
            </table>
        <?php
    }

    public static function get_option( $type = false ) {
        $options =  get_option( ASU_Admin::$option_slug );
        $default =  array(
                        'core'   => 'page',
                        'plugin' => 'page',
                        'theme'  => 'page'
                    );

        // Provide a sane default
        $options = wp_parse_args( $options, $default );

        if ( $type && isset( $options[ $type ] ) ) {
            return $options[ $type ];
        }

        return $options;
    }

    /**
     * Modify the admin bar submenu
     *
     * @since  1.0.0
     *
     * @param  object $wp_admin_bar WP_Admin_Bar
     *
     * @return void
     */
    public function admin_bar_submenu( $wp_admin_bar ) {

        $cores   = ASU_Updates::get_core_updates();
        $plugins = ASU_Updates::get_plugin_updates();
        $themes  = ASU_Updates::get_theme_updates();

        if ( ! empty( $cores ) ) {
            $wp_admin_bar->add_group( array( 'id' => 'update_core', 'parent' => 'updates' ) );
            foreach ( $cores as $core ) {
                $args = array(
                    'id'     => $core['slug'],
                    'title'  => $core['title'],
                    'href'   => $core['url'],
                    'parent' => 'update_core'
                );

                $wp_admin_bar->add_node( $args );
            }
        }

        if ( ! empty( $plugins ) ) {
            $wp_admin_bar->add_group( array( 'id' => 'update_plugins', 'parent' => 'updates' ) );
            foreach ( $plugins as $plugin ) {
                $args = array(
                    'id'     => $plugin['slug'],
                    'title'  => $plugin['title'],
                    'href'   => $plugin['url'],
                    'parent' => 'update_plugins'
                );

                $wp_admin_bar->add_node( $args );
            }
        }

        if ( ! empty( $themes ) ) {
            $wp_admin_bar->add_group( array( 'id' => 'update_themes', 'parent' => 'updates' ) );
            foreach ( $themes as $theme ) {
                $args = array(
                    'id'     => $theme['slug'],
                    'title'  => $theme['title'],
                    'href'   => $theme['url'],
                    'parent' => 'update_themes'
                );

                $wp_admin_bar->add_node( $args );
            }
        }

    }

}