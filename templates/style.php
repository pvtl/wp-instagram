<?php
/**
 * Instagram media item responsive css.
 *
 * @var float    $gutter
 * @var int      $xl
 * @var int      $lg
 * @var int      $md
 * @var int      $sm
 * @var int      $xs
 *
 * @package WPInstagram
 */

defined( 'ABSPATH' ) || exit;

?>
<style>
    .instagram-media-item {
        width: <?php echo number_format( 100 / $xs, 5 ); ?>%;
    }

    @media (min-width: 576px) {
        .instagram-media-item {
            width: <?php echo number_format( 100 / $sm, 5 ); ?>%;
        }
    }

    @media (min-width: 768px) {
        .instagram-media-item {
            width: <?php echo number_format( 100 / $md, 5 ); ?>%;
        }
    }

    @media (min-width: 992px) {
        .instagram-media-item {
            width: <?php echo number_format( 100 / $lg, 5 ); ?>%;
        }
    }

    @media (min-width: 1200px) {
        .instagram-media-item {
            width: <?php echo number_format( 100 / $xl, 5 ); ?>%;
        }
    }

    .instagram-media-container {
        margin-left: -<?php echo (float) $gutter; ?>rem;
        margin-right: -<?php echo (float) $gutter; ?>rem;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
    }

    .instagram-media-item {
        padding-left: <?php echo (float) $gutter; ?>rem;
        padding-right: <?php echo (float) $gutter; ?>rem;
        margin-bottom: <?php echo (float) $gutter; ?>rem;
    }

    .instagram-media-image {
        display: block;
        width: 100%;
        height: auto;
    }
</style>
