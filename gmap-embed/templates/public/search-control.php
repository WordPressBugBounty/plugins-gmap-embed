<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<!-- Advanced Search Control -->
<?php 
$gmap_embed_show_search = ($wgm_enable_store_locator == '1' || $wgm_enable_title_search == '1' || $wgm_enable_category_filter == '1');
if ($gmap_embed_show_search) :
?>
<div id="wpgmap_search_control_<?php echo esc_attr($count); ?>" class="wgm-search-control-container wgm-search-placement-<?php echo esc_attr($wgm_store_locator_placement); ?>" style="display:none;">
    <div class="wpgmap_search_map_control_wrapper">
    <div class="wpgmap_search_box">
        <?php if ($wgm_enable_store_locator == '1') : ?>
            <input type="text" id="wpgmap_search_loc_<?php echo esc_attr($count); ?>" 
                placeholder="<?php echo esc_attr($wgm_address_search_placeholder); ?>" 
                value="<?php echo esc_attr($wgm_default_address); ?>"
                class="wpgmap_search_input" />
        <?php endif; ?>
            
        <?php if ($marker_listing_type !== 'basic_list' && $wgm_enable_title_search == '1') : ?>
            <input type="text" id="wpgmap_search_key_<?php echo esc_attr($count); ?>" 
                placeholder="<?php echo esc_attr($wgm_title_search_placeholder); ?>" 
                class="wpgmap_search_input" />
        <?php endif; ?>

        <?php if ($wgm_enable_category_filter == '1' && !empty($categories)) : ?>
            <div class="wgm-filter-wrapper-front">
                <button type="button" id="wpgmap_filter_toggle_<?php echo esc_attr($count); ?>" class="wpgmap_search_btn wpgmap_filter_btn" title="<?php esc_html_e('Filter Categories', 'gmap-embed'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M400-240v-80h160v80H400ZM240-440v-80h480v80H240ZM120-640v-80h720v80H120Z"/></svg>
                </button>
                <div id="wpgmap_filter_panel_<?php echo esc_attr($count); ?>" class="wgm-filter-panel-front hidden">
                    <!-- Categories injected via JS -->
                </div>
            </div>
            <!-- Hidden input to store selected categories for search logic -->
            <input type="hidden" id="wpgmap_search_cat_<?php echo esc_attr($count); ?>" value="" />
        <?php endif; ?>
        
        <?php if ($wgm_enable_store_locator == '1') : ?>
            <select id="wpgmap_search_radius_<?php echo esc_attr($count); ?>" class="wpgmap_search_select">
                <option value=""><?php esc_html_e('Radius', 'gmap-embed'); ?></option>
                <?php
                $gmap_embed_available_radii = [1, 5, 10, 25, 50, 75, 100, 150, 200, 300, 500];
                foreach ($gmap_embed_available_radii as $gmap_embed_rad) {
                    $gmap_embed_selected_radius = ($wgm_default_radius == $gmap_embed_rad) ? 'selected' : '';
                    echo '<option value="' . esc_attr($gmap_embed_rad) . '" ' . esc_attr($gmap_embed_selected_radius) . '>' . esc_html($gmap_embed_rad) . '</option>';
                }
                ?>
            </select>
        <?php endif; ?>
        
        <button id="wpgmap_search_btn_<?php echo esc_attr($count); ?>" type="button" class="wpgmap_search_btn">
            <svg viewBox="0 0 24 24" width="18" height="18">
                <path fill="currentColor" d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"></path>
            </svg>
        </button>
        
        <button id="wpgmap_search_clear_<?php echo esc_attr($count); ?>" type="button" class="wpgmap_search_clear" style="display:none;" title="<?php esc_attr_e('Clear search', 'gmap-embed'); ?>">
            <svg viewBox="0 0 24 24" width="18" height="18">
                <path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"></path>
            </svg>
        </button>
        
        <!-- Spinner for geocoding -->
        <span id="wpgmap_search_spinner_<?php echo esc_attr($count); ?>" class="wpgmap_search_spinner" style="display:none;"></span>
    </div>
    </div>
        <!-- Not Found Message -->
    <div id="wpgmap_not_found_msg_<?php echo esc_attr($count); ?>" class="wpgmap_not_found_message" style="display:none;">
        <span class="wgm-not-found-text"><?php echo esc_html($wgm_not_found_message); ?></span>
        <span class="wgm-not-found-close" title="<?php esc_attr_e('Close', 'gmap-embed'); ?>" style="cursor:pointer; margin-left:8px;">
            <svg viewBox="0 0 24 24" width="16" height="16" style="vertical-align: middle;">
                <path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"></path>
            </svg>
        </span>
    </div>
</div>
<?php endif; ?>
