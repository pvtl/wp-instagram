<?php
/**
 * Handle Instagram callbacks.
 *
 * @package WPInstagram
 */

$bedrock_path  = dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/wp/wp-blog-header.php';
$standard_path = dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/wp-blog-header.php';

if ( file_exists( $bedrock_path ) ) {
    require_once $bedrock_path;
} elseif ( file_exists( $standard_path ) ) {
    require_once $standard_path;
} else {
    die();
}

include '../includes/autoloader.php';

if ( isset( $_GET['code'] ) && $code = $_GET['code'] ) {
   \Pvtl\Instagram\Admin::request_access_token( $code );
}
