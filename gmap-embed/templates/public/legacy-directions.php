<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="wgm-legacy-direction-box">
    <div class="wgm-legacy-direction-header">
        <h3 class="wgm-legacy-direction-title"><?php esc_html_e('Get Directions', 'gmap-embed'); ?></h3>
    </div>

    <!-- Travel Mode Switcher -->
    <div class="wgm-legacy-travel-switcher">
        <button type="button" class="wgm-legacy-travel-btn active" data-mode="DRIVING" title="<?php esc_html_e('Driving', 'gmap-embed'); ?>">
            <svg viewBox="0 0 24 24"><path fill="currentColor" d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"></path></svg>
        </button>
        <button type="button" class="wgm-legacy-travel-btn" data-mode="WALKING" title="<?php esc_html_e('Walking', 'gmap-embed'); ?>">
            <svg viewBox="0 0 24 24"><path fill="currentColor" d="M13.5 5.5c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zM9.8 8.9L7 23h2.1l1.8-8 2.1 2v6h2v-7.5l-2.1-2 .6-3C14.8 12 16.8 13 19 13v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1L6 8.3V13h2V9.6l1.8-.7"></path></svg>
        </button>
        <button type="button" class="wgm-legacy-travel-btn" data-mode="BICYCLING" title="<?php esc_html_e('Bicycling', 'gmap-embed'); ?>">
            <svg viewBox="0 0 24 24"><path fill="currentColor" d="M15.5 5.5c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zM5 12c-2.8 0-5 2.2-5 5s2.2 5 5 5 5-2.2 5-5-2.2-5-5-5zm0 8.5c-1.9 0-3.5-1.6-3.5-3.5s1.6-3.5 3.5-3.5 3.5 1.6 3.5 3.5-1.6 3.5-3.5 3.5zm19-3.5c0-2.8-2.2-5-5-5s-5 2.2-5 5 2.2 5 5 5 5-2.2 5-5zm-5 3.5c-1.9 0-3.5-1.6-3.5-3.5s1.6-3.5 3.5-3.5 3.5 1.6 3.5 3.5-1.6 3.5-3.5 3.5zm-6.1-5.4l2.7-2.7-1.2-1.2-.2-.2C13.5 8.3 12.3 8 11 8V6h-2v2H7L3.5 11l1.4 1.4L7 10.3V15h2v-4.7l2.1 2.1c.3.3.7.6 1.1.6h2.8l-1.6-1.6h-2.1z"></path></svg>
        </button>
        <button type="button" class="wgm-legacy-travel-btn" data-mode="TRANSIT" title="<?php esc_html_e('Transit', 'gmap-embed'); ?>">
            <svg viewBox="0 0 24 24"><path fill="currentColor" d="M12 2c-4.42 0-8 .5-8 4v10c0 .74.19 1.42.5 2.02V20c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h8v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1.98c.31-.6.5-1.28.5-2.02V6c0-3.5-3.58-4-8-4zM7.5 17c-.83 0-1.5-.67-1.5-1.5S6.67 14 7.5 14s1.5.67 1.5 1.5S8.33 17 7.5 17zm3.5-6H6V6h5v5zm5.5 6c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm2.5-6h-5V6h5v5z"></path></svg>
        </button>
    </div>

    <!-- From Input -->
    <div class="wgm-legacy-input-group">
        <label for="srm_gmap_from_<?php echo esc_attr($count); ?>" class="wgm-legacy-label"><?php esc_html_e('From', 'gmap-embed'); ?></label>
        <div class="wgm-legacy-field-wrap">
            <input type="text" id="srm_gmap_from_<?php echo esc_attr($count); ?>" class="wgm-legacy-input" placeholder="<?php esc_html_e('Choose start point...', 'gmap-embed'); ?>" />
            <button type="button" class="wgm-legacy-loc-btn" title="<?php esc_html_e('Use my location', 'gmap-embed'); ?>">
                <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3c-.46-4.17-3.77-7.48-7.94-7.94V1h-2v2.06C6.83 3.52 3.52 6.83 3.06 11H1v2h2.06c.46 4.17 3.77 7.48 7.94 7.94V23h2v-2.06c4.17-.46 7.48-3.77 7.94-7.94H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"></path></svg>
            </button>
        </div>
    </div>

    <!-- To Input -->
    <div class="wgm-legacy-input-group">
        <label for="srm_gmap_to_<?php echo esc_attr($count); ?>" class="wgm-legacy-label"><?php esc_html_e('To', 'gmap-embed'); ?></label>
        <div class="wgm-legacy-field-wrap">
            <input type="text" id="srm_gmap_to_<?php echo esc_attr($count); ?>" class="wgm-legacy-input" placeholder="<?php esc_html_e('Choose destination...', 'gmap-embed'); ?>" />
            <button type="button" class="wgm-legacy-loc-btn" title="<?php esc_html_e('Use my location', 'gmap-embed'); ?>">
                <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3c-.46-4.17-3.77-7.48-7.94-7.94V1h-2v2.06C6.83 3.52 3.52 6.83 3.06 11H1v2h2.06c.46 4.17 3.77 7.48 7.94 7.94V23h2v-2.06c4.17-.46 7.48-3.77 7.94-7.94H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"></path></svg>
            </button>
        </div>
    </div>

    <!-- Hidden mode select for backward compatibility -->
    <select id="srm_gmap_mode_<?php echo esc_attr($count); ?>" class="wgm-legacy-mode-select" style="display: none;">
        <option value="DRIVING"><?php esc_html_e('Driving', 'gmap-embed'); ?></option>
        <option value="WALKING"><?php esc_html_e('Walking', 'gmap-embed'); ?></option>
        <option value="BICYCLING"><?php esc_html_e('Bicycling', 'gmap-embed'); ?></option>
        <option value="TRANSIT"><?php esc_html_e('Transit', 'gmap-embed'); ?></option>
    </select>

    <!-- Submit Button -->
    <button type="button" class="wgm-legacy-submit-btn" id="wp_gmap_submit_<?php echo esc_attr($count); ?>">
        <span><?php esc_html_e('Get Directions', 'gmap-embed'); ?></span>
        <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"></path></svg>
    </button>

    <span id="wp_gmap_loading_<?php echo esc_attr($count); ?>" class="wgm-legacy-loading" style="display: none;">
        <svg class="wgm-legacy-spinner" viewBox="0 0 50 50"><circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="4"></circle></svg>
        <?php esc_html_e('Loading', 'gmap-embed'); ?>...
    </span>

    <!-- Results Panel -->
    <div id="wp_gmap_results_<?php echo esc_attr($count); ?>" class="wgm-legacy-results" style="display:none;">
        <div id="wp_gmap_directions_<?php echo esc_attr($count); ?>"></div>
    </div>
</div>
