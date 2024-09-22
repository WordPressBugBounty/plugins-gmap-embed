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
		if (!current_user_can($this->capability)) {
			echo wp_json_encode(
				array(
					'responseCode' => 0,
					'message'      => 'Unauthorized access tried.',
				)
			);
			wp_die();
		}
		if (!isset($_GET['ajax_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['ajax_nonce'])), 'ajax_nonce')) {
			die('Busted!');
		}
		$args = array(
			'post_type'      => 'wpgmapembed',
			'posts_per_page' => -1,
			'post_status'    => 'draft',
		);

		$return_json = array();
		$maps_list   = new WP_Query($args);
		while ($maps_list->have_posts()) {
			$maps_list->the_post();
			$title         = esc_html(get_post_meta(get_the_ID(), 'wpgmap_title', true));
			$type          = esc_html(get_post_meta(get_the_ID(), 'wpgmap_map_type', true));
			$width         = esc_html(get_post_meta(get_the_ID(), 'wpgmap_map_width', true));
			$height        = esc_html(get_post_meta(get_the_ID(), 'wpgmap_map_height', true));
			$shortcode     = '<input class="wpgmap-shortcode regular-text" style="width:100%!important;" type="text" value="' . esc_attr('[gmap-embed id=&quot;' . get_the_ID() . '&quot;]') . '"
                                                       onclick="this.select()"/>';
			$action        = '<button class="button media-button button-primary button-small wpgmap-copy-to-clipboard" data-id="' . get_the_ID() . '" style="margin-right: 5px;"><i class="fas fa-copy"></i></button>'
				. '<a href="?page=wpgmapembed&tag=edit&id=' . get_the_ID() . '" class="button media-button button-primary button-small wpgmap-edit" data-id="' . get_the_ID() . '"><i class="fas fa-edit"></i>
                                                ' . __('Edit', 'gmap-embed') . '
                                            </a>&nbsp;<span type="button"
                                                    class="button media-button button-small  wgm_wpgmap_delete" data-id="' . get_the_ID() . '" style="background-color: #aa2828;color: white;opacity:0.7;"><i class="fas fa-trash"></i> Delete
                                            </span>';
			$row           = array(
				'id'        => get_the_ID(),
				'title'     => $title,
				'map_type'  => $type,
				'width'     => $width,
				'height'    => $height,
				'shortcode' => $shortcode,
				'action'    => $action,
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
		if (!current_user_can($this->capability)) {
			echo wp_json_encode(
				array(
					'responseCode' => 0,
					'message'      => 'Unauthorized access tried.',
				)
			);
			wp_die();
		}
		if (!isset($_POST['c_s_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['c_s_nonce'])), 'c_s_nonce')) {
			die('Busted!');
		}
		$error = '';
		// Getting ajax fileds value
		$meta_data   = array(
			'wpgmap_title'               => sanitize_text_field(wp_strip_all_tags(wp_unslash($_POST['map_data']['wpgmap_title']))),
			'wpgmap_heading_class'       => sanitize_html_class(wp_unslash($_POST['map_data']['wpgmap_heading_class'])),
			'wpgmap_show_heading'        => sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_show_heading'])),
			// current marker lat lng
			'wpgmap_latlng'              => sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_latlng'])),
			'wpgmap_map_zoom'            => sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_map_zoom'])),
			'wpgmap_disable_zoom_scroll' => sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_disable_zoom_scroll'])),
			'wpgmap_map_width'           => sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_map_width'])),
			'wpgmap_map_height'          => sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_map_height'])),
			'wpgmap_map_type'            => sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_map_type'])),
			'wpgmap_show_infowindow'     => sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_show_infowindow'])),
			'wpgmap_enable_direction'    => sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_enable_direction'])),
			// map center lat lng
			'wpgmap_center_lat_lng'      => sanitize_text_field(wp_unslash($_POST['map_data']['wpgmap_center_lat_lng'])),
			'wgm_theme_json'           => sanitize_textarea_field(wp_unslash($_POST['map_data']['wgm_theme_json']))
		);
		$meta_data['wgm_theme_json'] = json_encode(json_decode(sanitize_textarea_field(wp_unslash($meta_data['wgm_theme_json']))));
		$action_type = sanitize_text_field(wp_unslash($_POST['map_data']['action_type']));
		if ($meta_data['wpgmap_latlng'] === '') {
			$error = 'Please input Latitude and Longitude';
		}
		if (strlen($error) > 0) {
			echo wp_json_encode(
				array(
					'responseCode' => 0,
					'message'      => $error,
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
			$post_id    = wp_insert_post($post_array);
		} elseif ($action_type === 'update') {
			$post_id = intval(sanitize_text_field(wp_unslash($_POST['map_data']['post_id'])));
		}

		// Updating post meta
		foreach ($meta_data as $key => $value) {
			$this->wgm_update_post_meta($post_id, $key, $value);
		}
		$return_array = array(
			'responseCode' => 1,
			'post_id'      => intval($post_id),
		);
		if ($action_type === 'save') {
			global $wpdb;
			$wpdb->update(
				$wpdb->prefix . 'wgm_markers',
				array('map_id' => intval($post_id)),
				array('map_id' => 0),
				array('%d'),
				array('%d')
			);
			$return_array['message'] = 'Map created Successfully.';
		} elseif ($action_type === 'update') {
			$return_array['message'] = 'Map updated Successfully.';
		}
		echo wp_json_encode($return_array);
		wp_die();
	}

	/**
	 * Classic editor: Loading popup content on WP Google Map click
	 */
	public function load_popup_wpgmapembed_list()
	{
		if (!current_user_can($this->capability)) {
			echo wp_json_encode(
				array(
					'responseCode' => 0,
					'message'      => 'Unauthorized access tried.',
				)
			);
			wp_die();
		}
		if (!isset($_POST['data']['c_s_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['data']['c_s_nonce'])), 'c_s_nonce')) {
			die('Busted!');
		}
		$content   = '';
		$args      = array(
			'post_type'      => 'wpgmapembed',
			'posts_per_page' => -1,
			'post_status'    => 'draft',
		);
		$maps_list = new WP_Query($args);

		while ($maps_list->have_posts()) {
			$maps_list->the_post();
			$title   = get_post_meta(get_the_ID(), 'wpgmap_title', true);
			$content .= '<div class="wp-gmap-single">
                                        <div class="wp-gmap-single-left">
                                            <div class="wp-gmap-single-title">
                                                ' . esc_html($title) . '
                                            </div>
                                            <div class="wp-gmap-single-shortcode">
                                                <input class="wpgmap-shortcode regular-text" type="text" value="[gmap-embed id=&quot;' . get_the_ID() . '&quot;]"
                                                       onclick="this.select()"/>
                                            </div>
                                        </div>
                                        <div class="wp-gmap-single-action">
                                            <button type="button"
                                                    class="button media-button button-primary button-large wpgmap-insert-shortcode">
                                                Insert
                                            </button>                                            
                                        </div>
                                    </div>';
		}
		$allowed_html = [
			'a'      => [],
			'br'     => [],
			'em'     => [],
			'strong' => [],
			'div'    => [
				'class' => []
			],
			'button' => [
				'type'  => [],
				'class' => []
			],
			'input'  => [
				'class'   => [],
				'value'   => [],
				'name'    => [],
				'onclick' => [],
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
			$gmap_id = intval(sanitize_text_field(wp_unslash($_POST['wpgmap_id'])));
		}

		$gmap_data = array(
			'wpgmap_id'                  => intval($gmap_id),
			'wpgmap_title'               => esc_html(get_post_meta($gmap_id, 'wpgmap_title', true)),
			'wpgmap_heading_class'       => esc_html(get_post_meta($gmap_id, 'wpgmap_heading_class', true)),
			'wpgmap_show_heading'        => esc_html(get_post_meta($gmap_id, 'wpgmap_show_heading', true)),
			'wpgmap_latlng'              => esc_html(get_post_meta($gmap_id, 'wpgmap_latlng', true)),
			'wpgmap_map_zoom'            => esc_html(get_post_meta($gmap_id, 'wpgmap_map_zoom', true)),
			'wpgmap_disable_zoom_scroll' => esc_html(get_post_meta($gmap_id, 'wpgmap_disable_zoom_scroll', true)),
			'wpgmap_map_width'           => esc_html(get_post_meta($gmap_id, 'wpgmap_map_width', true)),
			'wpgmap_map_height'          => esc_html(get_post_meta($gmap_id, 'wpgmap_map_height', true)),
			'wpgmap_map_type'            => esc_html(get_post_meta($gmap_id, 'wpgmap_map_type', true)),
			'wpgmap_show_infowindow'     => esc_html(get_post_meta($gmap_id, 'wpgmap_show_infowindow', true)),
			'wpgmap_enable_direction'    => esc_html(get_post_meta($gmap_id, 'wpgmap_enable_direction', true)),
			'wgm_theme_json'             => wp_kses_data(get_post_meta($gmap_id, 'wgm_theme_json', true)),
			'wpgmap_center_lat_lng'      => esc_html(get_center_lat_lng_by_map_id($gmap_id)),
		);
		$gmap_data['wgm_theme_json'] = strlen($gmap_data['wgm_theme_json']) == 0 ? '[]' : wp_kses_data($gmap_data['wgm_theme_json']);
		return wp_json_encode($gmap_data);
	}

	/**
	 * Remove map including post meta by map id
	 */
	public function remove_wpgmapembed_data()
	{
		if (!current_user_can($this->capability)) {
			$return_array = array(
				'responseCode' => 0,
				'message'      => 'Unauthorized access tried.',
			);
			echo wp_json_encode($return_array);
			wp_die();
		}
		if (!isset($_POST['c_s_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['c_s_nonce'])), 'c_s_nonce')) {
			die('Busted!');
		}
		$meta_data = array(
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
		);

		$post_id = intval(sanitize_text_field(wp_unslash($_POST['post_id'])));
		wp_delete_post($post_id);
		foreach ($meta_data as $field_name => $value) {
			delete_post_meta($post_id, $field_name, $value);
		}
		$return_array = array(
			'responseCode' => 1,
			'message'      => 'Deleted Successfully.',
		);
		echo wp_json_encode($return_array);
		wp_die();
	}
}
