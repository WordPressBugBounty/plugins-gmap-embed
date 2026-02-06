<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<?php if ($wpgmap_center_lat_lng !== '') : ?>
    <?php if ('1' === $wpgmap_show_heading) : ?>
        <h1 class='srm_gmap_heading_<?php echo esc_attr($count); ?> <?php echo esc_attr($wpgmap_heading_class); ?>'>
            <?php echo esc_html(wp_strip_all_tags($wpgmap_title)); ?>
        </h1>
    <?php endif; ?>

    <?php 
    $gmap_embed_listing_style = "--wgm-map-height: " . esc_attr($wpgmap_map_height) . "; ";
    $gmap_embed_listing_style .= "--wgm-drawer-width: " . esc_attr($wpgmap_drawer_width) . "px; ";
    if (!empty($wpgmap_marker_listing_width)) {
        $gmap_embed_listing_style .= "--wgm-listing-width: " . esc_attr($wpgmap_marker_listing_width) . "px; ";
    }
    ?>
    <div class="wgm-map-listing-container wgm-placement-<?php echo esc_attr($placement); ?> wgm-map-id-<?php echo esc_attr($wgm_map_id); ?>" 
         style="<?php echo esc_attr($gmap_embed_listing_style); ?>">
        
        <div class="wgm-map-area" style="width:<?php echo esc_attr($wpgmap_map_width); ?>; margin: 0 auto; max-width: 100%;">
            <?php $this->render('public/search-control', $data, true); ?>
            <?php $this->render('public/directions-drawer', $data, true); ?>
            
            <div id="srm_gmp_embed_<?php echo esc_attr($count); ?>" class="wgm-map-canvas"
                style="width: 100%; height:<?php echo esc_attr($wpgmap_map_height); ?> !important;">
            </div>

            <?php if ($wpgmap_enable_direction === '1' && _wgm_is_premium()) : ?>
                <?php $this->render('public/legacy-directions', $data, true); ?>
            <?php endif; ?>
        </div>

        <div id="wgm_listing_area_<?php echo esc_attr($count); ?>" class="wgm-listing-area">
            <?php if (_wgm_is_premium() && $marker_listing_type !== 'none') : ?>
                <?php $this->render('public/marker-listing/' . $marker_listing_type, $data, true); ?>
            <?php endif; ?>
        </div>
    </div>

    <div id="wgm-lightbox-overlay_<?php echo esc_attr($count); ?>" class="wgm-lightbox-overlay">
        <span class="wgm-lightbox-close">&times;</span>
        <img class="wgm-lightbox-content" id="wgm-lightbox-img_<?php echo esc_attr($count); ?>">
    </div>
<?php else : ?>
    <?php if (is_user_logged_in() && current_user_can('administrator')) : ?>
        <span style='color:darkred;'>Shortcode not defined, please check WP Google Map plugin in WordPress admin panel(sidebar). This message only visible to Administrator</span>
    <?php endif; ?>
<?php endif; ?>
