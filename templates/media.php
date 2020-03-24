<?php
/**
 * Display Instagram media items.
 *
 * @var WP_Query $media
 *
 * @package WPInstagram
 */

defined( 'ABSPATH' ) || exit;

if ( $media->have_posts() ) {
    ?>
    <div class="wp-instagram">
        <div class="instagram-media-container">
            <?php

            while ( $media->have_posts() ) {
                $media->the_post();

                $caption       = get_post_meta( get_the_ID(), 'ig_caption', true );
                $media_url     = get_the_post_thumbnail_url( get_the_ID(), 'wp_instagram_large' );
                $thumbnail_url = get_the_post_thumbnail_url( get_the_ID(), 'wp_instagram_thumbnail' );

                ?>
                <div class="instagram-media-item">
                    <a data-fancybox="instagram-media" class="instagram-media-link" href="<?php echo esc_url( $media_url ); ?>" target="_blank" rel="noopener">
                        <img class="instagram-media-image" src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( $caption ); ?>">
                    </a>
                </div>
                <?php
            }

            ?>
        </div>
    </div>
    <?php
}
