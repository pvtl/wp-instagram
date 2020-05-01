<?php
/**
 * Instagram
 *
 * @package           WPInstagram
 * @author            Pivotal Agency
 * @copyright         2019 Pivotal Agency
 *
 * @wordpress-plugin
 * Plugin Name:       WP Instagram
 * Plugin URI:        https://www.pivotalagency.com.au
 * Description:       Sync Instagram media with WordPress.
 * Version:           1.0.4
 * Requires at least: 4.8
 * Requires PHP:      5.6
 * Author:            Pivotal Agency
 * Author URI:        https://www.pivotalagency.com.au
 * Text Domain:       wp-instagram
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'GRAM_ABSPATH' ) ) {
    define( 'GRAM_ABSPATH', dirname( __FILE__ ) );
}

// Include the main Instagram class.
if ( ! class_exists( 'WPInstagram', false ) ) {
    include_once GRAM_ABSPATH . '/includes/class-wpinstagram.php';
}

/**
 * Returns the main instance of PI.
 *
 * @return WPInstagram
 */
function wp_instagram() {
    return WPInstagram::instance();
}

// Global for backwards compatibility.
$GLOBALS['wp_instagram'] = wp_instagram();
