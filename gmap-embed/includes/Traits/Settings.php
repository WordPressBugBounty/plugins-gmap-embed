<?php

namespace WGMSRM\Traits;

use WGMSRM\Classes\Migration;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Trait Settings
 */
trait Settings
{

    /**
     * Settings section callback code(BLANK NOW)
     */
    public function gmap_embed_settings_section_callback()
    {
        // code...
    }

    /**
     * Custom CSS part markup
     */
    public function gmap_embed_s_custom_css_markup()
    { ?>
        <textarea rows="10" cols="100" name="wpgmap_s_custom_css" id="wpgmap_custom_css"><?php echo esc_html(get_option('wpgmap_s_custom_css')); ?></textarea>
        <p class="description" id="tagline-description" style="font-style: italic;">
            <?php esc_html_e('Add your custom CSS code if needed.', 'gmap-embed'); ?>
        </p>
    <?php
    }

    /**
     * Custom JS part markup
     */
    public function wpgmap_s_custom_js_markup()
    {
    ?>
        <textarea rows="10" cols="100" name="wpgmap_s_custom_js" id="wpgmap_custom_js"><?php echo esc_html(get_option('wpgmap_s_custom_js')); ?></textarea>
        <p class="description" id="tagline-description" style="font-style: italic;">
            <?php esc_html_e('Add your custom JS code if needed.', 'gmap-embed'); ?>
        </p>
    <?php
    }

    /**
     * Where Map API engine should be -> Markup
     *
     * @since 1.7.5
     */
    public function wgm_load_api_condition_markup()
    {
    ?>
        <select name="_wgm_load_map_api_condition" id="_wgm_load_map_api_condition">
            <option value="where-required" <?php echo esc_attr(get_option('_wgm_load_map_api_condition') == 'where-required' ? 'selected' : ''); ?>>
                Where required
            </option>
            <option value="always" <?php echo esc_attr(get_option('_wgm_load_map_api_condition') == 'always' ? 'selected' : ''); ?>>
                Always
            </option>
            <option value="only-front-end" <?php echo esc_attr(get_option('_wgm_load_map_api_condition') == 'only-front-end' ? 'selected' : ''); ?>>
                Only Front End
            </option>
            <option value="only-back-end" <?php echo esc_attr(get_option('_wgm_load_map_api_condition') == 'only-back-end' ? 'selected' : ''); ?>>
                Only Back End
            </option>
            <option value="never" <?php echo esc_attr(get_option('_wgm_load_map_api_condition') == 'never' ? 'selected' : ''); ?>>
                Never
            </option>
        </select>
    <?php
    }

    /**
     * Directions option -> Distance Unit
     *
     * @since 1.9.0
     */
    public function wgm_distance_unit()
    {
    ?>
        <select name="_wgm_distance_unit" id="_wgm_distance_unit">
            <option value="km" <?php echo esc_attr(get_option('_wgm_distance_unit') == 'km' ? 'selected' : ''); ?>>
                Kilometers
            </option>
            <option value="mi" <?php echo esc_attr(get_option('_wgm_distance_unit') == 'mi' ? 'selected' : ''); ?>>
                Miles
            </option>
        </select>
    <?php
    }

    /**
     * Minimum Role for Map Edit
     *
     * @since 1.9.0
     */
    public function _wgm_minimum_role_for_map_edit()
    {
    ?>
        <select id="_wgm_minimum_role_for_map_edit" name="_wgm_minimum_role_for_map_edit">
            <option value="manage_options" <?php echo esc_attr(get_option('_wgm_minimum_role_for_map_edit') == 'manage_options' ? 'selected' : ''); ?>>Administrator</option>
            <option value="edit_pages" <?php echo esc_attr(get_option('_wgm_minimum_role_for_map_edit') == 'edit_pages' ? 'selected' : ''); ?>>Editor</option>
            <option value="publish_posts" <?php echo esc_attr(get_option('_wgm_minimum_role_for_map_edit') == 'publish_posts' ? 'selected' : ''); ?>>Author</option>
            <option value="edit_posts" <?php echo esc_attr(get_option('_wgm_minimum_role_for_map_edit') == 'edit_posts' ? 'selected' : ''); ?>>Contributor</option>
            <option value="read" <?php echo esc_attr(get_option('_wgm_minimum_role_for_map_edit') == 'read' ? 'selected' : ''); ?>>Subscriber</option>
        </select>
    <?php
    }

    /**
     * Prevent API load by other plugin or theme markup
     *
     * @since 1.7.5
     */
    public function wgm_prevent_api_load_markup()
    {
    ?>
        <input type="checkbox" name="_wgm_prevent_other_plugin_theme_api_load" id="_wgm_prevent_other_plugin_theme_api_load" value="Y" <?php echo esc_attr(get_option('_wgm_prevent_other_plugin_theme_api_load') == 'Y' ? 'checked="checked"' : ''); ?>> Check this option if your want to prevent other plugin or theme loading map api, in case of you are getting api key error, included multiple api key error.
        <br />
    <?php
    }

