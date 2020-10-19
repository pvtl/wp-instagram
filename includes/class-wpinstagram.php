<?php
/**
 * Instagram setup
 *
 * @package WPInstagram
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main Instagram class.
 *
 * @class WPInstagram
 */
final class WPInstagram {
    /**
     * WPInstagram version.
     *
     * @var string
     */
    public $version = '1.0.5';

    /**
     * The single instance of the class.
     *
     * @var WPInstagram
     */
    protected static $instance;

    /**
     * Query instance.
     *
     * @var WP_Query
     */
    public $query = null;

    /**
     * Main WPInstagram Instance.
     *
     * Ensures only one instance of WPInstagram is loaded or can be loaded.
     *
     * @return WPInstagram
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * WooCommerce Constructor.
     */
    public function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Trigger the `wp_instagram_loaded` hook.
     */
    public function on_loaded() {
        do_action( 'wp_instagram_loaded' );
    }

    /**
     * Hook into actions and filters.
     */
    private function init_hooks() {
        add_filter( 'cron_schedules', array( $this, 'add_schedules' ) );
        add_action( 'plugins_loaded', array( $this, 'on_loaded' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        add_action( 'after_setup_theme', array( $this, 'include_template_functions' ) );
        add_action( 'init', array( $this, 'init' ), 0 );
        add_action( 'init', array( $this, 'add_image_sizes' ) );
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'init', array( 'Pvtl\Instagram\Admin', 'init' ) );
        add_action( 'init', array( 'Pvtl\Instagram\Shortcode', 'init' ) );
        add_action( 'admin_footer', array( $this, 'admin_js' ) );
        add_action( 'wp_ajax_instagram_dismissed_notice', array( $this, 'dismiss_notice' ) );
        add_action( 'sync_media', array( $this, 'sync_media' ) );
        add_action( 'refresh_token', array( 'Pvtl\Instagram\Admin', 'refresh_access_token' ) );

        // Schedule the sync media task.
        if ( ! wp_next_scheduled( 'sync_media' ) ) {
            wp_clear_scheduled_hook( 'sync_media' );
            wp_schedule_event( time(), 'hourly', 'sync_media' );
        }

        // Schedule the refresh token task.
        if ( ! wp_next_scheduled( 'refresh_token' ) ) {
            wp_clear_scheduled_hook( 'refresh_token' );
            wp_schedule_event( time(), 'monthly', 'refresh_token' );
        }
    }

    /**
     * Add custom CRON schedules.
     *
     * @param array $schedules Current schedules.
     *
     * @return array
     */
    public function add_schedules( $schedules ): array {
        $schedules['monthly'] = array(
            'interval' => MONTH_IN_SECONDS,
            'display'  => 'Once per month',
        );

        return $schedules;
    }

    /**
     * Include any required files.
     */
    public function includes() {
        include_once GRAM_ABSPATH . '/includes/autoloader.php';
    }

    /**
     * Function used to init Instagram template functions.
     */
    public function include_template_functions() {
        include_once GRAM_ABSPATH . '/includes/template-functions.php';
    }

    /**
     * Init Instagram when WordPress initialises.
     */
    public function init() {
        do_action( 'wp_instagram_init' );
    }

    /**
     * Ensure post thumbnail support is turned on.
     */
    private function add_thumbnail_support() {
        if ( ! current_theme_supports( 'post-thumbnails' ) ) {
            add_theme_support( 'post-thumbnails' );
        }

        add_post_type_support( 'instagram', 'thumbnail' );
    }

    /**
     * Add image sizes to WordPress.
     */
    public function add_image_sizes() {
        $large     = apply_filters( 'wp_instagram_large', array( 1024, 1024, false ) );
        $thumbnail = apply_filters( 'wp_instagram_thumbnail', array( 256, 256, true ) );

        add_image_size( 'wp_instagram_large', $large[0], $large[1], $large[2] );
        add_image_size( 'wp_instagram_thumbnail', $thumbnail[0], $thumbnail[1], $thumbnail[2] );
    }

    /**
     * Register the instagram post type.
     */
    public function register_post_type() {
        register_post_type(
            'instagram',
            array(
                'labels'              => array(
                    'name'          => __( 'Instagram', 'wp-instagram' ),
                    'singular_name' => __( 'Instagram', 'wp-instagram' ),
                ),
                'public'              => true,
                'exclude_from_search' => true,
                'publicly_queryable'  => false,
                'show_in_nav_menus'   => false,
                'has_archive'         => false,
                'supports'            => array(
                    'title',
                    'editor',
                    'thumbnail',
                ),
            )
        );
    }

    /**
     * Get the plugin url.
     *
     * @return string
     */
    public function plugin_url() {
        return untrailingslashit( plugins_url( '/', GRAM_ABSPATH . '/wp-instagram.php' ) );
    }

    /**
     * Get the plugin path.
     *
     * @return string
     */
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( GRAM_ABSPATH . '/wp-instagram.php' ) );
    }

    /**
     * Get the template path.
     *
     * @return string
     */
    public function template_path() {
        return apply_filters( 'wp_instagram_template_path', 'instagram/' );
    }

    /**
     * Output a admin notice when build dependencies not met.
     *
     * @return void
     */
    public function admin_notices() {
        $dismissed = (int) get_option( 'instagram_notice_dismissed', 0 );

        if ( $dismissed > time() - MONTH_IN_SECONDS ) {
            return;
        }

        $client_id     = get_option( 'instagram_client_id' );
        $client_secret = get_option( 'instagram_client_secret' );
        $access_token  = get_option( 'instagram_access_token' );
        $token_expires = get_option( 'instagram_expires_at' );

        if ( ! $client_id || ! $client_secret || ! $access_token || time() >= $token_expires ) {
            $message            = 'Instagram is not connected.';
            $message_link       = get_admin_url( null, 'edit.php?post_type=instagram&page=login' );
            $message_link_label = 'Connect your account';

            // phpcs:disable WordPress.Security.EscapeOutput
            printf(
                '<div data-dismiss-notice="instagram" class="notice notice-warning is-dismissible"><p>%s <a href="%s">%s</a></p></div>',
                $message,
                $message_link,
                $message_link_label
            );
        }
    }

    /**
     * Sync Instagram media.
     */
    public function sync_media() {
        $user_id      = (int) get_option( 'instagram_user_id' );
        $access_token = get_option( 'instagram_access_token' );

        $params = array(
            'access_token' => $access_token,
            'fields'       => 'id,media_type,media_url,permalink,caption,timestamp',
        );

        $response = wp_remote_get( "https://graph.instagram.com/{$user_id}/media?" . http_build_query( $params ) );
        $json     = json_decode( wp_remote_retrieve_body( $response ) );

        if ( isset( $json->code ) && $json->code >= 300 ) {
            return;
        }

        $successful_post_ids = array();

        if ( ! empty( $json->data ) ) {
            foreach ( $json->data as $media ) {
                $args = array(
                    'post_type'     => 'instagram',
                    'post_status'   => 'publish',
                    'post_title'    => $media->id,
                    'post_date'     => $media->timestamp,
                    'post_modified' => $media->timestamp,
                    'post_content'  => isset( $media->caption ) ? $media->caption : '',
                );

                $existing = get_page_by_title( $media->id, OBJECT, 'instagram' );

                if ( $existing ) {
                    $args['ID'] = $existing->ID;

                    $post_id = wp_update_post( $args );
                } else {
                    $post_id = wp_insert_post( $args );
                }

                if ( is_wp_error( $post_id ) ) {
                    continue;
                }

                update_post_meta( $post_id, 'ig_media_url', $media->media_url );
                update_post_meta( $post_id, 'ig_permalink', $media->permalink );

                if ( has_post_thumbnail( $post_id ) ) {
                    $successful_post_ids[] = $post_id;
                    continue;
                }

                $wp_upload_dir = wp_upload_dir( null, true );
                $file_name     = 'instagram-' . $media->id . '.jpg';

                // Get the image contents.
                $image_response = wp_remote_get( $media->media_url );
                $media_contents = wp_remote_retrieve_body( $image_response );

                // Upload image to uploads dir.
                $put = wp_upload_bits( $file_name, null, $media_contents, null );

                // Prepare an array of post data for the attachment.
                $attachment = array(
                    'guid'           => $wp_upload_dir['url'] . '/' . basename( $put['file'] ),
                    'post_mime_type' => $put['type'],
                    'post_title'     => sanitize_file_name( basename( $put['file'] ) ),
                    'post_content'   => '',
                    'post_status'    => 'inherit',
                );

                // Insert the attachment.
                $attach_id = wp_insert_attachment( $attachment, $put['file'], $post_id );

                if ( is_wp_error( $attach_id ) ) {
                    continue;
                }

                require_once ABSPATH . 'wp-admin/includes/image.php';

                $attach_data = wp_generate_attachment_metadata( $attach_id, $put['file'] );

                wp_update_attachment_metadata( $attach_id, $attach_data );

                set_post_thumbnail( $post_id, $attach_id );

                $successful_post_ids[] = $post_id;
            }
        }

        $this->clean_old_posts( $successful_post_ids );
    }

    /**
     * Clean old posts that are either deleted or unavailable.
     * 
     * @param array $successful_post_ids  An array of valid IDs that should be kept.
     * 
     * @return void
     */
    public function clean_old_posts( $successful_post_ids ) {
        $query = new WP_Query(
            array(
                'post_type'      => 'instagram',
                'post__not_in'   => $successful_post_ids,
            )
        );

        while ( $query->have_posts() ) {
            $query->the_post();
            wp_delete_post( get_the_ID() );
        }
    }

    /**
     * Get Instagram media.
     *
     * @param int $items The amount of media to retrieve.
     *
     * @return WP_Query
     */
    public function get_media( $items ) {
        return new WP_Query(
            array(
                'post_type'      => 'instagram',
                'post_status'    => 'publish',
                'posts_per_page' => $items,
                'orderby'        => 'post_date',
                'order'          => 'DESC',
            )
        );
    }

    /**
     * Output admin JavaScript.
     */
    public function admin_js() {
        ?>
        <script>
            (function ($) {
                'use strict';

                $(document).on('click', '[data-dismiss-notice="instagram"]', function () {
                    $.ajax(ajaxurl, {
                        type: 'POST',
                        data: {
                            action: 'instagram_dismissed_notice',
                            type: 'instagram-disconnected',
                        }
                    });
                });
            })(jQuery);
        </script>
        <?php
    }

    /**
     * Dismiss an admin notice.
     */
    public function dismiss_notice() {
        update_option( 'instagram_notice_dismissed', time() );
    }
}
