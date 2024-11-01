<?php
class ASU_Updates {

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    private function __construct() {
        if ( ! function_exists('get_core_updates') || ! function_exists('get_plugin_updates') || ! function_exists('get_theme_updates') ) {
            require_once ABSPATH . '/wp-admin/includes/admin.php';
        }
    }

    /**
     * Return the core updates
     *
     * @since   1.0.0
     *
     * @return array Updates
     */

    public static function get_core_updates() {

        $cores   = get_core_updates();
        $updates = array();

        if ( ! isset( $cores[0]->response ) || 'latest' == $cores[0]->response ) {
            return $updates;
        }


        foreach ( $cores as $core ) {
            if ( $core->dismissed ) { continue; }

            switch ( ASU_Admin::get_option('core') ) {

                case 'file':
                    $url = $core->download;
                    break;

                default: //reserve default for `page`
                    $url = admin_url('update-core.php');
                    break;
            }

            $updates[] = array(
                            'url'   => $url,
                            'title' => 'WordPresss ' . $core->current,
                            'slug'  => 'wordpress'
                        );
        }

        return $updates;
    }

    public static function get_plugin_updates() {
        $plugins = get_plugin_updates();
        $updates = array();

        if ( empty( $plugins ) ) {
            return $updates;
        }

        foreach ( $plugins as $plugin_file => $plugin_data ) {

            switch ( ASU_Admin::get_option('plugin') ) {

                case 'file':
                    $url = $plugin_data->update->package;
                    break;

                case 'direct':
                    $url = wp_nonce_url( admin_url( 'update.php?action=upgrade-plugin&plugin=') . $plugin_file, 'upgrade-plugin_' . $plugin_file );
                    break;

                default: //reserve default for `page`
                    $url = admin_url( 'plugins.php?plugin_status=upgrade#' . $plugin_data->update->slug );
                    break;
            }

            $updates[] = array(
                            'url'   => $url,
                            'title' => esc_attr( $plugin_data->Name . ' ' . $plugin_data->update->new_version ),
                            'slug'  => $plugin_data->update->slug
                        );
        }

        return $updates;
    }

    public static function get_theme_updates() {
        $themes  = get_theme_updates();
        $updates = array();

        if ( empty( $themes ) ) {
            return $updates;
        }

        foreach ( $themes as $stylesheet => $theme ) {

            switch ( ASU_Admin::get_option('theme') ) {

                case 'file':
                    $url = $theme->update['package'];
                    break;

                case 'direct':
                    $url = wp_nonce_url( admin_url('update.php?action=upgrade-theme&theme=') . $stylesheet, 'upgrade-theme_' . $stylesheet );
                    break;

                default: //reserve default for `page`
                    $url = admin_url( 'themes.php?theme=' . $stylesheet );
                    break;
            }

            $updates[] = array(
                            'url'   => $url,
                            'title' => esc_attr( $theme->display('Name') . ' ' . $theme->update['new_version'] ),
                            'slug'  => $theme->update['theme']
                        );
        }

        return $updates;
    }

}