<?php

namespace WGMSRM\Classes;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class ShortcodeHandler
 * 
 * Handles data fetching and preparation for the map shortcode.
 */
class ShortcodeHandler
{
	/**
	 * Map ID
	 * @var int
	 */
	private $map_id;

	/**
	 * Instance count
	 * @var int
	 */
	private $count;

	/**
	 * Config instance
	 * @var Config
	 */
	private $config;

	/**
	 * ShortcodeHandler constructor.
	 * 
	 * @param int    $map_id Map ID.
	 * @param int    $count  Instance count.
	 * @param Config $config Config instance.
	 */
	public function __construct($map_id, $count, $config)
	{
		$this->map_id = intval($map_id);
		$this->count = intval($count);
		$this->config = $config;
	}

	/**
	 * Handle the shortcode logic.
	 * 
	 * @return string Rendered HTML.
	 */
	public function handle()
	{
		if ($this->map_id <= 0) {
			return $this->render_error(__('Shortcode attribute \'id\' is missing or invalid.', 'gmap-embed'));
		}

		$data = $this->fetch_metadata();
		$this->prepare_frontend_script($data);

		// Add extra components to data for sub-templates
		$data['data'] = $data;

		// Define legacy config early for theme templates
		$config_js = '<script type="text/javascript">window.wgm_config_' . $this->count . ' = ' . wp_json_encode($data['config']) . ';</script>';
		return $config_js . Bootstrap::instance()->render('public/main', $data);
	}

