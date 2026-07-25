<?php

namespace WGMSRM\Traits;

use WP_Query;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Trait MapCRUD: Map CRUD operation doing here
 */
trait MapCRUD
{

	/**
	 * Get all maps for datatable ajax request
	 *
	 * @since 1.7.5
	 */
	public function wgm_get_all_maps()
	{
		check_ajax_referer('wgm_get_all_maps', 'nonce');

		if (!current_user_can($this->capability)) {
			echo wp_json_encode(
				array(
					'responseCode' => 0,
					'message' => esc_html__('Unauthorized access tried.', 'gmap-embed'),
				)
			);
			wp_die();
		}

		$args = array(
			'post_type' => 'wpgmapembed',
			'posts_per_page' => -1,
			'post_status' => 'draft',
		);

		$return_json = array();
		$maps_list = new WP_Query($args);
		while ($maps_list->have_posts()) {
			$maps_list->the_post();
			$title = esc_html(get_post_meta(get_the_ID(), 'wpgmap_title', true));
			$type = esc_html(get_post_meta(get_the_ID(), 'wpgmap_map_type', true));
			$width = esc_html(get_post_meta(get_the_ID(), 'wpgmap_map_width', true));
			$height = esc_html(get_post_meta(get_the_ID(), 'wpgmap_map_height', true));
			$shortcode = '<input class="wpgmap-shortcode regular-text" style="width:100%!important;" type="text" value="' . esc_attr('[gmap-embed id=&quot;' . get_the_ID() . '&quot;]') . '"
                                                       onclick="this.select()"/>';
			$clone_btn = _wgm_is_premium()
				? '<button class="button media-button button-primary button-small wpgmap-clone" data-id="' . esc_attr(get_the_ID()) . '" title="' . esc_attr__('Clone Map', 'gmap-embed') . '" style="margin-right: 5px;"><i class="fas fa-copy"></i></button>'
				: '<button class="button media-button button-primary button-small wgm_enable_premium" style="margin-right: 5px; opacity: 0.5;" title="' . esc_attr__('Clone Map (Premium)', 'gmap-embed') . '" data-notice="' . esc_attr(sprintf(__('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to <b>Clone Maps</b>.', 'gmap-embed'), esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=map-list-clone-lock'))) . '"><i class="fas fa-copy"></i></button>';
			$action = '<button class="button media-button button-primary button-small wpgmap-copy-to-clipboard" data-id="' . esc_attr(get_the_ID()) . '" title="' . esc_attr__('Copy Shortcode', 'gmap-embed') . '" style="margin-right: 5px;"><i class="fas fa-code"></i></button>'
				. '<a href="?page=wpgmapembed&tag=edit&id=' . esc_attr(get_the_ID()) . '&wgm_map_create_nonce=' . wp_create_nonce('wgm_create_map') . '" class="button media-button button-primary button-small wpgmap-edit" data-id="' . esc_attr(get_the_ID()) . '" title="' . esc_attr__('Edit Map', 'gmap-embed') . '" style="margin-right: 5px;"><i class="fas fa-edit"></i>
                                                ' . esc_html__('Edit', 'gmap-embed') . '
                                            </a>'
				. $clone_btn . '<span type="button"
                                                    class="button media-button button-small  wgm_wpgmap_delete" data-id="' . esc_attr(get_the_ID()) . '" title="' . esc_attr__('Delete Map', 'gmap-embed') . '" style="background-color: #aa2828;color: white;opacity:0.7;"><i class="fas fa-trash"></i> ' . esc_html__('Delete', 'gmap-embed') . '
                                            </span>';
			$row = array(
				'id' => get_the_ID(),
				'title' => $title,
				'map_type' => $type,
				'width' => $width,
				'height' => $height,
				'shortcode' => $shortcode,
				'action' => $action,
			);
			$return_json[] = $row;
		}

		echo wp_json_encode(array('data' => $return_json));
		wp_die();
	}

	/**
	 * To save New Map Data
	 */
	public function save_wpgmapembed_data()
	{
		check_ajax_referer('wpgmapembed_save_map_data', 'nonce');

		if (!current_user_can($this->capability)) {
			echo wp_json_encode(
				array(
					'responseCode' => 0,
					'message' => esc_html__('Unauthorized access tried.', 'gmap-embed'),
				)
			);
			wp_die();
		}

		$error = '';
		// Getting ajax fields value
		$meta_data = array(
			'wpgmap_title' => isset($_POST['map_data']['wpgmap_title']) ? sanitize_text_field(wp_strip_all_tags(wp_unslash($_POST['map_data']['wpgmap_title']))) : '',
			'wpgmap_heading_class' => isset($_POST['map_data']['wpgmap_heading_class']) ? sanitize_html_class(wp_unslash($_POST['map_data']['wpgmap_heading_class'])) : '',
			'wpgmap_show_heading' => isset($_POST['map_data']['wpgmap_show_heading']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_show_heading'])) : '',
			'wpgmap_latlng' => isset($_POST['map_data']['wpgmap_latlng']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_latlng'])) : '',
			'wpgmap_map_zoom' => isset($_POST['map_data']['wpgmap_map_zoom']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_map_zoom'])) : '',
			'wpgmap_disable_zoom_scroll' => isset($_POST['map_data']['wpgmap_disable_zoom_scroll']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_disable_zoom_scroll'])) : '',
			'wpgmap_map_width' => isset($_POST['map_data']['wpgmap_map_width']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_map_width'])) : '',
			'wpgmap_map_height' => isset($_POST['map_data']['wpgmap_map_height']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_map_height'])) : '',
			'wpgmap_map_type' => isset($_POST['map_data']['wpgmap_map_type']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_map_type'])) : '',
			'wpgmap_show_infowindow' => isset($_POST['map_data']['wpgmap_show_infowindow']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_show_infowindow'])) : '',
			'wpgmap_enable_direction' => isset($_POST['map_data']['wpgmap_enable_direction']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_enable_direction'])) : '',
			'wpgmap_enable_modern_direction' => isset($_POST['map_data']['wpgmap_enable_modern_direction']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_enable_modern_direction'])) : '',
			'wpgmap_center_lat_lng' => isset($_POST['map_data']['wpgmap_center_lat_lng']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_center_lat_lng'])) : '',
			'wgm_theme_json' => isset($_POST['map_data']['wgm_theme_json']) ? sanitize_textarea_field(wp_unslash($_POST['map_data']['wgm_theme_json'])) : '',
 			'wpgmap_marker_listing_style' => isset($_POST['map_data']['wpgmap_marker_listing_style']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_marker_listing_style'])) : 'grid_view',
			'wpgmap_marker_listing_placement' => isset($_POST['map_data']['wpgmap_marker_listing_placement']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_marker_listing_placement'])) : 'below_map',
			'wpgmap_marker_listing_width' => isset($_POST['map_data']['wpgmap_marker_listing_width']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_marker_listing_width'])) : '',
			'wgm_enable_store_locator' => isset($_POST['map_data']['wgm_enable_store_locator']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_enable_store_locator'])) : 0,
			'wgm_enable_category_filter' => isset($_POST['map_data']['wgm_enable_category_filter']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_enable_category_filter'])) : 0,
            // Store Locator Extended Settings
            'wgm_enable_title_search' => isset($_POST['map_data']['wgm_enable_title_search']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_enable_title_search'])) : 0,
            'wgm_title_search_placeholder' => isset($_POST['map_data']['wgm_title_search_placeholder']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_title_search_placeholder'])) : '',
            'wgm_address_search_placeholder' => isset($_POST['map_data']['wgm_address_search_placeholder']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_address_search_placeholder'])) : '',
            'wgm_default_address' => isset($_POST['map_data']['wgm_default_address']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_default_address'])) : '',
            'wgm_default_radius' => isset($_POST['map_data']['wgm_default_radius']) ? intval(sanitize_text_field(wp_unslash($_POST['map_data']['wgm_default_radius']))) : '',
            'wgm_not_found_message' => isset($_POST['map_data']['wgm_not_found_message']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_not_found_message'])) : '',
            
            // Advanced Store Locator Settings
            'wgm_hide_markers_until_search' => isset($_POST['map_data']['wgm_hide_markers_until_search']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_hide_markers_until_search'])) : 0,
            'wgm_show_center_icon' => isset($_POST['map_data']['wgm_show_center_icon']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_show_center_icon'])) : 0,
            'wgm_show_distance' => isset($_POST['map_data']['wgm_show_distance']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_show_distance'])) : 0,
            'wgm_distance_unit' => isset($_POST['map_data']['wgm_distance_unit']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_distance_unit'])) : 'miles',
            'wgm_sort_by_distance' => isset($_POST['map_data']['wgm_sort_by_distance']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_sort_by_distance'])) : 0,
            'wgm_store_locator_placement' => isset($_POST['map_data']['wgm_store_locator_placement']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_store_locator_placement'])) : 'above_map',
            
            // Circle Settings
            'wgm_radius_circle_stroke_color' => isset($_POST['map_data']['wgm_radius_circle_stroke_color']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_radius_circle_stroke_color'])) : '#4285F4',
            'wgm_radius_circle_stroke_opacity' => isset($_POST['map_data']['wgm_radius_circle_stroke_opacity']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_radius_circle_stroke_opacity'])) : '0.8',
            'wgm_radius_circle_stroke_weight' => isset($_POST['map_data']['wgm_radius_circle_stroke_weight']) ? intval(sanitize_text_field(wp_unslash($_POST['map_data']['wgm_radius_circle_stroke_weight']))) : '2',
            'wgm_radius_circle_fill_color' => isset($_POST['map_data']['wgm_radius_circle_fill_color']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_radius_circle_fill_color'])) : '#4285F4',
            'wgm_radius_circle_fill_opacity' => isset($_POST['map_data']['wgm_radius_circle_fill_opacity']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_radius_circle_fill_opacity'])) : '0.2',

            // Marker Listing Extra Settings
            'marker_orderby_field' => isset($_POST['map_data']['marker_orderby_field']) ? sanitize_text_field(wp_unslash($_POST['map_data']['marker_orderby_field'])) : 'id',
            'marker_orderby_dir' => isset($_POST['map_data']['marker_orderby_dir']) ? sanitize_text_field(wp_unslash($_POST['map_data']['marker_orderby_dir'])) : 'ASC',
            'wpgmap_direction_drawer_width' => isset($_POST['map_data']['wpgmap_direction_drawer_width']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_direction_drawer_width'])) : '300',
            'wgm_enable_show_on_map_icon' => isset($_POST['map_data']['wgm_enable_show_on_map_icon']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_enable_show_on_map_icon'])) : 0,
            'wgm_enable_get_direction_icon' => isset($_POST['map_data']['wgm_enable_get_direction_icon']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_enable_get_direction_icon'])) : 0,
            'wgm_enable_direction_link' => isset($_POST['map_data']['wgm_enable_direction_link']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wgm_enable_direction_link'])) : 0,
            // Map Controls Customization
            'wpgmap_zoom_control' => isset($_POST['map_data']['wpgmap_zoom_control']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_zoom_control'])) : '',
            'wpgmap_zoom_control_pos' => isset($_POST['map_data']['wpgmap_zoom_control_pos']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_zoom_control_pos'])) : '',
            'wpgmap_map_type_control' => isset($_POST['map_data']['wpgmap_map_type_control']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_map_type_control'])) : '',
            'wpgmap_map_type_control_pos' => isset($_POST['map_data']['wpgmap_map_type_control_pos']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_map_type_control_pos'])) : '',
            'wpgmap_street_view_control' => isset($_POST['map_data']['wpgmap_street_view_control']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_street_view_control'])) : '',
            'wpgmap_street_view_control_pos' => isset($_POST['map_data']['wpgmap_street_view_control_pos']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_street_view_control_pos'])) : '',
            'wpgmap_fullscreen_control' => isset($_POST['map_data']['wpgmap_fullscreen_control']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_fullscreen_control'])) : '',
            'wpgmap_fullscreen_control_pos' => isset($_POST['map_data']['wpgmap_fullscreen_control_pos']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_fullscreen_control_pos'])) : '',
            'wpgmap_rotate_control' => isset($_POST['map_data']['wpgmap_rotate_control']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_rotate_control'])) : '',
            'wpgmap_rotate_control_pos' => isset($_POST['map_data']['wpgmap_rotate_control_pos']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_rotate_control_pos'])) : '',
            'wpgmap_scale_control' => isset($_POST['map_data']['wpgmap_scale_control']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_scale_control'])) : '',
            'wpgmap_scale_control_pos' => isset($_POST['map_data']['wpgmap_scale_control_pos']) ? sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_scale_control_pos'])) : '',
		);
        if (isset($meta_data['wpgmap_marker_listing_style'])) {
            update_option('wpgmap_marker_listing_style', $meta_data['wpgmap_marker_listing_style']);
        }
		$decoded_theme = json_decode(sanitize_textarea_field(wp_unslash($meta_data['wgm_theme_json'])));
		$meta_data['wgm_theme_json'] = (json_last_error() === JSON_ERROR_NONE && !is_null($decoded_theme)) ? wp_json_encode($decoded_theme) : '[]';
		$action_type = isset($_POST['map_data']['action_type']) ? sanitize_text_field(wp_unslash($_POST['map_data']['action_type'])) : '';
		if ($meta_data['wpgmap_latlng'] === '') {
			$error = esc_html__('Please input Latitude and Longitude', 'gmap-embed');
		}
		if (strlen($error) > 0) {
			echo wp_json_encode(
				array(
					'responseCode' => 0,
					'message' => $error,
				)
			);
			wp_die();
		}

		$post_id = 0;
		if ($action_type === 'save') {
			// Saving post array
			$post_array = array(
				'post_type' => 'wpgmapembed',
			);
			$post_id = wp_insert_post($post_array);
		} elseif ($action_type === 'update') {
			$post_id = isset($_POST['map_data']['post_id']) ? intval(sanitize_text_field(wp_unslash($_POST['map_data']['post_id']))) : 0;
		}

		// Updating post meta
		foreach ($meta_data as $key => $value) {
			$this->wgm_update_post_meta($post_id, $key, $value);
		}
		$return_array = array(
			'responseCode' => 1,
			'post_id' => intval($post_id),
		);
		if ($action_type === 'save') {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Required for updating custom table
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}wgm_markers SET map_id = %d WHERE map_id = %d",
					intval($post_id),
					0
				)
			);
			$return_array['message'] = esc_html__('Map created Successfully.', 'gmap-embed');
		} elseif ($action_type === 'update') {
			$return_array['message'] = esc_html__('Map updated Successfully.', 'gmap-embed');
		}
		echo wp_json_encode($return_array);
		wp_die();
	}

	/**
	 * Classic editor: Loading popup content on WP Google Map click
	 */
	public function load_popup_wpgmapembed_list()
	{
		check_ajax_referer('wpgmapembed_popup_load_map_data', 'nonce');

		if (!current_user_can($this->capability)) {
			echo wp_json_encode(
				array(
					'responseCode' => 0,
					'message' => esc_html__('Unauthorized access tried.', 'gmap-embed'),
				)
			);
			wp_die();
		}
		$content = '';
		$args = array(
			'post_type' => 'wpgmapembed',
			'posts_per_page' => -1,
			'post_status' => 'draft',
		);
		$maps_list = new WP_Query($args);

		while ($maps_list->have_posts()) {
			$maps_list->the_post();
			$title = get_post_meta(get_the_ID(), 'wpgmap_title', true);
			$content .= '<div class="wp-gmap-single">
                                        <div class="wp-gmap-single-left">
                                            <div class="wp-gmap-single-title">
                                                ' . esc_html($title) . '
                                            </div>
                                            <div class="wp-gmap-single-shortcode">
                                                <input class="wpgmap-shortcode regular-text" type="text" value="[gmap-embed id=&quot;' . esc_attr(get_the_ID()) . '&quot;]"
                                                       onclick="this.select()"/>
                                            </div>
                                        </div>
                                        <div class="wp-gmap-single-action">
                                            <button type="button"
                                                    class="button media-button button-primary button-large wpgmap-insert-shortcode">
                                                ' . esc_html__('Insert', 'gmap-embed') . '
                                            </button>                                            
                                        </div>
                                    </div>';
		}
		$allowed_html = [
			'a' => [],
			'br' => [],
			'em' => [],
			'strong' => [],
			'div' => [
				'class' => []
			],
			'button' => [
				'type' => [],
				'class' => []
			],
			'input' => [
				'class' => [],
				'value' => [],
				'name' => [],
				'onclick' => [],
				'type' => [],
			],
		];
		echo wp_kses(wp_unslash($content), $allowed_html);
		wp_die();
	}

	/**
	 * Get map data by mnap id
	 *
	 * @param string $gmap_id
	 *
	 * @return false|string
	 */
	public function get_wpgmapembed_data($gmap_id = 0)
	{
		if ($gmap_id == 0) {
			$gmap_id = 0;
			if (
				isset($_POST['_wgm_nonce']) &&
				wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wgm_nonce'])), '_wgm_nonce') &&
				isset($_POST['wpgmap_id'])
			) {
				$gmap_id = intval(sanitize_text_field(wp_unslash($_POST['wpgmap_id'])));
			}
		}

		$gmap_data = array(
			'wpgmap_id' => intval($gmap_id),
			'wpgmap_title' => esc_html(get_post_meta($gmap_id, 'wpgmap_title', true)),
			'wpgmap_heading_class' => esc_html(get_post_meta($gmap_id, 'wpgmap_heading_class', true)),
			'wpgmap_show_heading' => esc_html(get_post_meta($gmap_id, 'wpgmap_show_heading', true)),
			'wpgmap_latlng' => esc_html(get_post_meta($gmap_id, 'wpgmap_latlng', true)),
			'wpgmap_map_zoom' => esc_html(get_post_meta($gmap_id, 'wpgmap_map_zoom', true)),
			'wpgmap_disable_zoom_scroll' => esc_html(get_post_meta($gmap_id, 'wpgmap_disable_zoom_scroll', true)),
			'wpgmap_map_width' => esc_html(get_post_meta($gmap_id, 'wpgmap_map_width', true)),
			'wpgmap_map_height' => esc_html(get_post_meta($gmap_id, 'wpgmap_map_height', true)),
			'wpgmap_map_type' => esc_html(get_post_meta($gmap_id, 'wpgmap_map_type', true)),
			'wpgmap_show_infowindow' => esc_html(get_post_meta($gmap_id, 'wpgmap_show_infowindow', true)),
			'wpgmap_enable_direction' => esc_html(get_post_meta($gmap_id, 'wpgmap_enable_direction', true)),
			'wpgmap_enable_modern_direction' => esc_html(get_post_meta($gmap_id, 'wpgmap_enable_modern_direction', true)),
			'wpgmap_direction_drawer_width' => esc_html(get_post_meta($gmap_id, 'wpgmap_direction_drawer_width', true)),
			'wgm_theme_json' => wp_kses_data(get_post_meta($gmap_id, 'wgm_theme_json', true)),
 			'wpgmap_marker_listing_style' => esc_html(get_post_meta($gmap_id, 'wpgmap_marker_listing_style', true)),
			'wpgmap_marker_listing_placement' => esc_html(get_post_meta($gmap_id, 'wpgmap_marker_listing_placement', true)),
			'wpgmap_marker_listing_width' => esc_html(get_post_meta($gmap_id, 'wpgmap_marker_listing_width', true)),
			'wgm_enable_store_locator' => esc_html(get_post_meta($gmap_id, 'wgm_enable_store_locator', true)),
			'wgm_enable_category_filter' => esc_html(get_post_meta($gmap_id, 'wgm_enable_category_filter', true)),
            // Store Locator Extended Settings
            'wgm_enable_title_search' => esc_html(get_post_meta($gmap_id, 'wgm_enable_title_search', true)),
            'wgm_title_search_placeholder' => esc_html(get_post_meta($gmap_id, 'wgm_title_search_placeholder', true)),
            'wgm_address_search_placeholder' => esc_html(get_post_meta($gmap_id, 'wgm_address_search_placeholder', true)),
            'wgm_default_address' => esc_html(get_post_meta($gmap_id, 'wgm_default_address', true)),
            'wgm_default_radius' => esc_html(get_post_meta($gmap_id, 'wgm_default_radius', true)),
            'wgm_not_found_message' => esc_html(get_post_meta($gmap_id, 'wgm_not_found_message', true)),
            
            // Advanced Store Locator retrieval
            'wgm_hide_markers_until_search' => esc_html(get_post_meta($gmap_id, 'wgm_hide_markers_until_search', true)),
            'wgm_show_center_icon' => esc_html(get_post_meta($gmap_id, 'wgm_show_center_icon', true)),
            'wgm_show_distance' => esc_html(get_post_meta($gmap_id, 'wgm_show_distance', true)),
            'wgm_distance_unit' => esc_html(get_post_meta($gmap_id, 'wgm_distance_unit', true)),
            'wgm_sort_by_distance' => esc_html(get_post_meta($gmap_id, 'wgm_sort_by_distance', true)),
            'wgm_store_locator_placement' => esc_html(get_post_meta($gmap_id, 'wgm_store_locator_placement', true)),
            // Circle Settings
            'wgm_radius_circle_stroke_color' => esc_html(get_post_meta($gmap_id, 'wgm_radius_circle_stroke_color', true)),
            'wgm_radius_circle_stroke_opacity' => esc_html(get_post_meta($gmap_id, 'wgm_radius_circle_stroke_opacity', true)),
            'wgm_radius_circle_stroke_weight' => esc_html(get_post_meta($gmap_id, 'wgm_radius_circle_stroke_weight', true)),
            'wgm_radius_circle_fill_color' => esc_html(get_post_meta($gmap_id, 'wgm_radius_circle_fill_color', true)),
            'wgm_radius_circle_fill_opacity' => esc_html(get_post_meta($gmap_id, 'wgm_radius_circle_fill_opacity', true)),

            // Marker Listing Extra Settings
            'marker_orderby_field' => esc_html(get_post_meta($gmap_id, 'marker_orderby_field', true)),
            'marker_orderby_dir' => esc_html(get_post_meta($gmap_id, 'marker_orderby_dir', true)),
			'wpgmap_center_lat_lng' => esc_html(get_center_lat_lng_by_map_id($gmap_id)),
            'wgm_enable_show_on_map_icon' => esc_html(get_post_meta($gmap_id, 'wgm_enable_show_on_map_icon', true)),
            'wgm_enable_get_direction_icon' => esc_html(get_post_meta($gmap_id, 'wgm_enable_get_direction_icon', true)),
            'wgm_enable_direction_link' => esc_html(get_post_meta($gmap_id, 'wgm_enable_direction_link', true)),
            // Map Controls Customization
            'wpgmap_zoom_control' => esc_html(get_post_meta($gmap_id, 'wpgmap_zoom_control', true)),
            'wpgmap_zoom_control_pos' => esc_html(get_post_meta($gmap_id, 'wpgmap_zoom_control_pos', true)),
            'wpgmap_map_type_control' => esc_html(get_post_meta($gmap_id, 'wpgmap_map_type_control', true)),
            'wpgmap_map_type_control_pos' => esc_html(get_post_meta($gmap_id, 'wpgmap_map_type_control_pos', true)),
            'wpgmap_street_view_control' => esc_html(get_post_meta($gmap_id, 'wpgmap_street_view_control', true)),
            'wpgmap_street_view_control_pos' => esc_html(get_post_meta($gmap_id, 'wpgmap_street_view_control_pos', true)),
            'wpgmap_fullscreen_control' => esc_html(get_post_meta($gmap_id, 'wpgmap_fullscreen_control', true)),
            'wpgmap_fullscreen_control_pos' => esc_html(get_post_meta($gmap_id, 'wpgmap_fullscreen_control_pos', true)),
            'wpgmap_rotate_control' => esc_html(get_post_meta($gmap_id, 'wpgmap_rotate_control', true)),
            'wpgmap_rotate_control_pos' => esc_html(get_post_meta($gmap_id, 'wpgmap_rotate_control_pos', true)),
            'wpgmap_scale_control' => esc_html(get_post_meta($gmap_id, 'wpgmap_scale_control', true)),
            'wpgmap_scale_control_pos' => esc_html(get_post_meta($gmap_id, 'wpgmap_scale_control_pos', true)),
		);
		$gmap_data['wgm_theme_json'] = strlen($gmap_data['wgm_theme_json']) == 0 ? '[]' : wp_kses_data($gmap_data['wgm_theme_json']);
		return wp_json_encode($gmap_data);
	}

	/**
	 * Remove map including post meta by map id
	 */
	public function remove_wpgmapembed_data()
	{
		check_ajax_referer('wpgmapembed_remove_wpgmap', 'nonce');

		if (!current_user_can($this->capability)) {
			echo wp_json_encode(
				[
					'responseCode' => 0,
					'message' => esc_html__('Unauthorized access tried.', 'gmap-embed'),
				]
			);
			wp_die();
		}

		$meta_data = [
			'wpgmap_title',
			'wpgmap_heading_class',
			'wpgmap_show_heading',
			'wpgmap_latlng',
			'wpgmap_map_zoom',
			'wpgmap_disable_zoom_scroll',
			'wpgmap_map_width',
			'wpgmap_map_height',
			'wpgmap_map_type',
			'wpgmap_show_infowindow',
			'wpgmap_enable_direction',
 			'wpgmap_enable_modern_direction',
			'wpgmap_direction_drawer_width',
			'wpgmap_marker_listing_width',
			'wgm_enable_store_locator',
 			'wgm_enable_category_filter',
 			'wgm_enable_title_search',
 			'wgm_enable_show_on_map_icon',
            'wgm_enable_get_direction_icon',
            'wgm_enable_direction_link',
            'wpgmap_zoom_control',
            'wpgmap_zoom_control_pos',
            'wpgmap_map_type_control',
            'wpgmap_map_type_control_pos',
            'wpgmap_street_view_control',
            'wpgmap_street_view_control_pos',
            'wpgmap_fullscreen_control',
            'wpgmap_fullscreen_control_pos',
            'wpgmap_rotate_control',
            'wpgmap_rotate_control_pos',
            'wpgmap_scale_control',
            'wpgmap_scale_control_pos',
		];

		$post_id = isset($_POST['post_id']) ? \intval(sanitize_text_field(wp_unslash($_POST['post_id']))) : 0;
		wp_delete_post($post_id);
		foreach ($meta_data as $field_name) {
			delete_post_meta($post_id, $field_name);
		}
		echo wp_json_encode(
			[
				'responseCode' => 1,
				'message' => esc_html__('Deleted Successfully.', 'gmap-embed'),
			]
		);
		wp_die();
	}


	/**
	 * Create new map with default map and marker data.
	 *
	 * Sanitizes and escapes all data before saving to the database.
	 * Uses wp_insert_post for map creation and $wpdb->insert for marker creation.
	 * All values are sanitized and escaped according to WordPress coding standards.
	 *
	 * @return int $map_id The ID of the newly created map.
	 */
	public function initiate_new_map($title = null)
	{
		// Set default meta data for new map
		$meta_data = array(
			'wpgmap_title' => $title ? $title : 'New Map',
			'wpgmap_heading_class' => '',
			'wpgmap_show_heading' => 0,
			'wpgmap_map_zoom' => 4,
			'wpgmap_map_width' => '100%',
			'wpgmap_map_height' => '300px',
			'wpgmap_map_type' => 'ROADMAP',
			'wpgmap_show_infowindow' => 0,
			'wpgmap_enable_direction' => 0,
			'wpgmap_enable_modern_direction' => 0,
 			'wpgmap_direction_drawer_width' => '300',
			'wpgmap_marker_listing_width' => '',
			'wpgmap_center_lat_lng' => '40.779220392557676,-87.3700530411561',
			'wpgmap_latlng' => '40.779220392557676,-87.3700530411561',
			'wgm_theme_json' => '[]',
			'wgm_enable_store_locator' => 0,
 			'wgm_enable_category_filter' => 0,
			'wgm_enable_title_search' => 0,
            'wgm_enable_show_on_map_icon' => 1,
            'wgm_enable_get_direction_icon' => 1,
            'wgm_enable_direction_link' => 0,
		);

		// Sanitize and encode theme JSON with validation
		$decoded_theme = json_decode(sanitize_textarea_field($meta_data['wgm_theme_json']));
		$meta_data['wgm_theme_json'] = (json_last_error() === JSON_ERROR_NONE && !is_null($decoded_theme)) ? wp_json_encode($decoded_theme) : '[]';

		// Prepare post array
		$post_array = array(
			'post_type' => 'wpgmapembed',
			'post_status' => 'draft',
			'post_title' => sanitize_text_field($meta_data['wpgmap_title']),
		);

		// Insert new map post
		$map_id = wp_insert_post($post_array);

		// Ensure map_id is valid
		$map_id = intval($map_id);

		// Update post meta with sanitized values
		foreach ($meta_data as $key => $value) {
			$this->wgm_update_post_meta($map_id, sanitize_key($key), sanitize_text_field($value));
		}

		// Prepare demo marker data with sanitization
		// COMMENTED OUT: Default marker creation disabled as per user request
		/*
		$map_marker_data = array(
			'map_id' => $map_id,
			'marker_name' => sanitize_text_field('Chicago'),
			'marker_desc' => wp_kses_post(''),
			'icon' => esc_url_raw('https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png'),
			'address' => sanitize_text_field(''),
			'lat_lng' => sanitize_text_field('40.779220392557676,-87.3700530411561'),
			'have_marker_link' => 0,
			'marker_link' => esc_url_raw(''),
			'marker_link_new_tab' => 0,
			'show_desc_by_default' => 1,
		);

		// Merge with marker defaults
		$defaults = $this->get_marker_default_values();
		$wp_gmap_marker_data = wp_parse_args($map_marker_data, $defaults);

		// Insert marker into custom table
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'wgm_markers',
			array_map('sanitize_text_field', $wp_gmap_marker_data)
		);
		*/

		return $map_id;
	}

	/**
	 * Get all maps
	 *
	 * @param array $map_ids
	 * @return array
	 */
	function get_all_maps($map_ids)
	{
		$maps = [];
		if (empty($map_ids) || !is_array($map_ids)) {
			return $maps;
		}
		foreach ($map_ids as $map_id) {
			$map_id = intval($map_id);
			$map = $this->get_map_by_id($map_id);
			if ($map) {
				$maps[] = $map;
			}
		}
		return $maps;
	}

	/**
	 * Get a map by its ID with all associated map data fields from post meta.
	 *
	 * @param int $map_id
	 * @return array|null
	 */
	public function get_map_by_id($map_id)
	{
		$map_id = intval($map_id);
		$post = get_post($map_id);
		if (!$post || $post->post_type !== 'wpgmapembed') {
			return null;
		}
		
		$map = [
			'id' => $post->ID,
			'wpgmap_title' => $post->post_title,
		];

		// Exhaustively get all post meta
		$all_meta = get_post_custom($post->ID);
		if ($all_meta && is_array($all_meta)) {
			foreach ($all_meta as $key => $values) {
				// We only care about map-specific keys to keep the export clean
				// Usually start with wpgmap_, wgm_, or specific ordering keys
				if (
					strpos($key, 'wpgmap_') === 0 || 
					strpos($key, 'wgm_') === 0 || 
					strpos($key, 'marker_orderby_') === 0
				) {
					$map[$key] = maybe_unserialize($values[0]);
				}
			}
		}

		return $map;
	}

	/**
	 * Clone an existing map (all its settings and markers) into a new draft map.
	 * Pro feature — availability is also enforced server-side here in addition
	 * to the client-side lock, since this is invoked directly over AJAX.
	 *
	 * @since 1.9.7
	 */
	public function clone_wpgmapembed_data()
	{
		if (!_wgm_is_premium()) {
			wp_send_json_error(array('message' => esc_html__('Cloning maps is a Premium feature. Please upgrade to unlock it.', 'gmap-embed')), 403);
		}

		$source_map_id = isset($_POST['map_id']) ? intval(sanitize_text_field(wp_unslash($_POST['map_id']))) : 0;
		if ($source_map_id <= 0) {
			wp_send_json_error(array('message' => esc_html__('Invalid map ID.', 'gmap-embed')));
		}

		$source_map = $this->get_map_by_id($source_map_id);
		if (!$source_map) {
			wp_send_json_error(array('message' => esc_html__('Source map not found.', 'gmap-embed')));
		}

		$original_title = isset($source_map['wpgmap_title']) ? $source_map['wpgmap_title'] : '';
		// translators: %s: original map title.
		$new_title = sprintf(__('%s (Copy)', 'gmap-embed'), $original_title);

		$new_map_id = wp_insert_post(
			array(
				'post_type' => 'wpgmapembed',
			)
		);

		if (is_wp_error($new_map_id) || !$new_map_id) {
			wp_send_json_error(array('message' => esc_html__('Failed to create the cloned map.', 'gmap-embed')));
		}

		// Copy all map settings (post meta) from the source map.
		foreach ($source_map as $key => $value) {
			if ($key === 'id') {
				continue;
			}
			$value = ($key === 'wpgmap_title') ? $new_title : $value;
			$this->wgm_update_post_meta($new_map_id, $key, $value);
		}

		// Copy this map's markers to the new map.
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$source_markers = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wgm_markers WHERE map_id = %d", $source_map_id), ARRAY_A);
		$marker_defaults = $this->get_marker_default_values();

		if (!empty($source_markers) && is_array($source_markers)) {
			foreach ($source_markers as $marker) {
				unset($marker['id']);
				// A NULL text field (e.g. from a legacy/imported row) would otherwise be
				// carried into the clone and break the marker-edit form (which expects
				// strings, not null, for these inputs).
				foreach (array('marker_desc', 'marker_image', 'address', 'marker_link', 'animation') as $text_field) {
					if (!isset($marker[$text_field]) || is_null($marker[$text_field])) {
						$marker[$text_field] = '';
					}
				}
				$marker['map_id'] = $new_map_id;
				$marker['created_at'] = current_time('mysql');
				$marker['updated_at'] = current_time('mysql');
				$marker['created_by'] = get_current_user_id();
				$marker['updated_by'] = get_current_user_id();

				$new_marker_data = wp_parse_args($marker, $marker_defaults);
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->insert("{$wpdb->prefix}wgm_markers", $new_marker_data);
			}
		}

		wp_send_json_success(
			array(
				'new_id' => intval($new_map_id),
				'message' => esc_html__('Map cloned successfully.', 'gmap-embed'),
			)
		);
	}

}
