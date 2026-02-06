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
     * Sanitize the map language option.
     */
    public function wgm_sanitize_gmap_language($value)
    {
        $languages = function_exists('gmap_embed_get_languages') ? gmap_embed_get_languages() : array();
        $value = sanitize_text_field($value);
        return array_key_exists($value, $languages) ? $value : 'en';
    }

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
        <textarea rows="10" cols="100" name="wpgmap_s_custom_css"
            id="wpgmap_custom_css"><?php echo esc_textarea(get_option('wpgmap_s_custom_css')); ?></textarea>
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
        <textarea rows="10" cols="100" name="wpgmap_s_custom_js"
            id="wpgmap_custom_js"><?php echo esc_textarea(get_option('wpgmap_s_custom_js')); ?></textarea>
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
            <option value="where-required" <?php selected(get_option('_wgm_load_map_api_condition'), 'where-required'); ?>>
                <?php esc_html_e('Where required', 'gmap-embed'); ?>
            </option>
            <option value="always" <?php selected(get_option('_wgm_load_map_api_condition'), 'always'); ?>>
                <?php esc_html_e('Always', 'gmap-embed'); ?>
            </option>
            <option value="only-front-end" <?php selected(get_option('_wgm_load_map_api_condition'), 'only-front-end'); ?>>
                <?php esc_html_e('Only Front End', 'gmap-embed'); ?>
            </option>
            <option value="only-back-end" <?php selected(get_option('_wgm_load_map_api_condition'), 'only-back-end'); ?>>
                <?php esc_html_e('Only Back End', 'gmap-embed'); ?>
            </option>
            <option value="never" <?php selected(get_option('_wgm_load_map_api_condition'), 'never'); ?>>
                <?php esc_html_e('Never', 'gmap-embed'); ?>
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
            <option value="km" <?php selected(get_option('_wgm_distance_unit'), 'km'); ?>>
                <?php esc_html_e('Kilometers', 'gmap-embed'); ?>
            </option>
            <option value="mi" <?php selected(get_option('_wgm_distance_unit'), 'mi'); ?>>
                <?php esc_html_e('Miles', 'gmap-embed'); ?>
            </option>
        </select>
		<?php
	}

	/**
	 * Category selection logic -> OR/AND
	 *
	 * @since 1.2
	 */
	public function wgm_category_selection_logic_markup()
	{
		?>
		<select name="wgm_category_selection_logic" id="wgm_category_selection_logic">
			<option value="OR" <?php selected(get_option('wgm_category_selection_logic', 'OR'), 'OR'); ?>>
				<?php esc_html_e('OR (Show if any category matches)', 'gmap-embed'); ?>
			</option>
			<option value="AND" <?php selected(get_option('wgm_category_selection_logic', 'OR'), 'AND'); ?>>
				<?php esc_html_e('AND (Show if all categories match)', 'gmap-embed'); ?>
			</option>
		</select>
		<p class="description">
			<?php esc_html_e('Choose how multiple selected categories should be filtered on the frontend.', 'gmap-embed'); ?>
		</p>
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
            <option value="manage_options" <?php selected(get_option('_wgm_minimum_role_for_map_edit'), 'manage_options'); ?>>
                <?php esc_html_e('Administrator', 'gmap-embed'); ?>
            </option>
            <option value="edit_pages" <?php selected(get_option('_wgm_minimum_role_for_map_edit'), 'edit_pages'); ?>>
                <?php esc_html_e('Editor', 'gmap-embed'); ?>
            </option>
            <option value="publish_posts" <?php selected(get_option('_wgm_minimum_role_for_map_edit'), 'publish_posts'); ?>>
                <?php esc_html_e('Author', 'gmap-embed'); ?>
            </option>
            <option value="edit_posts" <?php selected(get_option('_wgm_minimum_role_for_map_edit'), 'edit_posts'); ?>>
                <?php esc_html_e('Contributor', 'gmap-embed'); ?>
            </option>
            <option value="read" <?php selected(get_option('_wgm_minimum_role_for_map_edit'), 'read'); ?>>
                <?php esc_html_e('Subscriber', 'gmap-embed'); ?>
            </option>
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
        <input type="checkbox" name="_wgm_prevent_other_plugin_theme_api_load" id="_wgm_prevent_other_plugin_theme_api_load"
            value="Y" <?php checked(get_option('_wgm_prevent_other_plugin_theme_api_load'), 'Y'); ?>>
        <?php esc_html_e('Check this option if your want to prevent other plugin or theme loading map api, in case of you are getting api key error, included multiple api key error.', 'gmap-embed'); ?>
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
        <input type="checkbox" name="_wgm_disable_full_screen_control" id="_wgm_disable_full_screen_control" value="Y" <?php checked(get_option('_wgm_disable_full_screen_control'), 'Y'); ?>>
        <?php esc_html_e('Disable Full Screen Control', 'gmap-embed'); ?>
        <br />
        <input type="checkbox" name="_wgm_disable_street_view" id="_wgm_disable_street_view" value="Y" <?php checked(get_option('_wgm_disable_street_view'), 'Y'); ?>> <?php esc_html_e('Disable StreetView', 'gmap-embed'); ?>
        <br />
        <input type="checkbox" name="_wgm_disable_zoom_control" id="_wgm_disable_zoom_control" value="Y" <?php checked(get_option('_wgm_disable_zoom_control'), 'Y'); ?>>
        <?php esc_html_e('Disable Zoom Controls', 'gmap-embed'); ?>
        <br />
        <input type="checkbox" name="_wgm_disable_pan_control" id="_wgm_disable_pan_control" value="Y" <?php checked(get_option('_wgm_disable_pan_control'), 'Y'); ?>> <?php esc_html_e('Disable Pan Controls', 'gmap-embed'); ?>
        <br />
        <input type="checkbox" name="_wgm_disable_map_type_control" id="_wgm_disable_map_type_control" value="Y" <?php checked(get_option('_wgm_disable_map_type_control'), 'Y'); ?>>
        <?php esc_html_e('Disable Map Type Controls', 'gmap-embed'); ?>
        <br />
        <input type="checkbox" name="_wgm_disable_mouse_wheel_zoom" id="_wgm_disable_mouse_wheel_zoom" value="Y" <?php checked(get_option('_wgm_disable_mouse_wheel_zoom'), 'Y'); ?>>
        <?php esc_html_e('Disable Mouse Wheel Zoom', 'gmap-embed'); ?>
        <br />
        <input type="checkbox" name="_wgm_disable_mouse_dragging" id="_wgm_disable_mouse_dragging" value="Y" <?php checked(get_option('_wgm_disable_mouse_dragging'), 'Y'); ?>>
        <?php esc_html_e('Disable Mouse Dragging', 'gmap-embed'); ?>
        <br />
        <input type="checkbox" name="_wgm_disable_mouse_double_click_zooming" id="_wgm_disable_mouse_double_click_zooming"
            value="Y" <?php checked(get_option('_wgm_disable_mouse_double_click_zooming'), 'Y'); ?>>
        <?php esc_html_e('Disable Mouse Double Click Zooming', 'gmap-embed'); ?>
        <br />
        <?php if (_wgm_is_premium()) { ?>
            <input type="checkbox" name="_wgm_enable_direction_form_auto_complete" id="_wgm_enable_direction_form_auto_complete"
                value="Y" <?php checked(get_option('_wgm_enable_direction_form_auto_complete'), 'Y'); ?>>
            <?php esc_html_e('Enable direction From/To Auto Complete', 'gmap-embed'); ?>
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
                    $selected = (get_option('srm_gmap_lng', 'en') == $lng_key) ? 'selected' : '';
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
                    $selected = (get_option('srm_gmap_region', 'US') == $region_key) ? 'selected' : '';
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
            null, // Use null instead of empty string for no title
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
            null, // Use null instead of empty string for no title
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

		// Marker Settings section and fields
		add_settings_section(
			'wgm_marker_settings_section',
			null, // Use null instead of empty string for no title
			array($this, 'gmap_embed_settings_section_callback'),
			'wgm_marker_settings-page'
		);

		add_settings_field(
			'wgm_category_selection_logic',
			__('Category Selection Logic:', 'gmap-embed'),
			array($this, 'wgm_category_selection_logic_markup'),
			'wgm_marker_settings-page',
			'wgm_marker_settings_section'
		);

        register_setting(
            'wpgmap_general_settings',
            'srm_gmap_lng',
            array(
                'sanitize_callback' => array($this, 'wgm_sanitize_gmap_language')
            )
        );
        register_setting('wpgmap_general_settings', 'srm_gmap_region', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('wpgmap_general_settings', 'wpgmap_s_custom_css', array(
            'sanitize_callback' => 'wp_strip_all_tags'
        ));
        register_setting('wpgmap_general_settings', 'wpgmap_s_custom_js', array(
            'sanitize_callback' => 'wp_strip_all_tags'
        ));
        /**
         * Map General Settings
         *
         * @since 1.7.5
         */
        register_setting('wpgmap_general_settings', '_wgm_disable_full_screen_control', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('wpgmap_general_settings', '_wgm_disable_street_view', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('wpgmap_general_settings', '_wgm_disable_zoom_control', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('wpgmap_general_settings', '_wgm_disable_pan_control', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('wpgmap_general_settings', '_wgm_disable_map_type_control', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('wpgmap_general_settings', '_wgm_disable_mouse_wheel_zoom', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('wpgmap_general_settings', '_wgm_disable_mouse_dragging', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('wpgmap_general_settings', '_wgm_disable_mouse_double_click_zooming', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('wpgmap_general_settings', '_wgm_enable_direction_form_auto_complete', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        /**
         * Advance Settings
         *
         * @since 1.7.5
         */
        register_setting('wgm_advance_settings', '_wgm_load_map_api_condition', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('wgm_advance_settings', '_wgm_prevent_other_plugin_theme_api_load', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
        register_setting('wgm_advance_settings', '_wgm_distance_unit', array(
            'sanitize_callback' => 'sanitize_text_field'
        ));
		register_setting('wgm_advance_settings', '_wgm_minimum_role_for_map_edit', array(
			'sanitize_callback' => 'sanitize_text_field'
		));

		/**
		 * Marker Settings
		 *
		 * @since 1.2
		 */
		register_setting('wgm_marker_settings', 'wgm_category_selection_logic', array(
			'sanitize_callback' => 'sanitize_text_field'
		));
	}

	/**
	 * Save API Key from settings page
	 * @since 1.9.1
	 */
	public function wgm_save_api_key()
	{
		check_admin_referer('wgm_settings_api_key_update', '_wp_nonce');

		if (!current_user_can($this->capability)) {
			wp_die(esc_html__('You do not have permission to update settings.', 'gmap-embed'));
		}

		$api_key = isset($_POST['wpgmapembed_key']) ? sanitize_text_field(wp_unslash($_POST['wpgmapembed_key'])) : '';
		if ($api_key !== '') {
			update_option('wpgmap_api_key', $api_key, 'yes');
		}

		$settings_nonce = wp_create_nonce('wgm_settings');
		wp_safe_redirect(add_query_arg(['page' => 'wpgmapembed-settings', 'message' => 3, 'wgm_settings_nonce' => $settings_nonce], admin_url('admin.php')));
		exit;
	}

	/**
	 * Save and validate License Key from settings page
	 * @since 1.9.1
	 */
	public function wgm_save_license()
	{
		check_admin_referer('wgm_settings_lc_key_update', '_wp_nonce');

		if (!current_user_can($this->capability)) {
			wp_die(esc_html__('You do not have permission to update settings.', 'gmap-embed'));
		}

		$license = isset($_POST['wpgmapembed_license']) ? sanitize_text_field(wp_unslash($_POST['wpgmapembed_license'])) : '';
		$api_base_url = 'https://wpgooglemap.com';

		$msg_code = 5; // Default error

		if ($license !== '') {
			$remote_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
			$remote_host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';

			$request_url = esc_url_raw($api_base_url . '/paypal/api.php?key=' . rawurlencode($license) . '&ip=' . rawurlencode($remote_ip) . '&host=' . rawurlencode($remote_host));
			$response = wp_remote_get($request_url);
			$code = wp_remote_retrieve_response_code($response);
			$body = wp_remote_retrieve_body($response);
			$data = json_decode($body);

			// Hardening: Robust validation of remote response
			$is_valid = (
				$code === 200 && 
				$data && 
				is_object($data) && 
				isset($data->status) && 
				$data->status === true
			);

			if ($is_valid) {
				update_option('wpgmapembed_license', $license, 'yes');
				update_option('_wgm_is_p_v', 'Y');
				$msg_code = 4; // Success
			} else {
				$msg_code = 5; // Error
			}
		} else {
			$msg_code = 6; // Empty
		}

		$settings_nonce = wp_create_nonce('wgm_settings');
		wp_safe_redirect(add_query_arg(['page' => 'wpgmapembed-settings', 'message' => $msg_code, 'wgm_settings_nonce' => $settings_nonce], admin_url('admin.php')));
		exit;
	}
}
