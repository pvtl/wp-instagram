<?php
/**
 * Shortcode setup
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
class Shortcode {
    /**
     * Init shortcode.
     */
    public static function init() {
        add_shortcode( 'wp_instagram', __CLASS__ . '::media' );
    }

    /**
     * Display Instagram media.
     *
     * @param array $atts Media options.
     *
     * @return string
     */
    public static function media( $atts = array() ) {
        $atts = shortcode_atts(
            apply_filters(
                'instagram_media_shortcode_defaults',
                array(
                    'total'  => 12,
                    'gutter' => 0.75,
                    'styles' => true,
                    'xl'     => 6,
                    'lg'     => 6,
                    'md'     => 4,
                    'sm'     => 3,
                    'xs'     => 2,
                )
            ),
            $atts
        );

        $media    = wp_instagram()->get_media( $atts['total'] );
        $template = wp_instagram_get_template( 'media.php', array( 'media' => $media ) );

        // Output styles.
        if ( $atts['styles'] ) {
            $template .= wp_instagram_get_template( 'style.php', $atts );
        }

        return $template;
    }
}
