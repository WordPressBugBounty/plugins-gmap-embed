<?php

namespace WGMSRM\Traits;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Trait MarkerCRUD: Map CRUD operation doing here
 */
trait MarkerCRUD
{


	/**
	 * Get Marker default values
	 *
	 * @return array
	 */
	public function get_marker_default_values()
	{
		return array(
			'map_id' => 0,
			'marker_name' => null,
			'marker_desc' => null,
			'marker_image' => null,
			'icon' => null,
			'address' => null,
			'lat_lng' => null,
			'have_marker_link' => 0,
			'marker_link' => null,
			'marker_link_new_tab' => 0,
			'animation' => null,
			'category_id' => 0,
			'show_desc_by_default' => 0,
			'created_at' => current_time('mysql'),
			'created_by' => get_current_user_id(),
			'updated_at' => current_time('mysql'),
			'updated_by' => get_current_user_id(),
		);
	}

	/**
	 * To save new map marker
	 */
	public function save_map_marker()
	{

		global $wpdb;


		// Ensure POSTed marker data is present and properly unslashed; field-level sanitization is applied later.
		$data = [];
		if (isset($_POST['map_markers_data']) && is_array($_POST['map_markers_data'])) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- field-level sanitization is done below
			$raw_data = $_POST['map_markers_data'];
			$data = is_array($raw_data) ? wp_unslash($raw_data) : [];
		}

		$map_id = isset($data['wpgmap_map_id']) ? \intval(sanitize_text_field(wp_unslash($data['wpgmap_map_id']))) : 0;
		$error = '';
		$map_marker_data = [
			'map_id' => $map_id,
			'marker_name' => isset($data['wpgmap_marker_name']) && \strlen(sanitize_text_field(wp_unslash($data['wpgmap_marker_name']))) === 0 ? null : (isset($data['wpgmap_marker_name']) ? sanitize_text_field(wp_unslash($data['wpgmap_marker_name'])) : null),
			'marker_desc' => isset($data['wpgmap_marker_desc']) ? wp_kses_post($data['wpgmap_marker_desc']) : '',
			'marker_image' => isset($data['wpgmap_marker_image']) ? esc_url_raw(wp_unslash($data['wpgmap_marker_image'])) : '',
			'icon' => isset($data['wpgmap_marker_icon']) ? esc_url_raw(wp_unslash($data['wpgmap_marker_icon'])) : '',
			'address' => isset($data['wpgmap_marker_address']) ? sanitize_text_field(wp_unslash($data['wpgmap_marker_address'])) : '',
			'lat_lng' => isset($data['wpgmap_marker_lat_lng']) ? sanitize_text_field(wp_unslash($data['wpgmap_marker_lat_lng'])) : '',
			'have_marker_link' => isset($data['wpgmap_have_marker_link']) ? \intval($data['wpgmap_have_marker_link']) : 0,
			'marker_link' => isset($data['wpgmap_marker_link']) ? esc_url_raw(wp_unslash($data['wpgmap_marker_link'])) : '',
			'marker_link_new_tab' => isset($data['wpgmap_marker_link_new_tab']) ? \intval($data['wpgmap_marker_link_new_tab']) : 0,
			'animation' => isset($data['wpgmap_marker_animation']) ? sanitize_text_field(wp_unslash($data['wpgmap_marker_animation'])) : '',
			'category_id' => isset($data['wpgmap_marker_category']) ? (is_array($data['wpgmap_marker_category']) ? implode(',', array_map('intval', $data['wpgmap_marker_category'])) : \intval($data['wpgmap_marker_category'])) : '0',
			'show_desc_by_default' => isset($data['wpgmap_marker_infowindow_show']) ? \intval($data['wpgmap_marker_infowindow_show']) : 0,
		];
		if (empty($map_marker_data['lat_lng'])) {
			$error = esc_html__('Please input Latitude and Longitude', 'gmap-embed');
		}
		if (\strlen($error) > 0) {
			echo wp_json_encode(
				[
					'responseCode' => 0,
					'message' => $error,
				]
			);
			wp_die();
		}

		if (!_wgm_is_premium()) {
			$no_of_marker_already_have = $this->get_no_of_markers_by_map_id(\intval($map_id));
			if ($no_of_marker_already_have > 0) {
				echo wp_json_encode(
					[
						'responseCode' => 0,
						'message' => esc_html__('Please upgrade to premium version to create unlimited markers', 'gmap-embed'),
					]
				);
				wp_die();
			}
		}