    /**
     * General Map Settings under General Settings
     *
     * @since 1.7.5
     */
    public function wgm_general_map_settings_markup()
    {
    ?>
        <input type="checkbox" name="_wgm_disable_full_screen_control" id="_wgm_disable_full_screen_control" value="Y" <?php echo esc_attr(get_option('_wgm_disable_full_screen_control') == 'Y' ? 'checked="checked"' : ''); ?>> Disable Full Screen Control
        <br />
        <input type="checkbox" name="_wgm_disable_street_view" id="_wgm_disable_street_view" value="Y" <?php echo esc_attr(get_option('_wgm_disable_street_view') == 'Y' ? 'checked="checked"' : ''); ?>> Disable StreetView
        <br />
        <input type="checkbox" name="_wgm_disable_zoom_control" id="_wgm_disable_zoom_control" value="Y" <?php echo esc_attr(get_option('_wgm_disable_zoom_control') == 'Y' ? 'checked="checked"' : ''); ?>> Disable Zoom Controls
        <br />
        <input type="checkbox" name="_wgm_disable_pan_control" id="_wgm_disable_pan_control" value="Y" <?php echo esc_attr(get_option('_wgm_disable_pan_control') == 'Y' ? 'checked="checked"' : ''); ?>> Disable Pan Controls
        <br />
        <input type="checkbox" name="_wgm_disable_map_type_control" id="_wgm_disable_map_type_control" value="Y" <?php echo esc_attr(get_option('_wgm_disable_map_type_control') == 'Y' ? 'checked="checked"' : ''); ?>> Disable Map Type Controls
        <br />
        <input type="checkbox" name="_wgm_disable_mouse_wheel_zoom" id="_wgm_disable_mouse_wheel_zoom" value="Y" <?php echo esc_attr(get_option('_wgm_disable_mouse_wheel_zoom') == 'Y' ? 'checked="checked"' : ''); ?>> Disable Mouse Wheel Zoom
        <br />
        <input type="checkbox" name="_wgm_disable_mouse_dragging" id="_wgm_disable_mouse_dragging" value="Y" <?php echo esc_attr(get_option('_wgm_disable_mouse_dragging') == 'Y' ? 'checked="checked"' : ''); ?>> Disable Mouse Dragging
        <br />
        <input type="checkbox" name="_wgm_disable_mouse_double_click_zooming" id="_wgm_disable_mouse_double_click_zooming" value="Y" <?php echo esc_attr(get_option('_wgm_disable_mouse_double_click_zooming') == 'Y' ? 'checked="checked"' : ''); ?>> Disable Mouse Double Click Zooming
        <br />
        <?php if (_wgm_is_premium()) { ?>
            <input type="checkbox" name="_wgm_enable_direction_form_auto_complete" id="_wgm_enable_direction_form_auto_complete" value="Y" <?php echo esc_attr(get_option('_wgm_enable_direction_form_auto_complete') == 'Y' ? 'checked="checked"' : ''); ?>> Enable direction From/To Auto Complete
            <br />
        <?php
        }
    }

    /**
     * Language selection part markup
     */
    public function gmap_embed_s_map_language_markup()
    {
        ?>
        <select id="srm_gmap_lng" name="srm_gmap_lng" class="regular-text" style="width: 100%;max-width:100%;">
            <?php
            $wpgmap_languages = gmap_embed_get_languages();
            if (count($wpgmap_languages) > 0) {
                foreach ($wpgmap_languages as $lng_key => $language) {
                    $selected = '';
                    if (get_option('srm_gmap_lng', 'en') == $lng_key) {
                        $selected = 'selected';
                    }
                    echo "<option value='" . esc_attr($lng_key) . "' " . esc_attr($selected) . '>' . esc_html($language) . '</option>';
                }
            }
            ?>
        </select>
        <p class="description" id="tagline-description" style="font-style: italic;">
            <?php esc_html_e('Chose your desired map language', 'gmap-embed'); ?>
        </p>
    <?php
    }

    /**
     * Region selection part markup
     */
    public function gmap_embed_s_map_region_markup()
    {
    ?>
        <select id="region" name="srm_gmap_region" class="regular-text" style="width: 100%;max-width: 100%;">
            <?php
            $wpgmap_regions = gmap_embed_get_regions();
            if (count($wpgmap_regions) > 0) {
                foreach ($wpgmap_regions as $region_key => $region) {
                    $selected = '';
                    if (get_option('srm_gmap_region', 'US') == $region_key) {
                        $selected = 'selected';
                    }
                    echo "<option value='" . esc_attr($region_key) . "' " . esc_attr($selected) . '>' . esc_html($region) . '</option>';
                }
            }
            ?>

        </select>
        <p class="description" id="tagline-description" style="font-style: italic;">
            <?php esc_html_e('Chose your regional area', 'gmap-embed'); ?>
        </p>
<?php
    }

