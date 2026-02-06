<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<!-- Directions Drawer -->
<div id="wgm_direction_drawer_<?php echo esc_attr($count); ?>" class="wgm-direction-drawer">
    <?php if ($wpgmap_enable_modern_direction == 1): ?>
    <button type="button" id="wgm_drawer_toggle_<?php echo esc_attr($count); ?>" class="wgm-drawer-toggle-btn" title="<?php esc_html_e('Open Directions', 'gmap-embed'); ?>">
        <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"></path></svg>
    </button>
    <?php endif; ?>
    
    <div class="wgm-drawer-content">
    <button type="button" class="wgm-drawer-close" aria-label="Close directions">
        <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"></path></svg>
    </button>
    <div class="wgm-drawer-header">
        <h3 class="wgm-drawer-title"><?php esc_html_e('Get Directions', 'gmap-embed'); ?></h3>
    </div>
    
    <div class="wgm-travel-mode-switcher">
        <button type="button" class="wgm-travel-btn active" data-mode="DRIVING" title="Driving">
            <svg viewBox="0 0 24 24"><path fill="currentColor" d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"></path></svg>
        </button>
        <button type="button" class="wgm-travel-btn" data-mode="WALKING" title="Walking">
            <svg viewBox="0 0 24 24"><path fill="currentColor" d="M13.5 5.5c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zM9.8 8.9L7 23h2.1l1.8-8 2.1 2v6h2v-7.5l-2.1-2 .6-3C14.8 12 16.8 13 19 13v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1L6 8.3V13h2V9.6l1.8-.7"></path></svg>
        </button>
        <button type="button" class="wgm-travel-btn" data-mode="BICYCLING" title="Cycling">
            <svg viewBox="0 0 24 24"><path fill="currentColor" d="M15.5 5.5c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zM5 12c-2.8 0-5 2.2-5 5s2.2 5 5 5 5-2.2 5-5-2.2-5-5-5zm0 8.5c-1.9 0-3.5-1.6-3.5-3.5s1.6-3.5 3.5-3.5 3.5 1.6 3.5 3.5-1.6 3.5-3.5 3.5zm19-3.5c0-2.8-2.2-5-5-5s-5 2.2-5 5 2.2 5 5 5 5-2.2 5-5zm-5 3.5c-1.9 0-3.5-1.6-3.5-3.5s1.6-3.5 3.5-3.5 3.5 1.6 3.5 3.5-1.6 3.5-3.5 3.5zm-6.1-5.4l2.7-2.7-1.2-1.2-.2-.2C13.5 8.3 12.3 8 11 8V6h-2v2H7L3.5 11l1.4 1.4L7 10.3V15h2v-4.7l2.1 2.1c.3.3.7.6 1.1.6h2.8l-1.6-1.6h-2.1z"></path></svg>
        </button>
        <button type="button" class="wgm-travel-btn" data-mode="TRANSIT" title="Transit">
            <svg viewBox="0 0 24 24"><path fill="currentColor" d="M12 2c-4.42 0-8 .5-8 4v10c0 .74.19 1.42.5 2.02V20c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h8v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1.98c.31-.6.5-1.28.5-2.02V6c0-3.5-3.58-4-8-4zM7.5 17c-.83 0-1.5-.67-1.5-1.5S6.67 14 7.5 14s1.5.67 1.5 1.5S8.33 17 7.5 17zm3.5-6H6V6h5v5zm5.5 6c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm2.5-6h-5V6h5v5z"></path></svg>
        </button>
    </div>

    <div class="wgm-dir-input-group">
        <div class="wgm-dir-field-wrap">
            <input type="text" id="wgm_dir_from_<?php echo esc_attr($count); ?>" class="wgm-dir-input" placeholder="<?php esc_html_e('Choose start point...', 'gmap-embed'); ?>">
            <button type="button" class="wgm-current-loc-btn" title="<?php esc_html_e('Use my location', 'gmap-embed'); ?>">
                <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3c-.46-4.17-3.77-7.48-7.94-7.94V1h-2v2.06C6.83 3.52 3.52 6.83 3.06 11H1v2h2.06c.46 4.17 3.77 7.48 7.94 7.94V23h2v-2.06c4.17-.46 7.48-3.77 7.94-7.94H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"></path></svg>
            </button>
        </div>
    </div>
    
    <div id="wgm_waypoints_container_<?php echo esc_attr($count); ?>" class="wgm-waypoints-container"></div>

    <div class="wgm-dir-input-group">
        <div class="wgm-dir-field-wrap">
            <input type="text" id="wgm_dir_to_<?php echo esc_attr($count); ?>" class="wgm-dir-input" placeholder="<?php esc_html_e('Choose destination...', 'gmap-embed'); ?>">
            <button type="button" class="wgm-current-loc-btn" title="<?php esc_html_e('Use my location', 'gmap-embed'); ?>">
                <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3c-.46-4.17-3.77-7.48-7.94-7.94V1h-2v2.06C6.83 3.52 3.52 6.83 3.06 11H1v2h2.06c.46 4.17 3.77 7.48 7.94 7.94V23h2v-2.06c4.17-.46 7.48-3.77 7.94-7.94H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"></path></svg>
            </button>
        </div>
    </div>

    <div class="wgm-add-waypoint-trigger" id="wgm_add_waypoint_<?php echo esc_attr($count); ?>">
            <svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"></path></svg>
            <span><?php esc_html_e('Add Waypoint', 'gmap-embed'); ?></span>
    </div>

    <div class="wgm_dir_options_wrap">
        <div class="wgm-action-bar">
            <div class="wgm-dir-adv-toggle">
                <svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M3 17v2h6v-2H3zM3 5v2h10V5H3zm10 16v-2h8v-2h-8v-2h-2v6h2zM7 9v2H3v2h4v2h2V9H7zm14 4v-2H11v2h10zm-6-4h2V7h4V5h-4V3h-2v6z"></path></svg>
                <span><?php esc_html_e('Route Options', 'gmap-embed'); ?></span>
            </div>
            <button type="button" class="wgm-dir-go-btn">
                <span><?php esc_html_e('Go', 'gmap-embed'); ?></span>
                <svg viewBox="0 0 24 24" width="20" height="20"><path fill="currentColor" d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"></path></svg>
            </button>
        </div>
        
        <div class="wgm-dir-adv-options">
            <label class="wgm-adv-opt-item">
                <input type="checkbox" class="wgm-avoid-tolls"> <?php esc_html_e('Avoid Tolls', 'gmap-embed'); ?>
            </label>
            <label class="wgm-adv-opt-item">
                <input type="checkbox" class="wgm-avoid-highways"> <?php esc_html_e('Avoid Highways', 'gmap-embed'); ?>
            </label>
            <label class="wgm-adv-opt-item">
                <input type="checkbox" class="wgm-avoid-ferries"> <?php esc_html_e('Avoid Ferries', 'gmap-embed'); ?>
            </label>
        </div>
    </div>

    <div id="wgm_dir_results_<?php echo esc_attr($count); ?>" class="wgm-dir-results-panel"></div>
    </div> <!-- End .wgm-drawer-content -->
</div>