		$defaults = $this->get_marker_default_values();
		$wp_gmap_marker_data = wp_parse_args($map_marker_data, $defaults);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->insert(
			"{$wpdb->prefix}wgm_markers",
			$wp_gmap_marker_data,
			[
				'%d', // map_id
				'%s', // marker_name
				'%s', // marker_desc
				'%s', // marker_image
				'%s', // icon
				'%s', // address
				'%s', // lat_lng
				'%d', // have_marker_link
				'%s', // marker_link
				'%d', // marker_link_new_tab
				'%s', // animation
				'%s', // category_id
				'%d', // show_desc_by_default
				'%s', // created_at
				'%d', // created_by
				'%s', // updated_at
				'%d', // updated_by
			]
		); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

		$return_array = [
			'responseCode' => 1,
			'marker_id' => \intval($wpdb->insert_id),
		];
		$return_array['message'] = esc_html__('Marker Saved Successfully.', 'gmap-embed');
		echo wp_json_encode($return_array);
		wp_die();
	}

	/**
	 * To update existing marker information
	 */

	public function update_map_marker()
	{

		global $wpdb;


		$error = '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized	
		$data = isset($_POST['map_markers_data']) && is_array($_POST['map_markers_data']) ? wp_unslash($_POST['map_markers_data']) : [];
		$marker_id = isset($data['wpgmap_marker_id']) ? intval(sanitize_text_field(wp_unslash($data['wpgmap_marker_id']))) : 0;
		$map_id = isset($data['wpgmap_map_id']) ? intval(sanitize_text_field(wp_unslash($data['wpgmap_map_id']))) : 0;
		$map_marker_data = array(
			'map_id' => $map_id,
			'marker_name' => isset($data['wpgmap_marker_name']) && strlen(sanitize_text_field(wp_unslash($data['wpgmap_marker_name']))) === 0 ? null : (isset($data['wpgmap_marker_name']) ? sanitize_text_field(wp_unslash($data['wpgmap_marker_name'])) : null),
			'marker_desc' => isset($data['wpgmap_marker_desc']) ? wp_kses_post($data['wpgmap_marker_desc']) : '',
			'marker_image' => isset($data['wpgmap_marker_image']) ? esc_url_raw(wp_unslash($data['wpgmap_marker_image'])) : '',
			'icon' => isset($data['wpgmap_marker_icon']) ? esc_url_raw(wp_unslash($data['wpgmap_marker_icon'])) : '',
			'address' => isset($data['wpgmap_marker_address']) ? sanitize_text_field(wp_unslash($data['wpgmap_marker_address'])) : '',
			'lat_lng' => isset($data['wpgmap_marker_lat_lng']) ? sanitize_text_field(wp_unslash($data['wpgmap_marker_lat_lng'])) : '',
			'have_marker_link' => isset($data['wpgmap_have_marker_link']) ? intval($data['wpgmap_have_marker_link']) : 0,
			'marker_link' => isset($data['wpgmap_marker_link']) ? esc_url_raw(wp_unslash($data['wpgmap_marker_link'])) : '',
			'marker_link_new_tab' => isset($data['wpgmap_marker_link_new_tab']) ? intval($data['wpgmap_marker_link_new_tab']) : 0,
			'animation' => isset($data['wpgmap_marker_animation']) ? sanitize_text_field(wp_unslash($data['wpgmap_marker_animation'])) : '',
			'category_id' => isset($data['wpgmap_marker_category']) ? (is_array($data['wpgmap_marker_category']) ? implode(',', array_map('intval', $data['wpgmap_marker_category'])) : intval($data['wpgmap_marker_category'])) : '0',
			'show_desc_by_default' => isset($data['wpgmap_marker_infowindow_show']) ? intval($data['wpgmap_marker_infowindow_show']) : 0,
		);
		if (empty($map_marker_data['lat_lng'])) {
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

		$defaults = $this->get_marker_default_values();
		$wp_gmap_marker_data = wp_parse_args($map_marker_data, $defaults);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$wpdb->prefix . 'wgm_markers',
			$wp_gmap_marker_data,
			array('id' => intval($marker_id)),
			array(
				'%d', // map_id
				'%s', // marker_name
				'%s', // marker_desc
				'%s', // marker_image
				'%s', // icon
				'%s', // address
				'%s', // lat_lng
				'%d', // have_marker_link
				'%s', // marker_link
				'%d', // marker_link_new_tab
				'%s', // animation
				'%s', // category_id
				'%d', // show_desc_by_default
				'%s', // created_at
				'%d', // created_by
				'%s', // updated_at
				'%d', // updated_by
			),
			array('%d')
		); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching

		$return_array = array(
			'responseCode' => 1,
			'marker_id' => intval($marker_id),
		);
		$return_array['message'] = esc_html__('Updated Successfully.', 'gmap-embed');
		echo wp_json_encode($return_array);
		wp_die();
	}

	/**
	 * Get all marker icons/pins
	 */
	public function get_marker_icons()
	{
		// Nonce verification


		ob_start();
		require_once WGM_PLUGIN_PATH . 'admin/includes/markers-icons.php';
		$output = ob_get_clean();
		// Allow necessary HTML for the icon selector with search functionality
		$allowed_html = array(
			'style' => array(),
			'ul' => array(
				'class' => array(),
				'id' => array(),
			),
			'li' => array(
				'class' => array(),
				'id' => array(),
				'style' => array(),
				'data-*' => array(),
				'data-icon-name' => array(),
			),
			'img' => array(
				'src' => array(),
				'alt' => array(),
				'class' => array(),
				'style' => array(),
				'onclick' => array(),
				'width' => array(),
				'height' => array(),
				'id' => array(),
				'title' => array(),
				'data-*' => array(),
			),
			'div' => array(
				'class' => array(),
				'id' => array(),
				'style' => array(),
			),
			'input' => array(
				'type' => array(),
				'id' => array(),
				'class' => array(),
				'placeholder' => array(),
				'autocomplete' => array(),
				'aria-label' => array(),
				'value' => array(),
			),
			'button' => array(
				'type' => array(),
				'class' => array(),
				'id' => array(),
				'aria-label' => array(),
			),
			'span' => array(
				'class' => array(),
				'id' => array(),
				'style' => array(),
			),
			'a' => array(
				'href' => array(),
				'class' => array(),
				'id' => array(),
				'style' => array(),
				'target' => array(),
				'rel' => array(),
			),
		);
		echo wp_kses($output, $allowed_html);
		wp_die();
	}

	/**
	 * Save Marker Icon
	 */
	public function save_marker_icon()
	{

		global $wpdb;


		$error = '';
		$icon_url = isset($_POST['data']['icon_url']) ? esc_url_raw(wp_unslash($_POST['data']['icon_url'])) : '';
		$map_icon_data = array(
			'type' => 'uploaded_marker_icon',
			'title' => '',
			'desc' => '',
			'file_name' => $icon_url,
		);

		$is_marker_icon_already_exist = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}wgm_icons WHERE file_name=%s", $icon_url)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		if ($is_marker_icon_already_exist == 0) {
			$defaults = array(
				'file_name' => '',
			);
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wp_gmap_marker_icon = wp_parse_args($map_icon_data, $defaults);
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->insert(
				$wpdb->prefix . 'wgm_icons',
				$wp_gmap_marker_icon,
				array(
					'%s',
					'%s',
					'%s',
					'%s',
				)
			); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		$return_array = array(
			'responseCode' => 1,
			'icon_url' => esc_url($icon_url),
		);
		$return_array['message'] = esc_html__('Updated Successfully.', 'gmap-embed');
		echo wp_json_encode($return_array);
		wp_die();
	}

	/**
	 * Get no of markers by map id
	 *
	 * @param $map_id int
	 *
	 * @retun int
	 */
	public function get_no_of_markers_by_map_id($map_id = 0)
	{
		global $wpdb;
		$map_id = intval($map_id);
		return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}wgm_markers WHERE map_id=%d", $map_id)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Get all markers by map id
	 */
	public function get_markers_by_map_id()
	{

		global $wpdb;


		$map_id = isset($_POST['data']['map_id']) ? intval(sanitize_text_field(wp_unslash($_POST['data']['map_id']))) : 0;
		$filtered_map_markers = array();

        $orderby_field = get_post_meta($map_id, 'marker_orderby_field', true);
        $orderby_dir = get_post_meta($map_id, 'marker_orderby_dir', true);

        // Sanitize field name - only allow specific fields
        $allowed_fields = ['id', 'marker_name', 'address', 'marker_desc', 'created_at', 'updated_at', 'lat_lng'];
        if (!in_array($orderby_field, $allowed_fields)) {
            $orderby_field = 'id';
        }
        // Sanitize direction
        $orderby_dir = (strtoupper($orderby_dir) === 'DESC') ? 'DESC' : 'ASC';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- ORDER BY is whitelisted above.
		$map_markers = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wgm_markers WHERE map_id=%d ORDER BY {$orderby_field} {$orderby_dir}", $map_id));
		if (count($map_markers) > 0) {
			foreach ($map_markers as $key => $map_marker) {
				$filtered_map_markers[$key] = $map_marker;
			}
		}
		$return_array = array(
			'responseCode' => 1,
			'markers' => $filtered_map_markers,
		);
		$return_array['message'] = esc_html__('Markers fetched successfully.', 'gmap-embed');
		echo wp_json_encode($return_array);
		wp_die();
	}

	/**
	 * Public Get all markers by map id
	 */
	public function p_get_markers_by_map_id()
	{
		global $wpdb;

		$map_id = isset($_POST['data']['map_id']) ? intval(sanitize_text_field(wp_unslash($_POST['data']['map_id']))) : 0;
		$nonce  = isset($_POST['_wgm_p_nonce']) ? sanitize_text_field(wp_unslash($_POST['_wgm_p_nonce'])) : '';

		/**
		 * Technical Solution for Cache Plugins (e.g. LiteSpeed):
		 * 
		 * Nonces are incompatible with heavy caching because they expire while the page remains cached.
		 * For "Read" actions like fetching markers, we allow the request if:
		 * 1. A valid nonce is provided.
		 * 2. OR the Map ID corresponds to a valid 'wpgmapembed' post.
		 */
		$is_valid_nonce = !empty($nonce) && wp_verify_nonce($nonce, 'wgm_marker_render');
		$is_valid_map   = ($map_id > 0 && get_post_type($map_id) === 'wpgmapembed');

		if (!$is_valid_nonce && !$is_valid_map) {
			$return_array = array(
				'responseCode' => 0,
				'message'      => esc_html__('Invalid request or Map ID.', 'gmap-embed'),
			);
			echo wp_json_encode($return_array);
			wp_die();
		}

		$filtered_map_markers = array();

        $orderby_field = get_post_meta($map_id, 'marker_orderby_field', true);
        $orderby_dir = get_post_meta($map_id, 'marker_orderby_dir', true);

        // Sanitize field name - only allow specific fields
        $allowed_fields = ['id', 'marker_name', 'address', 'marker_desc', 'created_at', 'updated_at', 'lat_lng'];
        if (!in_array($orderby_field, $allowed_fields)) {
            $orderby_field = 'id';
        }
        // Sanitize direction
        $orderby_dir = (strtoupper($orderby_dir) === 'DESC') ? 'DESC' : 'ASC';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- ORDER BY is whitelisted above.
		$map_markers = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wgm_markers WHERE map_id=%d ORDER BY {$orderby_field} {$orderby_dir}", $map_id));
		if (count($map_markers) > 0) {
			foreach ($map_markers as $key => $map_marker) {
				$filtered_map_markers[$key] = $map_marker;
			}
		}
		$return_array = array(
			'responseCode' => 1,
			'markers' => $filtered_map_markers,
		);
		$return_array['message'] = esc_html__('Markers fetched successfully.', 'gmap-embed');
		echo wp_json_encode($return_array);
		wp_die();
	}

	/**
	 * Get markers by map id for datatable
	 */
	public function wgm_get_markers_by_map_id_for_dt()
	{


		$map_id = isset($_GET['map_id']) ? intval(sanitize_text_field(wp_unslash($_GET['map_id']))) : 0;

		$return_json = array();
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query is required for custom table.
		$wpgmap_markers = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wgm_markers WHERE map_id=%d", $map_id)); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		if (count($wpgmap_markers) > 0) {
			foreach ($wpgmap_markers as $marker_key => $wpgmap_marker) {
				$clone_btn = _wgm_is_premium()
					? '<a href="" class="wpgmap_marker_clone button button-small" map_marker_id="' . esc_attr($wpgmap_marker->id) . '" title="' . esc_attr__('Clone Marker', 'gmap-embed') . '"><i class="fas fa-copy"></i></a>'
					: '<a href="" class="wgm_enable_premium button button-small" style="opacity: 0.5;" title="' . esc_attr__('Clone Marker (Premium)', 'gmap-embed') . '" data-notice="' . esc_attr(sprintf(__('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to <b>Clone Markers</b>.', 'gmap-embed'), esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=marker-list-clone-lock'))) . '"><i class="fas fa-copy"></i></a>';
				$action = '<a href="" class="wpgmap_marker_edit button button-small"
                           map_marker_id="' . esc_attr($wpgmap_marker->id) . '" title="' . esc_attr__('Edit Marker', 'gmap-embed') . '"><i class="fas fa-edit"></i></a>
                        <a href="" class="wpgmap_marker_view button button-small"
                           map_marker_id="' . esc_attr($wpgmap_marker->id) . '" title="' . esc_attr__('View on Map', 'gmap-embed') . '"><i class="fas fa-eye"></i></a>
                        ' . $clone_btn . '
                        <a href="" class="wpgmap_marker_trash button button-small"
                           map_marker_id="' . esc_attr($wpgmap_marker->id) . '" title="' . esc_attr__('Delete Marker', 'gmap-embed') . '"><i class="fas fa-trash"></i></a>';
				$row = array(
					'id' => intval($wpgmap_marker->id),
					'marker_name' => esc_html($wpgmap_marker->marker_name),
					//phpscs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
					'icon' => '<img src="' . esc_url($wpgmap_marker->icon) . '" width="20">',
					'action' => $action,
				);
				$return_json[] = $row;
			}
		}
		echo wp_json_encode(array('data' => $return_json));
		wp_die();
	}

	/**
	 * Delete single marker
	 */
	public function delete_marker()
	{

		global $wpdb;


		$marker_id = isset($_POST['data']['marker_id']) ? intval(sanitize_text_field(wp_unslash($_POST['data']['marker_id']))) : 0;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete(
			$wpdb->prefix . 'wgm_markers',
			array(
				'id' => $marker_id,
			),
			array(
				'%d',
			)
		); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Get marker single data by marker ID
	 */
	public function get_marker_data_by_marker_id()
	{

		global $wpdb;


		$marker_id = 0;
		if (isset($_POST['data']['marker_id'])) {
			$marker_id = intval(sanitize_text_field(wp_unslash($_POST['data']['marker_id'])));
		}
		$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wgm_markers WHERE id=%d", intval($marker_id)), OBJECT); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		if ($result) {
		}
		echo wp_json_encode($result);
		wp_die();
	}

	function get_marker_data_by_map_id($map_id)
	{
		global $wpdb;
		$map_id = intval($map_id);
		$map_id = intval($map_id);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$markers = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, map_id, marker_name, marker_desc, icon, address, lat_lng,
				have_marker_link, marker_link, marker_link_new_tab, animation, category_id, show_desc_by_default
				FROM {$wpdb->prefix}wgm_markers WHERE map_id = %d",
				$map_id
			),
			ARRAY_A
		);
		return $markers;
	}

	/**
	 * Clone a single marker. Pro feature — availability is also enforced
	 * server-side here in addition to the client-side lock, since this is
	 * invoked directly over AJAX.
	 *
	 * @since 1.9.7
	 */
	public function clone_map_marker()
	{
		if (!_wgm_is_premium()) {
			wp_send_json_error(array('message' => esc_html__('Cloning markers is a Premium feature. Please upgrade to unlock it.', 'gmap-embed')), 403);
		}

		$marker_id = isset($_POST['marker_id']) ? intval(sanitize_text_field(wp_unslash($_POST['marker_id']))) : 0;
		if ($marker_id <= 0) {
			wp_send_json_error(array('message' => esc_html__('Invalid marker ID.', 'gmap-embed')));
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$marker = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wgm_markers WHERE id = %d", $marker_id), ARRAY_A);
		if (!$marker) {
			wp_send_json_error(array('message' => esc_html__('Marker not found.', 'gmap-embed')));
		}

		unset($marker['id']);
		// A NULL text field (e.g. from a legacy/imported row) would otherwise be
		// carried into the clone and break the marker-edit form (which expects
		// strings, not null, for these inputs).
		foreach (array('marker_desc', 'marker_image', 'address', 'marker_link', 'animation') as $text_field) {
			if (!isset($marker[$text_field]) || is_null($marker[$text_field])) {
				$marker[$text_field] = '';
			}
		}
		$marker['marker_name'] = !empty($marker['marker_name'])
			// translators: %s: original marker name.
			? sprintf(__('%s (Copy)', 'gmap-embed'), $marker['marker_name'])
			: esc_html__('Marker (Copy)', 'gmap-embed');
		$marker['created_at'] = current_time('mysql');
		$marker['updated_at'] = current_time('mysql');
		$marker['created_by'] = get_current_user_id();
		$marker['updated_by'] = get_current_user_id();

		$new_marker_data = wp_parse_args($marker, $this->get_marker_default_values());
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$inserted = $wpdb->insert("{$wpdb->prefix}wgm_markers", $new_marker_data);

		if (!$inserted) {
			wp_send_json_error(array('message' => esc_html__('Failed to clone marker.', 'gmap-embed')));
		}

		$new_marker_id = intval($wpdb->insert_id);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$new_marker = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}wgm_markers WHERE id = %d", $new_marker_id));

		wp_send_json_success(
			array(
				'marker_id' => $new_marker_id,
				'marker' => $new_marker,
				'message' => esc_html__('Marker cloned successfully.', 'gmap-embed'),
			)
		);
	}
}