	/**
	 * Fetch all necessary metadata for the map.
	 * 
	 * @return array metadata.
	 */
	private function fetch_metadata()
	{
		$map_id = $this->map_id;

		$wgm_distance_unit = get_post_meta($map_id, 'wgm_distance_unit', true);
		$wgm_distance_unit = !empty($wgm_distance_unit) ? $wgm_distance_unit : 'miles';

		$wgm_default_radius = get_post_meta($map_id, 'wgm_default_radius', true);

		$wgm_radius_circle_stroke_opacity = get_post_meta($map_id, 'wgm_radius_circle_stroke_opacity', true);
		$wgm_radius_circle_stroke_opacity = is_numeric($wgm_radius_circle_stroke_opacity) ? floatval($wgm_radius_circle_stroke_opacity) : 0.8;

		$wgm_radius_circle_stroke_weight = get_post_meta($map_id, 'wgm_radius_circle_stroke_weight', true);
		$wgm_radius_circle_stroke_weight = is_numeric($wgm_radius_circle_stroke_weight) ? intval($wgm_radius_circle_stroke_weight) : 2;

		$wgm_radius_circle_fill_opacity = get_post_meta($map_id, 'wgm_radius_circle_fill_opacity', true);
		$wgm_radius_circle_fill_opacity = is_numeric($wgm_radius_circle_fill_opacity) ? floatval($wgm_radius_circle_fill_opacity) : 0.2;

		$wpgmap_center_lat_lng = esc_html(get_center_lat_lng_by_map_id($map_id));
		if (empty($wpgmap_center_lat_lng)) {
			$wpgmap_center_lat_lng = '40.73359922990751,-74.02791395625002';
		}

		$wpgmap_drawer_width = get_post_meta($map_id, 'wpgmap_direction_drawer_width', true);
		$wpgmap_drawer_width = !empty($wpgmap_drawer_width) ? $wpgmap_drawer_width : '300';

		$wgm_title_search_placeholder = get_post_meta($map_id, 'wgm_title_search_placeholder', true);
		$wgm_title_search_placeholder = !empty($wgm_title_search_placeholder) ? $wgm_title_search_placeholder : __('Search by Title/Description', 'gmap-embed');
		
		$wgm_address_search_placeholder = get_post_meta($map_id, 'wgm_address_search_placeholder', true);
		$wgm_address_search_placeholder = !empty($wgm_address_search_placeholder) ? $wgm_address_search_placeholder : __('Search by Address, Zip Code...', 'gmap-embed');

		$wgm_not_found_message = get_post_meta($map_id, 'wgm_not_found_message', true);
		$wgm_not_found_message = !empty($wgm_not_found_message) ? $wgm_not_found_message : __('No results found.', 'gmap-embed');

		global $wpdb;
		$cache_key = 'wgm_map_categories_' . $map_id;
		$categories = wp_cache_get($cache_key, 'gmap-embed');

		if (false === $categories) {
			// Get all category IDs used by markers on this map
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$marker_categories = $wpdb->get_col($wpdb->prepare(
				"SELECT category_id FROM {$wpdb->prefix}wgm_markers WHERE map_id = %d AND category_id != '0' AND category_id != ''",
				$map_id
			));

			$related_ids = [];
			if ($marker_categories) {
				foreach ($marker_categories as $cat_str) {
					$ids = explode(',', $cat_str);
					foreach ($ids as $id) {
						$id = intval(trim($id));
						if ($id > 0) {
							$related_ids[] = $id;
						}
					}
				}
				$related_ids = array_unique($related_ids);
			}

			if (!empty($related_ids)) {
				$placeholders = implode(',', array_fill(0, count($related_ids), '%d'));
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$query = $wpdb->prepare("SELECT id, name, icon, parent_id FROM {$wpdb->prefix}wgm_categories WHERE id IN ($placeholders) ORDER BY name ASC", ...$related_ids);
				$categories = $wpdb->get_results($query);
			} else {
				$categories = [];
			}

			wp_cache_set($cache_key, $categories, 'gmap-embed', 12 * HOUR_IN_SECONDS);
		}
		
		// Normalize parent_id to 0 if null
		foreach ($categories as $cat) {
			if (empty($cat->parent_id)) {
				$cat->parent_id = 0;
			}
		}

		return [
			'count'                              => $this->count,
			'wgm_map_id'                         => $map_id,
			'wpgmap_title'                       => esc_html(get_post_meta($map_id, 'wpgmap_title', true)),
			'wpgmap_show_heading'                => esc_html(get_post_meta($map_id, 'wpgmap_show_heading', true)),
			'wpgmap_heading_class'               => esc_attr(get_post_meta($map_id, 'wpgmap_heading_class', true)),
			'wpgmap_map_zoom'                    => intval(get_post_meta($map_id, 'wpgmap_map_zoom', true)),
			'wpgmap_map_width'                   => esc_attr(get_post_meta($map_id, 'wpgmap_map_width', true)),
			'wpgmap_map_height'                  => esc_attr(get_post_meta($map_id, 'wpgmap_map_height', true)),
			'wpgmap_map_type'                    => esc_html(get_post_meta($map_id, 'wpgmap_map_type', true)),
			'wpgmap_enable_direction'            => esc_html(get_post_meta($map_id, 'wpgmap_enable_direction', true)),
			'wpgmap_enable_modern_direction'     => esc_html(get_post_meta($map_id, 'wpgmap_enable_modern_direction', true)),
			'wpgmap_center_lat_lng'              => $wpgmap_center_lat_lng,
			'wgm_theme_json'                     => get_post_meta($map_id, 'wgm_theme_json', true),
			'marker_listing_type'                => get_post_meta($map_id, 'wpgmap_marker_listing_style', true) ?: 'none',
			'placement'                          => get_post_meta($map_id, 'wpgmap_marker_listing_placement', true) ?: 'below_map',
			'wgm_enable_store_locator'           => get_post_meta($map_id, 'wgm_enable_store_locator', true),
 			'wpgmap_drawer_width'                => $wpgmap_drawer_width,
			'wpgmap_marker_listing_width'        => get_post_meta($map_id, 'wpgmap_marker_listing_width', true),
			'wgm_enable_title_search'            => get_post_meta($map_id, 'wgm_enable_title_search', true),
			'wgm_title_search_placeholder'       => $wgm_title_search_placeholder,
			'wgm_address_search_placeholder'     => $wgm_address_search_placeholder,
			'wgm_default_address'                => get_post_meta($map_id, 'wgm_default_address', true),
			'wgm_default_radius'                 => $wgm_default_radius,
			'wgm_not_found_message'              => $wgm_not_found_message,
			'wgm_hide_markers_until_search'      => get_post_meta($map_id, 'wgm_hide_markers_until_search', true),
			'wgm_show_center_icon'               => get_post_meta($map_id, 'wgm_show_center_icon', true),
			'wgm_show_distance'                  => get_post_meta($map_id, 'wgm_show_distance', true),
			'wgm_distance_unit'                  => $wgm_distance_unit,
			'wgm_store_locator_placement'        => get_post_meta($map_id, 'wgm_store_locator_placement', true) ?: 'inside_map',
			'wgm_sort_by_distance'               => get_post_meta($map_id, 'wgm_sort_by_distance', true),
			'wgm_enable_show_on_map_icon'        => get_post_meta($map_id, 'wgm_enable_show_on_map_icon', true),
			'wgm_enable_get_direction_icon'      => get_post_meta($map_id, 'wgm_enable_get_direction_icon', true),
			'wgm_enable_direction_link'          => get_post_meta($map_id, 'wgm_enable_direction_link', true),
			'wgm_enable_direction_link'          => get_post_meta($map_id, 'wgm_enable_direction_link', true),
			'wgm_enable_category_filter'        => get_post_meta($map_id, 'wgm_enable_category_filter', true),
			'wgm_radius_circle_stroke_color'     => get_post_meta($map_id, 'wgm_radius_circle_stroke_color', true) ?: '#4285F4',
			// Map Controls Customization
			'map_zoom_control_enabled'           => get_post_meta($map_id, 'wpgmap_zoom_control', true),
			'map_zoom_control_pos'               => get_post_meta($map_id, 'wpgmap_zoom_control_pos', true),
			'map_type_control_enabled'           => get_post_meta($map_id, 'wpgmap_map_type_control', true),
			'map_type_control_pos'               => get_post_meta($map_id, 'wpgmap_map_type_control_pos', true),
			'street_view_control_enabled'        => get_post_meta($map_id, 'wpgmap_street_view_control', true),
			'street_view_control_pos'            => get_post_meta($map_id, 'wpgmap_street_view_control_pos', true),
			'fullscreen_control_enabled'         => get_post_meta($map_id, 'wpgmap_fullscreen_control', true),
			'fullscreen_control_pos'             => get_post_meta($map_id, 'wpgmap_fullscreen_control_pos', true),
			'rotate_control_enabled'             => get_post_meta($map_id, 'wpgmap_rotate_control', true),
			'rotate_control_pos'                 => get_post_meta($map_id, 'wpgmap_rotate_control_pos', true),
			'scale_control_enabled'              => get_post_meta($map_id, 'wpgmap_scale_control', true),
			'scale_control_pos'                  => get_post_meta($map_id, 'wpgmap_scale_control_pos', true),
			'wgm_radius_circle_stroke_opacity'   => $wgm_radius_circle_stroke_opacity,
			'wgm_radius_circle_stroke_weight'    => $wgm_radius_circle_stroke_weight,
			'wgm_radius_circle_fill_color'       => get_post_meta($map_id, 'wgm_radius_circle_fill_color', true) ?: '#4285F4',
			'wgm_radius_circle_fill_opacity'     => $wgm_radius_circle_fill_opacity,
			'config'                             => [
				'hide_markers'              => (get_post_meta($map_id, 'wgm_hide_markers_until_search', true) == '1'),
				'show_center_icon'          => (get_post_meta($map_id, 'wgm_show_center_icon', true) == '1'),
				'show_distance'             => (get_post_meta($map_id, 'wgm_show_distance', true) == '1'),
				'sort_by_distance'          => (get_post_meta($map_id, 'wgm_sort_by_distance', true) == '1'),
				'distance_unit'             => $wgm_distance_unit,
				'placement'                 => get_post_meta($map_id, 'wgm_store_locator_placement', true) ?: 'inside_map',
				'enable_show_on_map_icon'   => (get_post_meta($map_id, 'wgm_enable_show_on_map_icon', true) == '1'),
				'enable_get_direction_icon' => (get_post_meta($map_id, 'wgm_enable_get_direction_icon', true) == '1'),
				'enable_direction_link'     => (get_post_meta($map_id, 'wgm_enable_direction_link', true) == '1'),
				'enable_modern_direction'    => (get_post_meta($map_id, 'wpgmap_enable_modern_direction', true) == 1),
				'enable_category_filter'     => (get_post_meta($map_id, 'wgm_enable_category_filter', true) == '1'),
				'category_selection_logic'   => $this->config->get('wgm_category_selection_logic', 'OR'),
			],
			'categories'                         => $categories,
		];
	}

