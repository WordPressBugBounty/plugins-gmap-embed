<?php if (!defined('ABSPATH')) {
    exit;
}
if (
    !isset($_GET['wgm_map_create_nonce']) ||
    !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['wgm_map_create_nonce'])), 'wgm_create_map')
) {
    wp_die(esc_html__('Invalid request. Nonce verification failed.', 'gmap-embed'));
}
//phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$gmap_embed_admin_map_id = 0;
if (isset($_GET['id'])) {
    $gmap_embed_admin_map_id = intval(sanitize_text_field(wp_unslash($_GET['id'])));
}
$gmap_embed_admin_gmap_data = $this->get_wpgmapembed_data(intval($gmap_embed_admin_map_id));
$gmap_embed_admin_single_map = json_decode($gmap_embed_admin_gmap_data);

// Hardening: Ensure $gmap_embed_admin_single_map is a valid object and properties exist before access
if (!$gmap_embed_admin_single_map || !is_object($gmap_embed_admin_single_map)) {
    wp_die(esc_html__('Failed to load map data. The data may be corrupted.', 'gmap-embed'));
}

// Ensure center lat/lng property exists before explode
$gmap_embed_admin_center_lat_lng = isset($gmap_embed_admin_single_map->wpgmap_center_lat_lng) ? $gmap_embed_admin_single_map->wpgmap_center_lat_lng : '0,0';
list($gmap_embed_admin_center_lat, $gmap_embed_admin_center_lng) = explode(',', esc_html($gmap_embed_admin_center_lat_lng));
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Edit Map', 'gmap-embed'); ?></h1>
    <?php if (_wgm_can_add_new_map()) { ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=wpgmapembed-new')); ?>"
            class="page-title-action"><?php esc_html_e('Add New', 'gmap-embed'); ?></a>
        <?php
    } else {
        // translators: %s: Premium version URL.
        echo '<a href="#" class="page-title-action wgm_enable_premium" style="opacity: .3" data-notice="' . esc_html(sprintf(__('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to <b> Create Unlimited Maps</b>.', 'gmap-embed'), esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=edit-map-add-new-lock'))) . '">' . esc_html__('Add New', 'gmap-embed') . '</a><sup class="wgm-pro-label">' . esc_html__('Pro', 'gmap-embed') . '</sup>';
    }
    ?>
    <?php
    if (!_wgm_is_premium()) {
        echo '<a target="_blank" href="' . esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=edit-map-header-upgrade-btn') . '" class="button wgm_btn" style="float:right;width:auto;padding: 5px 7px;font-size: 11px;margin-left:5px;"><i style="line-height: 25px;" class="dashicons dashicons-star-filled"></i> ' . esc_html__('Upgrade ($19 only)', 'gmap-embed') . '</a>';
    }
    echo '<a target="_blank" href="' . esc_url('https://tawk.to/chat/6083e29962662a09efc1acd5/1f41iqarp') . '" class="button wgm_btn" style="float:right;width:auto;padding: 5px 7px;font-size: 11px;margin-right:5px;background-color: #cb5757 !important;color: white !important;"><i style="line-height: 28px;" class="dashicons dashicons-format-chat"></i> ' . esc_html__('LIVE Chat', 'gmap-embed') . '</a>';
    echo '<a href="' . esc_url(admin_url('admin.php?page=wpgmapembed-support')) . '" class="button wgm_btn" style="float:right;width:auto;padding: 5px 7px;font-size: 11px;margin-right:5px;"><i style="line-height: 25px;" class="dashicons  dashicons-editor-help"></i> ' . esc_html__('Documentation', 'gmap-embed') . '</a>';
    ?>
    <span style="float: right;margin: 0 8px 0 0;">Shortcode <input type="text"
            value="<?php echo esc_attr('[gmap-embed id=&quot;' . intval($gmap_embed_admin_map_id) . '&quot;]'); ?>"
            style="padding: 2px 10px;border: 2px #008dff solid;" onclick="this.select()"></span>
    <hr class="wp-header-end">
    <div id="gmap_container_inner">
        <span class="wpgmap_msg_error" style="width:80%;"></span>
        <div id="wp-gmap-edit" style="padding:5px;">
            <?php require_once WGM_PLUGIN_PATH . 'admin/includes/wgm_messages_viewer.php'; ?>

            <input id="wpgmap_map_id" name="wpgmap_map_id" value="<?php echo esc_attr($gmap_embed_admin_map_id); ?>"
                type="hidden" />
            <div class="wp-gmap-properties-outer">
                <div class="wgm_wpgmap_tab">
                    <ul class="wgm_wpgmap_tab">
                        <li class="active" id="wp-gmap-properties">General</li>
                        <li id="wgm_gmap_markers">Markers</li>
                        <li id="wgm_marker_listing">Marker Listing</li> 
                        <li id="wgm_store_locator"><?php esc_html_e('Store Locator', 'gmap-embed'); ?></li>
                    </ul>
                </div>
                <div class="wp-gmap-tab-contents wp-gmap-properties active">
                    <table class="gmap_properties">
                        <tr>
                            <td>
                                <label
                                    for="wpgmap_title"><b><?php esc_html_e('Map Title', 'gmap-embed'); ?></b></label><br />
                                <input id="wpgmap_title" name="wpgmap_title"
                                    value="<?php echo esc_attr($gmap_embed_admin_single_map->wpgmap_title); ?>" type="text"
                                    class="regular-text">
                                <br />

                                <input type="checkbox" value="1" name="wpgmap_show_heading" id="wpgmap_show_heading"
                                    <?php echo esc_attr(($gmap_embed_admin_single_map->wpgmap_show_heading == 1) ? 'checked' : ''); ?>>
                                <label
                                    for="wpgmap_show_heading"><?php esc_html_e('Show as map title', 'gmap-embed'); ?></label>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label
                                    for="wpgmap_latlng"><b><?php esc_html_e('Latitude, Longitude(Approx)', 'gmap-embed'); ?></b></label><br />
                                <input id="wpgmap_latlng" name="wpgmap_latlng"
                                    value="<?php echo esc_attr($gmap_embed_admin_single_map->wpgmap_latlng); ?>" type="text"
                                    class="regular-text">
                                <input type="hidden" name="wpgmap_center_lat_lng" id="wpgmap_center_lat_lng"
                                    value="<?php echo esc_attr($gmap_embed_admin_single_map->wpgmap_center_lat_lng); ?>">
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label
                                    for="wpgmap_map_zoom"><b><?php esc_html_e('Zoom', 'gmap-embed'); ?></b></label><br />
                                <input id="wpgmap_map_zoom" name="wpgmap_map_zoom"
                                    value="<?php echo esc_attr($gmap_embed_admin_single_map->wpgmap_map_zoom); ?>" type="text"
                                    class="regular-text">


                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label
                                    for="wpgmap_map_width"><b><?php esc_html_e('Width (%)', 'gmap-embed'); ?></b></label><br />
                                <input id="wpgmap_map_width" name="wpgmap_map_width"
                                    value="<?php echo esc_attr($gmap_embed_admin_single_map->wpgmap_map_width); ?>" type="text"
                                    class="regular-text">
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label
                                    for="wpgmap_map_height"><b><?php esc_html_e('Height (px)', 'gmap-embed'); ?></b></label><br />
                                <input id="wpgmap_map_height" name="wpgmap_map_height"
                                    value="<?php echo esc_attr($gmap_embed_admin_single_map->wpgmap_map_height); ?>" type="text"
                                    class="regular-text">
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label><b><?php esc_html_e('Map Type', 'gmap-embed'); ?></b></label><br />
                                <select id="wpgmap_map_type" class="regular-text">
                                    <option <?php echo esc_attr($gmap_embed_admin_single_map->wpgmap_map_type == 'ROADMAP' ? 'selected' : ''); ?>>
                                        ROADMAP
                                    </option>
                                    <option <?php echo esc_attr($gmap_embed_admin_single_map->wpgmap_map_type == 'SATELLITE' ? 'selected' : ''); ?>>
                                        SATELLITE
                                    </option>
                                    <option <?php echo esc_attr($gmap_embed_admin_single_map->wpgmap_map_type == 'HYBRID' ? 'selected' : ''); ?>>
                                        HYBRID
                                    </option>
                                    <option <?php echo esc_attr($gmap_embed_admin_single_map->wpgmap_map_type == 'TERRAIN' ? 'selected' : ''); ?>>
                                        TERRAIN
                                    </option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <label
                                    for="wpgmap_heading_class"><b><?php esc_html_e('Heading Custom Class', 'gmap-embed'); ?></b></label><br />
                                <input id="wpgmap_heading_class" name="wpgmap_heading_class"
                                    value="<?php echo esc_attr($gmap_embed_admin_single_map->wpgmap_heading_class); ?>" type="text"
                                    class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; justify-content: space-between; max-width: 400px; margin: 15px 0;">
                                    <label for="wpgmap_enable_direction" <?php echo !_wgm_is_premium() ? ' class="wgm_enable_premium" " ' : ''; ?>
                                        data-notice="<?php
                                        // translators: %s: Premium version URL.
                                        echo esc_html(sprintf(__('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to <b> Enable Direction Option on Map</b>.', 'gmap-embed'), esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=edit-map-enable-direction-lock'))); ?>">
                                        <b><?php esc_html_e('Enable Direction feature(legacy) below map', 'gmap-embed'); ?></b>
                                        <?php echo !_wgm_is_premium() ? '<sup class="wgm-pro-label">' . esc_html__('Pro', 'gmap-embed') . '</sup>' : ''; ?>
                                    </label>
                                    <div class="wgm_switch">
                                        <input type="checkbox" value="1" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?> name="wpgmap_enable_direction"
                                            id="wpgmap_enable_direction" <?php echo esc_attr(($gmap_embed_admin_single_map->wpgmap_enable_direction == 1) ? 'checked' : ''); ?>>
                                        <span class="wgm_slider round"></span>
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; justify-content: space-between; max-width: 400px; margin: 15px 0;">
                                    <label for="wpgmap_enable_modern_direction" <?php echo !_wgm_is_premium() ? ' class="wgm_enable_premium" ' : ''; ?>
                                        data-notice="<?php
                                        // translators: %s: Premium version URL.
                                        echo esc_html(sprintf(__('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to <b>Enable Modern Direction Option</b>.', 'gmap-embed'), esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=edit-map-enable-modern-direction-lock'))); ?>">
                                        <b><?php esc_html_e('Enable Direction feature(modern) inside map', 'gmap-embed'); ?></b>
                                        <?php echo !_wgm_is_premium() ? '<sup class="wgm-pro-label">' . esc_html__('Pro', 'gmap-embed') . '</sup>' : ''; ?>
                                    </label>
                                    <div class="wgm_switch">
                                        <input type="checkbox" value="1" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?> name="wpgmap_enable_modern_direction"
                                            id="wpgmap_enable_modern_direction" <?php echo esc_attr((isset($gmap_embed_admin_single_map->wpgmap_enable_modern_direction) && $gmap_embed_admin_single_map->wpgmap_enable_modern_direction == 1) ? 'checked' : ''); ?>>
                                        <span class="wgm_slider round"></span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php 
                        $gmap_embed_admin_enable_modern_direction_val = (isset($gmap_embed_admin_single_map->wpgmap_enable_modern_direction) && $gmap_embed_admin_single_map->wpgmap_enable_modern_direction == 1) ? true : false;
                        ?>
                        <tr id="wgm_direction_drawer_width_row" style="<?php echo $gmap_embed_admin_enable_modern_direction_val ? '' : 'display:none;'; ?>">
                            <td>
                                <label for="wpgmap_direction_drawer_width" <?php echo !_wgm_is_premium() ? ' class="wgm_enable_premium" ' : ''; ?>
                                    data-notice="<?php
                                    // translators: %s: Premium version URL.
                                    echo esc_html(sprintf(__('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to <b> Adjust Direction Drawer Width</b>.', 'gmap-embed'), esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=edit-map-direction-drawer-width-lock'))); ?>"><?php esc_html_e('Direction drawer width (px)', 'gmap-embed'); ?>
                                    <b><?php esc_html_e('Direction drawer width (px)', 'gmap-embed'); ?></b>
                                    <?php echo !_wgm_is_premium() ? '<sup class="wgm-pro-label">' . esc_html__('Pro', 'gmap-embed') . '</sup>' : ''; ?>
                                    <br />
                                    <input type="number" id="wpgmap_direction_drawer_width" name="wpgmap_direction_drawer_width"
                                        value="<?php echo esc_attr(!empty($gmap_embed_admin_single_map->wpgmap_direction_drawer_width) ? $gmap_embed_admin_single_map->wpgmap_direction_drawer_width : '300'); ?>" 
                                        class="regular-text" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?>
                                        placeholder="300">
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 9px;">
                                <label
                                    for="wpgmap_map_theme"><b><?php esc_html_e('Map Theme Presets', 'gmap-embed'); ?></b>
                                </label><br />
                                <?php
                                require_once WGM_PLUGIN_PATH . 'admin/includes/map_theme_presets.php';
                                ?>
                                <select id="wpgmap_map_theme" name="wpgmap_map_theme"
                                    style="width:99%;max-width:99%;margin-bottom: 5px;">
                                    <?php
                                    echo '<option value="[]">Default Theme</option>';
                                    foreach ($gmap_embed_map_styles as $gmap_embed_key => $gmap_embed_style) {
                                        echo '<option value="' . esc_attr($gmap_embed_style) . '">' . esc_html($gmap_embed_key) . '</option>';
                                    }
                                    ?>
                                </select>
                                <?php if (!_wgm_is_premium()) { ?>
                                    <a target="_blank"
                                        href="<?php echo esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=edit-map-theme-presets-lock'); ?>">
                                        Unlock unlimited custom themes by Pro version</a>
                                    <?php
                                }
                                ?>
                                <br />
                                <span style="<?php echo (!_wgm_is_premium()) ? 'display: none' : ''; ?>">
                                    <label for="wgm_theme_json"><b><?php esc_html_e('Map Theme JSON', 'gmap-embed'); ?>
                                        </b></label>
                                    <br />
                                    <textarea rows="5" cols="50" class="wgm_theme_json" id="wgm_theme_json"
                                        style="width:99%;max-width:99%;"><?php echo esc_html($gmap_embed_admin_single_map->wgm_theme_json); ?></textarea>
                                    You may create your own map style from
                                    <a target="_blank" href="<?php echo esc_url('https://snazzymaps.com'); ?>">
                                        Snazzy Maps</a> and use JSON here.
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 20px;">
                                <div class="wgm-toggle-header" id="wgm-map-controls-toggle" style="cursor: pointer; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">
                                    <b><?php esc_html_e('Advanced Map Controls', 'gmap-embed'); ?></b>
                                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                                </div>
                                <div id="wgm-map-controls-container" style="display:none; padding-top: 10px;">
                                    <?php
                                    $gmap_embed_admin_map_controls = [
                                        'zoom_control' => [
                                            'label' => __('Zoom Control', 'gmap-embed'),
                                            'meta_enable' => 'wpgmap_zoom_control',
                                            'meta_pos' => 'wpgmap_zoom_control_pos'
                                        ],
                                        'map_type_control' => [
                                            'label' => __('Map Type Control', 'gmap-embed'),
                                            'meta_enable' => 'wpgmap_map_type_control',
                                            'meta_pos' => 'wpgmap_map_type_control_pos'
                                        ],
                                        'street_view_control' => [
                                            'label' => __('Street View Control', 'gmap-embed'),
                                            'meta_enable' => 'wpgmap_street_view_control',
                                            'meta_pos' => 'wpgmap_street_view_control_pos'
                                        ],
                                        'fullscreen_control' => [
                                            'label' => __('Fullscreen Control', 'gmap-embed'),
                                            'meta_enable' => 'wpgmap_fullscreen_control',
                                            'meta_pos' => 'wpgmap_fullscreen_control_pos'
                                        ],
                                        'rotate_control' => [
                                            'label' => __('Rotate Control', 'gmap-embed'),
                                            'meta_enable' => 'wpgmap_rotate_control',
                                            'meta_pos' => 'wpgmap_rotate_control_pos'
                                        ],
                                        'scale_control' => [
                                            'label' => __('Scale Control', 'gmap-embed'),
                                            'meta_enable' => 'wpgmap_scale_control',
                                            'meta_pos' => 'wpgmap_scale_control_pos'
                                        ],
                                    ];

                                    $gmap_embed_admin_control_positions = [
                                        '' => __('Global/Default', 'gmap-embed'),
                                        'TOP_LEFT' => 'TOP_LEFT',
                                        'TOP_CENTER' => 'TOP_CENTER',
                                        'TOP_RIGHT' => 'TOP_RIGHT',
                                        'LEFT_TOP' => 'LEFT_TOP',
                                        'LEFT_CENTER' => 'LEFT_CENTER',
                                        'LEFT_BOTTOM' => 'LEFT_BOTTOM',
                                        'RIGHT_TOP' => 'RIGHT_TOP',
                                        'RIGHT_CENTER' => 'RIGHT_CENTER',
                                        'RIGHT_BOTTOM' => 'RIGHT_BOTTOM',
                                        'BOTTOM_LEFT' => 'BOTTOM_LEFT',
                                        'BOTTOM_CENTER' => 'BOTTOM_CENTER',
                                        'BOTTOM_RIGHT' => 'BOTTOM_RIGHT',
                                    ];

                                    foreach ($gmap_embed_admin_map_controls as $gmap_embed_admin_control_key => $gmap_embed_admin_control_data) {
                                        $gmap_embed_admin_enabled_val = isset($gmap_embed_admin_single_map->{$gmap_embed_admin_control_data['meta_enable']}) ? $gmap_embed_admin_single_map->{$gmap_embed_admin_control_data['meta_enable']} : '';
                                        $gmap_embed_admin_position_val = isset($gmap_embed_admin_single_map->{$gmap_embed_admin_control_data['meta_pos']}) ? $gmap_embed_admin_single_map->{$gmap_embed_admin_control_data['meta_pos']} : '';
                                        ?>
                                        <div class="wgm-control-setting-row">
                                            <div class="wgm-control-main">
                                                <label class="wgm-control-title"><?php echo esc_html($gmap_embed_admin_control_data['label']); ?></label>
                                                <select name="<?php echo esc_attr($gmap_embed_admin_control_data['meta_enable']); ?>" id="<?php echo esc_attr($gmap_embed_admin_control_data['meta_enable']); ?>">
                                                    <option value="" <?php selected($gmap_embed_admin_enabled_val, ''); ?>><?php esc_html_e('Global/Default', 'gmap-embed'); ?></option>
                                                    <option value="1" <?php selected($gmap_embed_admin_enabled_val, '1'); ?>><?php esc_html_e('Enable', 'gmap-embed'); ?></option>
                                                    <option value="0" <?php selected($gmap_embed_admin_enabled_val, '0'); ?>><?php esc_html_e('Disable', 'gmap-embed'); ?></option>
                                                </select>
                                            </div>
                                            <div class="wgm-control-sub">
                                                <label class="wgm-control-sub-label"><?php esc_html_e('Position', 'gmap-embed'); ?></label>
                                                <select name="<?php echo esc_attr($gmap_embed_admin_control_data['meta_pos']); ?>" id="<?php echo esc_attr($gmap_embed_admin_control_data['meta_pos']); ?>">
                                                    <?php foreach ($gmap_embed_admin_control_positions as $gmap_embed_admin_pos_key => $gmap_embed_admin_pos_label) : ?>
                                                        <option value="<?php echo esc_attr($gmap_embed_admin_pos_key); ?>" <?php selected($gmap_embed_admin_position_val, $gmap_embed_admin_pos_key); ?>><?php echo esc_html($gmap_embed_admin_pos_label); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>

                    </table>
                </div>
                <div class="wp-gmap-tab-contents wgm_gmap_markers hidden" style="padding:15px;background:#fff;border-radius:6px;">
                    <?php
                    require_once plugin_dir_path(__FILE__) . 'markers-settings.php';
                    ?>
                </div>
                <div class="wp-gmap-tab-contents wgm_marker_listing hidden"
                    style="padding:15px;background:#fff;border-radius:6px;">
                    <!-- Redesigned Marker Listing UI -->
                    <?php if (!_wgm_is_premium()) : ?>
                        <div class="wgm-upgrade-banner">
                            <div class="wgm-upgrade-banner-icon">
                                <span class="dashicons dashicons-star-filled"></span>
                            </div>
                            <div class="wgm-upgrade-banner-content">
                                <p>
                                    <?php 
                                    echo sprintf(
                                        /* translators: %s: Premium version URL. */
                                        esc_html__('Marker listing styles and advanced options are available in the %s.', 'gmap-embed'),
                                        '<b>' . esc_html__('Premium Version', 'gmap-embed') . '</b>'
                                    );
                                    ?>
                                </p>
                            </div>
                            <a href="<?php echo esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=edit-map-marker-listing-tab-banner'); ?>" target="_blank" class="wgm-upgrade-banner-btn"><?php esc_html_e('Upgrade Now', 'gmap-embed'); ?></a>
                        </div>
                    <?php endif; ?>
                    <div class="wgm-marker-listing-container">
                        <div class="wgm-row wgm-listing-top">
                            <div class="wgm-col wgm-col-7"><br />
                                <label
                                    for="wpgmap_marker_listing_style"><b><?php esc_html_e('Marker Listing Style', 'gmap-embed'); ?></b></label>

                                <div id="wpgmap_marker_listing_style" class="wgm-marker-style-grid wgm-style-carousel"
                                    role="radiogroup"
                                    aria-label="<?php esc_attr_e('Marker listing styles', 'gmap-embed'); ?>">

                                    <button type="button" class="wgm-carousel-btn wgm-carousel-prev"
                                        aria-label="<?php esc_attr_e('Previous styles', 'gmap-embed'); ?>">‹</button>
                                    <div class="wgm-style-viewport" aria-hidden="false">
                                        <div class="wgm-style-track">
                                            <?php
                                            $gmap_embed_current_style = (isset($gmap_embed_admin_single_map->wpgmap_marker_listing_style) && !empty($gmap_embed_admin_single_map->wpgmap_marker_listing_style)) ? $gmap_embed_admin_single_map->wpgmap_marker_listing_style : 'none';
                                            $gmap_embed_styles = array(
                                                'none' => array('file' => 'none', 'label' => __('No marker listing', 'gmap-embed'), 'img' => 'gmap_embed_logo.jpg'),
                                                'basic_table' => array('file' => 'basic_table', 'label' => __('Basic table', 'gmap-embed'), 'img' => 'basic_table.png'),
                                                'basic_list' => array('file' => 'basic_list', 'label' => __('Basic list', 'gmap-embed'), 'img' => 'basic_list.png'),
                                                'advanced_table' => array('file' => 'advanced_table', 'label' => __('Advanced table', 'gmap-embed'), 'img' => 'advanced_table.png'),
                                                'carousel' => array('file' => 'carousel', 'label' => __('Carousel', 'gmap-embed'), 'img' => 'carousel.png'),
                                                //'modern' => array('file' => 'modern', 'label' => __('Modern', 'gmap-embed')),
                                                //'grid' => array('file' => 'grid', 'label' => __('Grid', 'gmap-embed'))
                                            );
                                            foreach ($gmap_embed_styles as $gmap_embed_key => $gmap_embed_style_item) {
                                                // fallback thumb - keep path but use placeholder widely available in the plugin
                                                $gmap_embed_img_src = esc_url(plugins_url('../assets/images/' . $gmap_embed_style_item['img'], __FILE__));
                                                $gmap_embed_label_attr = esc_attr($gmap_embed_style_item['label']);
                                                $gmap_embed_is_checked = ($gmap_embed_current_style === $gmap_embed_key) ? 'true' : 'false';
                                                
                                                // Pro feature logic
                                                $gmap_embed_is_premium = _wgm_is_premium();
                                                $gmap_embed_is_pro_feature = (!$gmap_embed_is_premium && $gmap_embed_key !== 'none');
                                                $gmap_embed_disabled_attr = $gmap_embed_is_pro_feature ? 'disabled="disabled"' : '';
                                                $gmap_embed_class = 'wgm-style-option wgm-style-card';
                                                $gmap_embed_wrapper_style = '';
                                                $gmap_embed_data_notice = '';
                                                
                                                if ($gmap_embed_is_pro_feature) {
                                                    $gmap_embed_class .= ' wgm_enable_premium';
                                                    $gmap_embed_wrapper_style = 'style="opacity: 0.6;"'; // Lower opacity for content
                                                    // translators: %s: Premium version URL.
                                                    $gmap_embed_data_notice = esc_html(sprintf(__('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to <b> use this Marker Listing Style</b>.', 'gmap-embed'), esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=edit-map-marker-listing-style-lock')));
                                                }
                                                ?>
                                                <label class="<?php echo esc_attr($gmap_embed_class); ?>"
                                                    title="<?php echo esc_attr($gmap_embed_label_attr); ?>" role="radio"
                                                    aria-checked="<?php echo esc_attr($gmap_embed_is_checked); ?>"
                                                    tabindex="0" data-value="<?php echo esc_attr($gmap_embed_key); ?>"
                                                    <?php echo $gmap_embed_is_pro_feature ? 'data-notice="' . esc_attr($gmap_embed_data_notice) . '"' : ''; ?>>
                                                    <input type="radio" name="marker_listing_style"
                                                        value="<?php echo esc_attr($gmap_embed_key); ?>" <?php checked($gmap_embed_current_style, $gmap_embed_key); ?> 
                                                        <?php echo esc_attr($gmap_embed_disabled_attr); ?> />
                                                    
                                                    <div class="wgm-style-content-wrapper" <?php echo $gmap_embed_wrapper_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                                                        <div class="wgm-style-thumb">
                                                            <img src="<?php echo esc_url($gmap_embed_img_src); ?>"
                                                                alt="<?php echo esc_attr($gmap_embed_label_attr); ?>"
                                                                onerror="this.onerror=null;this.src='<?php echo esc_url(plugins_url('../assets/images/marker_listing_placeholder.png', __FILE__)); ?>'">
                                                        </div>
                                                        <div class="wgm-style-info">
                                                            <span class="wgm-style-label"><?php echo esc_html($gmap_embed_style_item['label']); ?></span>
                                                            <span class="wgm-style-selected-dot" aria-hidden="true"></span>
                                                        </div>
                                                    </div>
                                                </label>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <button type="button" class="wgm-carousel-btn wgm-carousel-next"
                                        aria-label="<?php esc_attr_e('Next styles', 'gmap-embed'); ?>">›</button>
                                </div>

                                <p class="description">
                                    <?php esc_html_e('Choose how markers are listed. Select "No marker listing" to disable.', 'gmap-embed'); ?>
                                </p>

                            </div>

                            <div class="wgm-col wgm-col-5">

                                <!-- Listing Placement - Premium Feature -->
                                <?php 
                                $gmap_embed_is_premium_placement = _wgm_is_premium();
                                $gmap_embed_placement_class = !$gmap_embed_is_premium_placement ? ' wgm_enable_premium' : '';
                                $gmap_embed_placement_style = !$gmap_embed_is_premium_placement ? 'style="opacity: 0.5; pointer-events: none;"' : '';
                                $gmap_embed_placement_notice = !$gmap_embed_is_premium_placement ? 'data-notice="' . esc_attr(sprintf(
                                     /* translators: %s: Premium version URL. */
                                     __('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to <b>change Listing Placement</b>.', 'gmap-embed'),
                                     esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=edit-map-marker-listing-placement-lock')
                                 )) . '"' : '';
                                ?>
                                <div class="<?php echo esc_attr($gmap_embed_placement_class); ?>" <?php echo $gmap_embed_placement_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo $gmap_embed_placement_notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                                    <fieldset class="wgm-fieldset-spacing" style="display:block;">
                                        <legend><b><?php esc_html_e('Listing Placement', 'gmap-embed'); ?></b></legend>
                                        
                                        <?php 
                                        $gmap_embed_admin_placement = (isset($gmap_embed_admin_single_map->wpgmap_marker_listing_placement) && !empty($gmap_embed_admin_single_map->wpgmap_marker_listing_placement)) ? $gmap_embed_admin_single_map->wpgmap_marker_listing_placement : 'below_map'; 
                                        ?>
                                        <input type="hidden" id="wpgmap_marker_listing_placement" name="marker_listing_placement" value="<?php echo esc_attr($gmap_embed_admin_placement); ?>">
                                        
                                        <div class="wgm-placement-grid">
                                            <div class="wgm-placement-btn <?php echo ($gmap_embed_admin_placement == 'above_map') ? 'active' : ''; ?>" data-value="above_map">
                                                <i class="fas fa-arrow-up"></i>
                                                <span><?php esc_html_e('Above', 'gmap-embed'); ?></span>
                                            </div>
                                            <div class="wgm-placement-btn <?php echo ($gmap_embed_admin_placement == 'below_map') ? 'active' : ''; ?>" data-value="below_map">
                                                <i class="fas fa-arrow-down"></i>
                                                <span><?php esc_html_e('Below', 'gmap-embed'); ?></span>
                                            </div>
                                            <div class="wgm-placement-btn <?php echo ($gmap_embed_admin_placement == 'left_map') ? 'active' : ''; ?>" data-value="left_map">
                                                <i class="fas fa-arrow-left"></i>
                                                <span><?php esc_html_e('Left', 'gmap-embed'); ?></span>
                                            </div>
                                            <div class="wgm-placement-btn <?php echo ($gmap_embed_admin_placement == 'right_map') ? 'active' : ''; ?>" data-value="right_map">
                                                <i class="fas fa-arrow-right"></i>
                                                <span><?php esc_html_e('Right', 'gmap-embed'); ?></span>
                                            </div>
                                        </div>
                                         <div style="margin-top: 15px;">
                                            <label for="wpgmap_marker_listing_width"><b><?php esc_html_e('Marker Listing Width (px)', 'gmap-embed'); ?></b></label>
                                            <input type="number" id="wpgmap_marker_listing_width" name="marker_listing_width" value="<?php echo (isset($gmap_embed_admin_single_map->wpgmap_marker_listing_width)) ? esc_attr($gmap_embed_admin_single_map->wpgmap_marker_listing_width) : ''; ?>" class="regular-text" style="width: 100%; margin-top: 5px;" placeholder="<?php esc_attr_e('e.g. 300 (leave empty for default)', 'gmap-embed'); ?>">
                                            <p class="description"><?php esc_html_e('Default: 300px for Left/Right placement, 100% for Above/Below.', 'gmap-embed'); ?></p>
                                        </div>
                                    </fieldset>
                                </div>

                                <?php if (!_wgm_is_premium()) : ?>
                                    <div class="wgm_enable_premium" style="opacity: 0.5; position: relative; margin-top: 20px;" <?php
                                        echo 'data-notice="' . esc_attr(sprintf(
                                            /* translators: %s: Premium version URL. */
                                            __('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to <b>configure Sorting & Ordering options</b>.', 'gmap-embed'),
                                            esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=edit-map-marker-listing-sorting-lock')
                                        )) . '"';
                                    ?>>
                                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10; cursor: pointer;"></div>
                                <?php endif; ?>
                                <fieldset class="wgm-fieldset-spacing" style="display:block; margin-top: 20px;">
                                    <legend><b><?php esc_html_e('Sorting & Ordering', 'gmap-embed'); ?></b></legend>
                                    
                                    <div style="margin-bottom: 15px;">
                                        <label for="wpgmap_marker_orderby_field"><b><?php esc_html_e('Order Markers By', 'gmap-embed'); ?></b></label>
                                        <?php 
                                        $gmap_embed_admin_orderby_field = (isset($gmap_embed_admin_single_map->wpgmap_marker_orderby_field) && !empty($gmap_embed_admin_single_map->wpgmap_marker_orderby_field)) ? $gmap_embed_admin_single_map->wpgmap_marker_orderby_field : 'id'; 
                                        ?>
                                        <select id="wpgmap_marker_orderby_field" name="marker_orderby_field" class="regular-text" style="width: 100%; margin-top: 5px;">
                                            <option value="id" <?php selected($gmap_embed_admin_orderby_field, 'id'); ?>><?php esc_html_e('ID', 'gmap-embed'); ?></option>
                                            <option value="marker_name" <?php selected($gmap_embed_admin_orderby_field, 'marker_name'); ?>><?php esc_html_e('Title/Name', 'gmap-embed'); ?></option>
                                            <option value="address" <?php selected($gmap_embed_admin_orderby_field, 'address'); ?>><?php esc_html_e('Address', 'gmap-embed'); ?></option>
                                            <option value="marker_desc" <?php selected($gmap_embed_admin_orderby_field, 'marker_desc'); ?>><?php esc_html_e('Description', 'gmap-embed'); ?></option>
                                            <option value="created_at" <?php selected($gmap_embed_admin_orderby_field, 'created_at'); ?>><?php esc_html_e('Created Date', 'gmap-embed'); ?></option>
                                            <option value="updated_at" <?php selected($gmap_embed_admin_orderby_field, 'updated_at'); ?>><?php esc_html_e('Updated Date', 'gmap-embed'); ?></option>
                                            <option value="lat_lng" <?php selected($gmap_embed_admin_orderby_field, 'lat_lng'); ?>><?php esc_html_e('Latitude/Longitude', 'gmap-embed'); ?></option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="wpgmap_marker_orderby_dir"><b><?php esc_html_e('Sorting Order', 'gmap-embed'); ?></b></label>
                                        <?php 
                                        $gmap_embed_admin_orderby_dir = (isset($gmap_embed_admin_single_map->wpgmap_marker_orderby_dir) && !empty($gmap_embed_admin_single_map->wpgmap_marker_orderby_dir)) ? $gmap_embed_admin_single_map->wpgmap_marker_orderby_dir : 'ASC'; 
                                        ?>
                                        <select id="wpgmap_marker_orderby_dir" name="marker_orderby_dir" class="small-text" style="width: 100%; margin-top: 5px;">
                                            <option value="ASC" <?php selected($gmap_embed_admin_orderby_dir, 'ASC'); ?>><?php esc_html_e('Ascending', 'gmap-embed'); ?></option>
                                            <option value="DESC" <?php selected($gmap_embed_admin_orderby_dir, 'DESC'); ?>><?php esc_html_e('Descending', 'gmap-embed'); ?></option>
                                        </select>
                                    </div>
                                </fieldset>
                                <?php if (!_wgm_is_premium()) : ?>
                                    </div>
                                    <div class="wgm_enable_premium" style="opacity: 0.5; position: relative; margin-top: 20px;" <?php
                                        echo 'data-notice="' . esc_attr(sprintf(
                                            /* translators: %s: Premium version URL. */
                                            __('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to <b>configure Icon Visibility options</b>.', 'gmap-embed'),
                                            esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=edit-map-marker-listing-icons-lock')
                                        )) . '"';
                                    ?>>
                                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10; cursor: pointer;"></div>
                                <?php endif; ?>
                                <fieldset class="wgm-fieldset-spacing" style="display:block; margin-top: 20px;">
                                    <legend><b><?php esc_html_e('Icon Visibility', 'gmap-embed'); ?></b></legend>
                                    <table class="form-table">
                                        <tr>
                                            <th><label for="wgm_enable_show_on_map_icon"><?php esc_html_e('Enable show on map icon', 'gmap-embed'); ?></label></th>
                                            <td>
                                                <div class="wgm_switch">
                                                    <input id="wgm_enable_show_on_map_icon" name="wgm_enable_show_on_map_icon" type="checkbox" <?php echo (isset($gmap_embed_admin_single_map->wgm_enable_show_on_map_icon) && $gmap_embed_admin_single_map->wgm_enable_show_on_map_icon == 1) ? 'checked' : ''; ?>>
                                                    <span class="wgm_slider round"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><label for="wgm_enable_get_direction_icon"><?php esc_html_e('Enable Get direction icon', 'gmap-embed'); ?></label></th>
                                            <td>
                                                <div class="wgm_switch">
                                                    <input id="wgm_enable_get_direction_icon" name="wgm_enable_get_direction_icon" type="checkbox" <?php echo (isset($gmap_embed_admin_single_map->wgm_enable_get_direction_icon) && $gmap_embed_admin_single_map->wgm_enable_get_direction_icon == 1) ? 'checked' : ''; ?>>
                                                    <span class="wgm_slider round"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><label for="wgm_enable_direction_link"><?php esc_html_e('Enable direction link', 'gmap-embed'); ?></label></th>
                                            <td>
                                                <div class="wgm_switch">
                                                    <input id="wgm_enable_direction_link" name="wgm_enable_direction_link" type="checkbox" <?php echo (isset($gmap_embed_admin_single_map->wgm_enable_direction_link) && $gmap_embed_admin_single_map->wgm_enable_direction_link == 1) ? 'checked' : ''; ?>>
                                                    <span class="wgm_slider round"></span>
                                                </div>
                                                <p class="description"><?php esc_html_e('If enabled, a "Get Directions" link will be added to the marker infowindow.', 'gmap-embed'); ?></p>
                                            </td>
                                        </tr>
                                    </table>
                                </fieldset>

                                <fieldset class="wgm-fieldset-spacing" style="display:block; margin-top: 20px;">
                                    <legend><b><?php esc_html_e('Search & Filtering', 'gmap-embed'); ?></b></legend>
                                    <table class="form-table">
                                        <tr>
                                            <th><label for="wgm_enable_title_search"><?php esc_html_e('Enable Title Search', 'gmap-embed'); ?></label></th>
                                            <td>
                                                <div class="wgm_switch">
                                                    <input id="wgm_enable_title_search" name="wgm_enable_title_search" type="checkbox" <?php echo (isset($gmap_embed_admin_single_map->wgm_enable_title_search) && $gmap_embed_admin_single_map->wgm_enable_title_search == 1) ? 'checked' : ''; ?>>
                                                    <span class="wgm_slider round"></span>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><label for="wgm_enable_category_filter"><?php esc_html_e('Enable Category Filter', 'gmap-embed'); ?></label></th>
                                            <td>
                                                <div class="wgm_switch">
                                                    <input id="wgm_enable_category_filter" name="wgm_enable_category_filter" type="checkbox" <?php echo (isset($gmap_embed_admin_single_map->wgm_enable_category_filter) && $gmap_embed_admin_single_map->wgm_enable_category_filter == 1) ? 'checked' : ''; ?>>
                                                    <span class="wgm_slider round"></span>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </fieldset>
                                <?php if (!_wgm_is_premium()) : ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="wp-gmap-tab-contents wgm_store_locator hidden" style="padding:15px;background:#fff;border-radius:6px;">
                    <?php if (!_wgm_is_premium()) : ?>
                        <div class="wgm-upgrade-banner">
                            <div class="wgm-upgrade-banner-icon">
                                <span class="dashicons dashicons-star-filled"></span>
                            </div>
                            <div class="wgm-upgrade-banner-content">
                                <p>
                                    <?php 
                                    echo sprintf(
                                        /* translators: %s: Premium version URL. */
                                        esc_html__('Store Locator feature is available in the %s.', 'gmap-embed'),
                                        '<b>' . esc_html__('Premium Version', 'gmap-embed') . '</b>'
                                    );
                                    ?>
                                </p>
                            </div>
                            <a href="<?php echo esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=edit-map-store-locator-tab-btn'); ?>" target="_blank" class="wgm-upgrade-banner-btn"><?php esc_html_e('Upgrade Now', 'gmap-embed'); ?></a>
                        </div>
                        <div class="wgm_enable_premium" style="opacity: 0.5; position: relative;" <?php
                            echo 'data-notice="' . esc_attr(sprintf(
                                /* translators: %s: Premium version URL. */
                                __('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to <b> use Store Locator Feature</b>.', 'gmap-embed'),
                                esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=edit-map-store-locator-lock')
                            )) . '"';
                        ?>>
                            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 10; cursor: pointer;"></div>
                    <?php endif; ?>
                    <table class="form-table">
                        <tr>
                            <th><label for="wgm_enable_store_locator"><?php esc_html_e('Enable Store Locator', 'gmap-embed'); ?></label></th>
                            <td>
                                <div class="wgm_switch">
                                    <input id="wgm_enable_store_locator" name="wgm_enable_store_locator" type="checkbox" <?php echo (isset($gmap_embed_admin_single_map->wgm_enable_store_locator) && $gmap_embed_admin_single_map->wgm_enable_store_locator == 1) ? 'checked' : ''; ?>>
                                    <span class="wgm_slider round"></span>
                                </div>
                                <p class="description"><?php esc_html_e('When enabled, a radius-based search control will be displayed on the map.', 'gmap-embed'); ?></p>
                            </td>
                        </tr>
                        <!-- Moved to Marker Listing tab -->
                        <tr>
                            <th><label for="wgm_title_search_placeholder"><?php esc_html_e('Title Search Placeholder', 'gmap-embed'); ?></label></th>
                            <td>
                                <input type="text" id="wgm_title_search_placeholder" class="regular-text" value="<?php echo (isset($gmap_embed_admin_single_map->wgm_title_search_placeholder) && !empty($gmap_embed_admin_single_map->wgm_title_search_placeholder)) ? esc_attr($gmap_embed_admin_single_map->wgm_title_search_placeholder) : esc_attr__('Search by Title/Description', 'gmap-embed'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wgm_address_search_placeholder"><?php esc_html_e('Address Search Placeholder', 'gmap-embed'); ?></label></th>
                            <td>
                                <input type="text" id="wgm_address_search_placeholder" class="regular-text" value="<?php echo (isset($gmap_embed_admin_single_map->wgm_address_search_placeholder) && !empty($gmap_embed_admin_single_map->wgm_address_search_placeholder)) ? esc_attr($gmap_embed_admin_single_map->wgm_address_search_placeholder) : esc_attr__('Search by Address, Zip Code...', 'gmap-embed'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wgm_default_address"><?php esc_html_e('Default Address', 'gmap-embed'); ?></label></th>
                            <td>
                                <input type="text" id="wgm_default_address" class="regular-text" value="<?php echo isset($gmap_embed_admin_single_map->wgm_default_address) ? esc_attr($gmap_embed_admin_single_map->wgm_default_address) : ''; ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wgm_default_radius"><?php esc_html_e('Default Radius', 'gmap-embed'); ?></label></th>
                            <td>
                                <input type="number" id="wgm_default_radius" class="small-text" value="<?php echo isset($gmap_embed_admin_single_map->wgm_default_radius) ? esc_attr($gmap_embed_admin_single_map->wgm_default_radius) : ''; ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wgm_not_found_message"><?php esc_html_e('Not Found Message', 'gmap-embed'); ?></label></th>
                            <td>
                                <input type="text" id="wgm_not_found_message" class="regular-text" value="<?php echo (isset($gmap_embed_admin_single_map->wgm_not_found_message) && !empty($gmap_embed_admin_single_map->wgm_not_found_message)) ? esc_attr($gmap_embed_admin_single_map->wgm_not_found_message) : esc_attr__('No results found.', 'gmap-embed'); ?>">
                            </td>
                        </tr>

                        <tr>
                            <th><label for="wgm_hide_markers_until_search"><?php esc_html_e('Hide markers until search', 'gmap-embed'); ?></label></th>
                            <td>
                                <div class="wgm_switch">
                                    <input id="wgm_hide_markers_until_search" name="wgm_hide_markers_until_search" type="checkbox" <?php echo (isset($gmap_embed_admin_single_map->wgm_hide_markers_until_search) && $gmap_embed_admin_single_map->wgm_hide_markers_until_search == 1) ? 'checked' : ''; ?>>
                                    <span class="wgm_slider round"></span>
                                </div>
                                <p class="description"><?php esc_html_e('If enabled, markers will be hidden until a search is performed.', 'gmap-embed'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wgm_show_center_icon"><?php esc_html_e('Show center point as an icon', 'gmap-embed'); ?></label></th>
                            <td>
                                <div class="wgm_switch">
                                    <input id="wgm_show_center_icon" name="wgm_show_center_icon" type="checkbox" <?php echo (isset($gmap_embed_admin_single_map->wgm_show_center_icon) && $gmap_embed_admin_single_map->wgm_show_center_icon == 1) ? 'checked' : ''; ?>>
                                    <span class="wgm_slider round"></span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wgm_show_distance"><?php esc_html_e('Show distance from search', 'gmap-embed'); ?></label></th>
                            <td>
                                <div class="wgm_switch">
                                    <input id="wgm_show_distance" name="wgm_show_distance" type="checkbox" <?php echo (isset($gmap_embed_admin_single_map->wgm_show_distance) && $gmap_embed_admin_single_map->wgm_show_distance == 1) ? 'checked' : ''; ?>>
                                    <span class="wgm_slider round"></span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wgm_distance_unit"><?php esc_html_e('Show distance in', 'gmap-embed'); ?></label></th>
                            <td>
                                <select id="wgm_distance_unit">
                                    <option value="miles" <?php echo (isset($gmap_embed_admin_single_map->wgm_distance_unit) && $gmap_embed_admin_single_map->wgm_distance_unit === 'miles') ? 'selected' : ''; ?>><?php esc_html_e('Miles', 'gmap-embed'); ?></option>
                                    <option value="km" <?php echo (isset($gmap_embed_admin_single_map->wgm_distance_unit) && $gmap_embed_admin_single_map->wgm_distance_unit === 'km') ? 'selected' : ''; ?>><?php esc_html_e('Kilometers', 'gmap-embed'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wgm_sort_by_distance"><?php esc_html_e('Markers listing sort by distance', 'gmap-embed'); ?></label></th>
                            <td>
                                <div class="wgm_switch">
                                    <input id="wgm_sort_by_distance" name="wgm_sort_by_distance" type="checkbox" <?php echo (isset($gmap_embed_admin_single_map->wgm_sort_by_distance) && $gmap_embed_admin_single_map->wgm_sort_by_distance == 1) ? 'checked' : ''; ?>>
                                    <span class="wgm_slider round"></span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wgm_store_locator_placement"><?php esc_html_e('Store Locator Placement', 'gmap-embed'); ?></label></th>
                            <td>
                                <select id="wgm_store_locator_placement">
                                    <option value="inside_map" <?php echo (isset($gmap_embed_admin_single_map->wgm_store_locator_placement) && $gmap_embed_admin_single_map->wgm_store_locator_placement === 'inside_map') ? 'selected' : ( (!isset($gmap_embed_admin_single_map->wgm_store_locator_placement) || empty($gmap_embed_admin_single_map->wgm_store_locator_placement)) ? 'selected' : '' ); ?>><?php esc_html_e('Inside Map', 'gmap-embed'); ?></option>
                                    <option value="above_map" <?php echo (isset($gmap_embed_admin_single_map->wgm_store_locator_placement) && $gmap_embed_admin_single_map->wgm_store_locator_placement === 'above_map') ? 'selected' : ''; ?>><?php esc_html_e('Above Map', 'gmap-embed'); ?></option>
                                    <option value="below_map" <?php echo (isset($gmap_embed_admin_single_map->wgm_store_locator_placement) && $gmap_embed_admin_single_map->wgm_store_locator_placement === 'below_map') ? 'selected' : ''; ?>><?php esc_html_e('Below Map', 'gmap-embed'); ?></option>
                                </select>
                            </td>
                        </tr>
                        
                        <!-- Circle Settings -->
                        <tr>
                            <th colspan="2"><h3 style="margin:0;"><?php esc_html_e('Radius Circle Settings', 'gmap-embed'); ?></h3></th>
                        </tr>
                        <tr>
                            <th><label for="wgm_radius_circle_stroke_color"><?php esc_html_e('Stroke Color', 'gmap-embed'); ?></label></th>
                            <td>
                                <input type="color" id="wgm_radius_circle_stroke_color" value="<?php echo (isset($gmap_embed_admin_single_map->wgm_radius_circle_stroke_color) && !empty($gmap_embed_admin_single_map->wgm_radius_circle_stroke_color)) ? esc_attr($gmap_embed_admin_single_map->wgm_radius_circle_stroke_color) : '#4285F4'; ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wgm_radius_circle_stroke_opacity"><?php esc_html_e('Stroke Opacity (0-1)', 'gmap-embed'); ?></label></th>
                            <td>
                                <input type="number" step="0.1" min="0" max="1" id="wgm_radius_circle_stroke_opacity" class="small-text" value="<?php echo (isset($gmap_embed_admin_single_map->wgm_radius_circle_stroke_opacity) && $gmap_embed_admin_single_map->wgm_radius_circle_stroke_opacity !== '') ? esc_attr($gmap_embed_admin_single_map->wgm_radius_circle_stroke_opacity) : '0.8'; ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wgm_radius_circle_stroke_weight"><?php esc_html_e('Stroke Weight', 'gmap-embed'); ?></label></th>
                            <td>
                                <input type="number" min="0" id="wgm_radius_circle_stroke_weight" class="small-text" value="<?php echo (isset($gmap_embed_admin_single_map->wgm_radius_circle_stroke_weight) && $gmap_embed_admin_single_map->wgm_radius_circle_stroke_weight !== '') ? esc_attr($gmap_embed_admin_single_map->wgm_radius_circle_stroke_weight) : '2'; ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wgm_radius_circle_fill_color"><?php esc_html_e('Fill Color', 'gmap-embed'); ?></label></th>
                            <td>
                                <input type="color" id="wgm_radius_circle_fill_color" value="<?php echo (isset($gmap_embed_admin_single_map->wgm_radius_circle_fill_color) && !empty($gmap_embed_admin_single_map->wgm_radius_circle_fill_color)) ? esc_attr($gmap_embed_admin_single_map->wgm_radius_circle_fill_color) : '#4285F4'; ?>">
                            </td>
                        </tr>
                        <tr>
                            <th><label for="wgm_radius_circle_fill_opacity"><?php esc_html_e('Fill Opacity (0-1)', 'gmap-embed'); ?></label></th>
                            <td>
                                <input type="number" step="0.1" min="0" max="1" id="wgm_radius_circle_fill_opacity" class="small-text" value="<?php echo (isset($gmap_embed_admin_single_map->wgm_radius_circle_fill_opacity) && $gmap_embed_admin_single_map->wgm_radius_circle_fill_opacity !== '') ? esc_attr($gmap_embed_admin_single_map->wgm_radius_circle_fill_opacity) : '0.2'; ?>">
                            </td>
                        </tr>
                    </table>
                    <?php if (!_wgm_is_premium()) : ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            

            <div class="wp-gmap-preview">
                <h1 id="wpgmap_heading_preview" style="padding: 0px;margin: 0px;">
                    <?php echo esc_html($gmap_embed_admin_single_map->wpgmap_title); ?>
                </h1>
                <input id="wgm_pac_input" class="wgm_controls" type="text"
                    placeholder="<?php esc_html_e('Search by Address, Zip Code, (Latitude,Longitude)', 'gmap-embed'); ?>" />
                <!-- Category Filter Toggle Icon -->

<div id="wgm_map" style="height: 520px;"></div>
                <div class="" style="width: 100%;float:left;text-align: right;margin-bottom: 5px;margin-top: 5px;">
                    <span class="spinner" style="margin: 0 !important;float: none;"></span>
                    <button class="button wgm_btn" style="width: auto;padding: 5px 12px;"
                        id="wp-gmap-embed-update"><?php esc_html_e('Update Map', 'gmap-embed'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>