<?php
/**
 * Admin setup
 *
 * @package WPInstagram
 */

namespace Pvtl\Instagram;

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode class.
 *
 * @class Shortcode
 */
class Admin {
    /**
     * Init shortcode.
     */
    public static function init() {
        add_action( 'admin_menu', __CLASS__ . '::add_auth_page' );
    }

    /**
     * Add the Instagram auth pages.
     */
    public static function add_auth_page() {
        add_submenu_page(
            'edit.php?post_type=instagram',
            'Login',
            'Login',
            'edit_posts',
            'login',
            __CLASS__ . '::render_auth_page'
        );
    }

    /**
     * Render the Instagram auth pages.
     */
    public static function render_auth_page() {
        // phpcs:disable WordPress.Security.NonceVerification
        if ( isset( $_GET['code'] ) ) {
            $code = sanitize_text_field( wp_unslash( $_GET['code'] ) );
            self::request_access_token( $code );
        }

        // phpcs:disable WordPress.Security.NonceVerification
        if ( isset( $_GET['connected'] ) ) {
            printf( '<div class="notice notice-success"><p>%s</p></div>', 'Instagram account connected successfully.' );
        }

        // $token_expires_at = get_option( 'instagram_expires_at' );

        // if ( $token_expires_at ) {
            // $expires_at = \DateTime::createFromFormat( 'U', $token_expires_at, new \DateTimeZone( 'UTC' ) );

            // phpcs:disable WordPress.Security.EscapeOutput
            // printf(
            //     '<div class="notice notice-info"><p>%s %s</p></div>',
            //     'Access token expires on the',
            //     $expires_at->setTimezone( wp_timezone() )->format( 'jS F \a\t h:i a.' )
            // );
        // }

        if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
            $nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : null;

            if ( ! wp_verify_nonce( $nonce, 'update_instagram_client_keys' ) ) {
                wp_die( 'Invalid nonce' );
            }

            $client_id = isset( $_POST['instagram_client_id'] )
                ? sanitize_text_field( wp_unslash( $_POST['instagram_client_id'] ) )
                : null;

            $client_secret = isset( $_POST['instagram_client_secret'] )
                ? sanitize_text_field( wp_unslash( $_POST['instagram_client_secret'] ) )
                : null;

            update_option( 'instagram_client_id', $client_id );
            update_option( 'instagram_client_secret', $client_secret );

            if ( isset( $_POST['sync_media'] ) ) {
                wp_instagram()->sync_media();

                printf( '<div class="notice notice-info"><p>%s</p></div>', 'Media has been synced.' );
            }

            printf( '<div class="notice notice-success"><p>%s</p></div>', 'Instagram client keys updated successfully.' );
        }

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Connect Your Account</h1>

            <br class="clear">

            <form method="post" novalidate="novalidate">
                <?php wp_nonce_field( 'update_instagram_client_keys' ); ?>

                <table class="form-table" role="presentation">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label for="oauth_callback">OAuth2 Callback URI</label>
                        </th>
                        <td>
                            <input disabled name="oauth_callback" type="text" id="oauth_callback" value="<?php echo esc_url( plugins_url( 'wp-instagram/admin/callback.php' ) ); ?>" class="regular-text">
                            <p>Enter the above URI in the <strong>Valid OAuth Redirect URIs</strong> field under <strong>Client OAuth Settings</strong> section of your Facebook app.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="instagram_client_id">Client ID</label>
                        </th>
                        <td>
                            <input name="instagram_client_id" type="text" id="instagram_client_id" value="<?php echo esc_html( get_option( 'instagram_client_id' ) ); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="instagram_client_secret">Client Secret</label>
                        </th>
                        <td>
                            <input name="instagram_client_secret" type="text" id="instagram_client_secret" value="<?php echo esc_html( get_option( 'instagram_client_secret' ) ); ?>" class="regular-text">
                        </td>
                    </tr>
                    <?php if ( get_option( 'instagram_access_token' ) ) { ?>
                        <tr>
                            <th scope="row">
                                <label for="sync_media">Sync Media Now</label>
                            </th>
                            <td>
                                <input name="sync_media" type="checkbox" id="sync_media" value="1">
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                    <a href="<?php echo esc_url( self::build_auth_uri() ); ?>" class="button">Login to Instagram</a>
                </p>
            </form>

            <br class="clear">
        </div>
        <?php
    }

    /**
     * Generate an access token URI.
     */
    private static function build_auth_uri() {
        $client_id = get_option( 'instagram_client_id' );

        $params = array(
            'client_id'     => $client_id,
            'redirect_uri'  => plugins_url( 'wp-instagram/admin/callback.php' ),
            'response_type' => 'code',
            'scope'         => 'user_profile,user_media',
        );

        return 'https://api.instagram.com/oauth/authorize?' . http_build_query( $params );
    }

    /**
     * Generate an access token URI.
     *
     * @param string $code The access code returned by Instagram.
     */
    public static function request_access_token( $code ) {
        $client_id     = get_option( 'instagram_client_id' );
        $client_secret = get_option( 'instagram_client_secret' );

        $params = array(
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'code'          => $code,
            'redirect_uri'  => plugins_url( 'wp-instagram/admin/callback.php' ),
            'grant_type'    => 'authorization_code',
        );

        $response = wp_remote_post( 'https://api.instagram.com/oauth/access_token', array( 'body' => $params ) );
        $json     = json_decode( wp_remote_retrieve_body( $response ) );

        if ( isset( $json->code ) && $json->code >= 300 ) {
            wp_die( esc_html( $json->error_message ) );
        }

        update_option( 'instagram_user_id', $json->user_id );

        $params = array(
            'client_secret' => $client_secret,
            'access_token'  => $json->access_token,
            'grant_type'    => 'ig_exchange_token',
        );

        $response = wp_remote_get( 'https://graph.instagram.com/access_token?' . http_build_query( $params ) );
        $json     = json_decode( wp_remote_retrieve_body( $response ) );

        if ( isset( $json->code ) && $json->code >= 300 ) {
            wp_die( esc_html( $json->error_message ) );
        }

        update_option( 'instagram_access_token', $json->access_token );
        update_option( 'instagram_expires_at', time() + $json->expires_in );

        wp_safe_redirect( get_admin_url( null, 'edit.php?post_type=instagram&page=login&connected', 'relative' ) );

        die();
    }

    /**
     * Refresh the access token.
     */
    public static function refresh_access_token() {
        $access_token = get_option( 'instagram_access_token' );

        if ( ! $access_token ) {
            return;
        }

        $params = array(
            'access_token' => get_option( 'instagram_access_token' ),
            'grant_type'   => 'ig_refresh_token',
        );

        $response = wp_remote_get( 'https://graph.instagram.com/refresh_access_token?' . http_build_query( $params ) );
        $json     = json_decode( wp_remote_retrieve_body( $response ) );

        if ( isset( $json->code ) && $json->code >= 300 ) {
            wp_die( esc_html( $json->error_message ) );
        }

        update_option( 'instagram_access_token', $json->access_token );
        update_option( 'instagram_expires_at', time() + $json->expires_in );
    }
}