	/**
	 * Prepare and enqueue frontend JavaScript.
	 * 
	 * @param array $data Map data.
	 */
	private function prepare_frontend_script($data)
	{
		$wgm_js_data = [
			'count'                => $data['count'],
			'map_id'               => $data['wgm_map_id'],
			'map_center'           => [
				'lat' => (isset(explode(',', $data['wpgmap_center_lat_lng'])[0]) && is_numeric(explode(',', $data['wpgmap_center_lat_lng'])[0])) ? floatval(explode(',', $data['wpgmap_center_lat_lng'])[0]) : 40.73359922990751,
				'lng' => (isset(explode(',', $data['wpgmap_center_lat_lng'])[1]) && is_numeric(explode(',', $data['wpgmap_center_lat_lng'])[1])) ? floatval(explode(',', $data['wpgmap_center_lat_lng'])[1]) : -74.02791395625002,
			],
			'map_zoom'             => $data['wpgmap_map_zoom'],
			'map_type'             => $data['wpgmap_map_type'],
			'theme_json'           => $data['wgm_theme_json'],
			'ajax_url'             => admin_url('admin-ajax.php'),
			'marker_listing_type'  => $data['marker_listing_type'],
			'distance_unit_system' => $this->config->get('_wgm_distance_unit', 'km') == 'km' ? 'METRIC' : 'IMPERIAL',
			'options'              => [
				'disable_mouse_wheel_zoom'           => $this->config->get('_wgm_disable_mouse_wheel_zoom'),
				'zoom_control'                       => ($data['map_zoom_control_enabled'] !== '') ? ($data['map_zoom_control_enabled'] === '1') : ($this->config->get('_wgm_disable_zoom_control') !== 'Y'),
				'zoom_control_pos'                   => $data['map_zoom_control_pos'] ?: 'TOP_LEFT',
				'map_type_control'                   => ($data['map_type_control_enabled'] !== '') ? ($data['map_type_control_enabled'] === '1') : ($this->config->get('_wgm_disable_map_type_control') !== 'Y'),
				'map_type_control_pos'               => $data['map_type_control_pos'] ?: 'TOP_LEFT',
				'street_view_control'                => ($data['street_view_control_enabled'] !== '') ? ($data['street_view_control_enabled'] === '1') : ($this->config->get('_wgm_disable_street_view') !== 'Y'),
				'street_view_control_pos'            => $data['street_view_control_pos'] ?: 'RIGHT_TOP',
				'fullscreen_control'                 => ($data['fullscreen_control_enabled'] !== '') ? ($data['fullscreen_control_enabled'] === '1') : ($this->config->get('_wgm_disable_full_screen_control') !== 'Y'),
				'fullscreen_control_pos'             => $data['fullscreen_control_pos'] ?: 'RIGHT_TOP',
				'rotate_control'                     => ($data['rotate_control_enabled'] !== '') ? ($data['rotate_control_enabled'] === '1') : true,
				'rotate_control_pos'                 => $data['rotate_control_pos'] ?: 'RIGHT_TOP',
				'scale_control'                      => ($data['scale_control_enabled'] !== '') ? ($data['scale_control_enabled'] === '1') : true,
				'scale_control_pos'                  => $data['scale_control_pos'] ?: 'BOTTOM_LEFT',
				'disable_mouse_dragging'              => $this->config->get('_wgm_disable_mouse_dragging'),
				'disable_mouse_double_click_zooming' => $this->config->get('_wgm_disable_mouse_double_click_zooming'),
				'disable_pan_control'                 => $this->config->get('_wgm_disable_pan_control'),
				'enable_direction_form_auto_complete' => $this->config->get('_wgm_enable_direction_form_auto_complete') === 'Y',
			],
			'config'               => $data['config'],
			'categories'           => $data['categories'],
			'circle'               => [
				'stroke_color'   => $data['wgm_radius_circle_stroke_color'],
				'stroke_opacity' => $data['wgm_radius_circle_stroke_opacity'],
				'stroke_weight'  => $data['wgm_radius_circle_stroke_weight'],
				'fill_color'     => $data['wgm_radius_circle_fill_color'],
				'fill_opacity'   => $data['wgm_radius_circle_fill_opacity'],
			],
			'nonces'               => [
				'marker_render' => wp_create_nonce('wgm_marker_render'),
			],
			'default_icon'         => WGM_PLUGIN_URL . 'admin/assets/images/markers/default.png',
			'i18n'                 => [
				'get_directions'       => __('Get Directions', 'gmap-embed'),
				'via'                  => __('Via...', 'gmap-embed'),
				'remove_waypoint'      => __('Remove waypoint', 'gmap-embed'),
				'geo_fail'             => __('Unable to retrieve your location.', 'gmap-embed'),
				'geo_not_supported'    => __('Geolocation is not supported by your browser.', 'gmap-embed'),
				'origin_dest_required' => __('Please enter both origin and destination.', 'gmap-embed'),
			],
		];

		wp_enqueue_script('wp-gmap-embed-front-js');
		wp_add_inline_script(
			'wp-gmap-embed-front-js',
			'window.wgm_front.init(' . wp_json_encode($wgm_js_data) . ');',
			'after'
		);
	}

	/**
	 * Render an error message.
	 * 
	 * @param string $message Error message.
	 * @return string Error HTML.
	 */
	private function render_error($message)
	{
		if (is_user_logged_in() && current_user_can('administrator')) {
			return sprintf(
				'<span class="wgm-admin-error">%s %s</span>',
				esc_html($message),
				__('This message is only visible to Administrator.', 'gmap-embed')
			);
		}
		return '';
	}
}