    /**
     * Settings section, fields register
     */
    public function gmapsrm_settings()
    {
        // Language settings section and fields
        add_settings_section(
            'gmap_embed_language_settings_section',
            __('Map Language and Regional Settings<hr/>', 'gmap-embed'),
            array($this, 'gmap_embed_settings_section_callback'),
            'gmap-embed-settings-page-ls'
        );

        add_settings_field(
            'srm_gmap_lng',
            __('Map Language:', 'gmap-embed'),
            array($this, 'gmap_embed_s_map_language_markup'),
            'gmap-embed-settings-page-ls',
            'gmap_embed_language_settings_section'
        );

        add_settings_field(
            'srm_gmap_region',
            __('Map Region:', 'gmap-embed'),
            array($this, 'gmap_embed_s_map_region_markup'),
            'gmap-embed-settings-page-ls',
            'gmap_embed_language_settings_section'
        );

        // Custom Scripts section and fields
        add_settings_section(
            'gmap_embed_custom_scripts_section',
            __('Custom Scripts<hr/>', 'gmap-embed'),
            array($this, 'gmap_embed_settings_section_callback'),
            'gmap-embed-settings-page-cs'
        );

        add_settings_field(
            'wpgmap_s_custom_css',
            __('Custom CSS:', 'gmap-embed'),
            array($this, 'gmap_embed_s_custom_css_markup'),
            'gmap-embed-settings-page-cs',
            'gmap_embed_custom_scripts_section'
        );

        add_settings_field(
            'wpgmap_s_custom_js',
            __('Custom JS:', 'gmap-embed'),
            array($this, 'wpgmap_s_custom_js_markup'),
            'gmap-embed-settings-page-cs',
            'gmap_embed_custom_scripts_section'
        );

        /**
         * General map settings section and fields
         *
         * @since 1.7.5
         */
        add_settings_section(
            'gmap_embed_general_map_settings_section',
            __('', 'gmap-embed'),
            array($this, 'gmap_embed_settings_section_callback'),
            'gmap-embed-general-settings'
        );

        // General map settings related all fields are included
        add_settings_field(
            'wpgm_disable_full_screen_control',
            __('Map Control Options:', 'gmap-embed'),
            array($this, 'wgm_general_map_settings_markup'),
            'gmap-embed-general-settings',
            'gmap_embed_general_map_settings_section'
        );

        /**
         * Advance settings section and fields
         *
         * @since 1.7.5
         */
        add_settings_section(
            'wgm_advance_settings_section',
            __('', 'gmap-embed'),
            array($this, 'gmap_embed_settings_section_callback'),
            'wgm_advance_settings-page'
        );

        add_settings_field(
            '_wgm_load_map_api_condition',
            __('Load Map API:', 'gmap-embed'),
            array($this, 'wgm_load_api_condition_markup'),
            'wgm_advance_settings-page',
            'wgm_advance_settings_section'
        );

        add_settings_field(
            '_wgm_prevent_other_plugin_theme_api_load',
            __('Prevent Map API loading for other plugin and themes:', 'gmap-embed'),
            array($this, 'wgm_prevent_api_load_markup'),
            'wgm_advance_settings-page',
            'wgm_advance_settings_section'
        );

        add_settings_field(
            '_wgm_distance_unit',
            __('Distance Unit:', 'gmap-embed'),
            array($this, 'wgm_distance_unit'),
            'wgm_advance_settings-page',
            'wgm_advance_settings_section'
        );

        add_settings_field(
            '_wgm_minimum_role_for_map_edit',
            __('Minimum Role for Map Editor:', 'gmap-embed'),
            array($this, '_wgm_minimum_role_for_map_edit'),
            'wgm_advance_settings-page',
            'wgm_advance_settings_section'
        );

        register_setting('wpgmap_general_settings', 'srm_gmap_lng');
        register_setting('wpgmap_general_settings', 'srm_gmap_region');
        register_setting('wpgmap_general_settings', 'wpgmap_s_custom_css');
        register_setting('wpgmap_general_settings', 'wpgmap_s_custom_js');
        /**
         * Map General Settings
         *
         * @since 1.7.5
         */
        register_setting('wpgmap_general_settings', '_wgm_disable_full_screen_control');
        register_setting('wpgmap_general_settings', '_wgm_disable_street_view');
        register_setting('wpgmap_general_settings', '_wgm_disable_zoom_control');
        register_setting('wpgmap_general_settings', '_wgm_disable_pan_control');
        register_setting('wpgmap_general_settings', '_wgm_disable_map_type_control');
        register_setting('wpgmap_general_settings', '_wgm_disable_mouse_wheel_zoom');
        register_setting('wpgmap_general_settings', '_wgm_disable_mouse_dragging');
        register_setting('wpgmap_general_settings', '_wgm_disable_mouse_double_click_zooming');
        register_setting('wpgmap_general_settings', '_wgm_enable_direction_form_auto_complete');
        /**
         * Advance Settings
         *
         * @since 1.7.5
         */
        register_setting('wgm_advance_settings', '_wgm_load_map_api_condition');
        register_setting('wgm_advance_settings', '_wgm_prevent_other_plugin_theme_api_load');
        register_setting('wgm_advance_settings', '_wgm_distance_unit');
        register_setting('wgm_advance_settings', '_wgm_minimum_role_for_map_edit');
    }
}
