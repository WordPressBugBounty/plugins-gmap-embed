<?php

namespace WGMSRM\Classes;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class Config
 * 
 * Centralizes all plugin settings and options.
 */
class Config
{
	/**
	 * Get a plugin option with a default value.
	 * 
	 * @param string $key     Option key.
	 * @param mixed  $default Default value.
	 * @return mixed Sanatized option value.
	 */
	public function get($key, $default = null)
	{
		$value = get_option($key, $default);
		return $this->sanitize_option($key, $value);
	}

	/**
	 * Sanitize options based on key.
	 * 
	 * @param string $key   Option key.
	 * @param mixed  $value Option value.
	 * @return mixed Sanatized value.
	 */
	private function sanitize_option($key, $value)
	{
		switch ($key) {
			case 'wpgmap_api_key':
			case 'srm_gmap_lng':
			case 'srm_gmap_region':
			case 'wpgmap_s_custom_css':
			case 'wpgmap_s_custom_js':
			case '_wgm_load_map_api_condition':
			case '_wgm_prevent_other_plugin_theme_api_load':
			case '_wgm_distance_unit':
			case '_wgm_minimum_role_for_map_edit':
			case '_wgm_disable_full_screen_control':
			case '_wgm_disable_street_view':
			case '_wgm_disable_zoom_control':
			case '_wgm_disable_pan_control':
			case '_wgm_disable_map_type_control':
			case '_wgm_disable_mouse_wheel_zoom':
			case '_wgm_disable_mouse_dragging':
			case '_wgm_disable_mouse_double_click_zooming':
			case '_wgm_enable_direction_form_auto_complete':
				return is_string($value) ? sanitize_text_field($value) : $value;
			
			default:
				return $value;
		}
	}

	/**
	 * Helper to check if premium.
	 * 
	 * @return bool
	 */
	public function is_premium()
	{
		return $this->get('_wgm_is_p_v', 'N') === 'Y';
	}

	/**
	 * Get the API key.
	 * 
	 * @return string
	 */
	public function get_api_key()
	{
		return $this->get('wpgmap_api_key', '');
	}
}
