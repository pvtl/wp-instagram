<?php
/**
 * Template functions.
 *
 * @package WPInstagram
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get shortcode templates.
 *
 * @param string $template_name Template name.
 * @param array  $args          Template arguments.
 * @param string $template_path Template path.
 *
 * @return string
 */
function wp_instagram_get_template( $template_name, $args = array(), $template_path = '' ) {
    $cache_key = sanitize_key( implode( '-', array( 'template', $template_name, $template_path ) ) );
    $template  = (string) wp_cache_get( $cache_key, 'wp-instagram' );

    if ( ! $template ) {
        $template_path = wp_instagram_locate_template( $template_name, $template_path );

        // Get the template output.
        $template = ( function ( $args, $template_path ) {
            ob_start();
            extract( $args, EXTR_SKIP );
            unset( $args );

            include $template_path;

            return ob_get_clean();
        } )( $args, $template_path );

        wp_cache_set( $cache_key, $template, 'wp-instagram' );
    }

    return $template;
}

/**
 * Locate a template and return the path for inclusion.
 *
 * @param string $template_name Template name.
 * @param string $template_path Template path.
 *
 * @return string
 */
function wp_instagram_locate_template( $template_name, $template_path = '' ) {
    if ( ! $template_path ) {
        $template_path = wp_instagram()->template_path();
    }

    $template = locate_template(
        array(
            trailingslashit( $template_path ) . $template_name,
            $template_name,
        )
    );

    if ( ! $template ) {
        $default_path = wp_instagram()->plugin_path() . '/templates/';

        $template = $default_path . $template_name;
    }

    return $template;
}
