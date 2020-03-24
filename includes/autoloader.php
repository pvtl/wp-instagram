<?php
/**
 * Autoload classes.
 *
 * @package WPInstagram
 */

spl_autoload_register(
    function ( $class_name ) {
        $path_parts    = explode( '\\', $class_name );
        $class_name    = end( $path_parts );
        $expected_path = GRAM_ABSPATH . '/includes/class-' . strtolower( $class_name ) . '.php';

        if ( file_exists( $expected_path ) ) {
            include $expected_path;
        }
    }
);
