<?php

namespace WGMSRM\Traits;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Trait AssetHandler: enqueue, dequeue assets
 */
trait AssetHandler
{


	/**
	 * @return array
	 */
	public function getLocalizedScripts()
	{
		// Setup wizard validation data
		$wpgmap_setup_validation_data = array(
			'apikey' => __('Please enter a valid API key', 'gmap-embed'),
			'language' => __('Please select a language', 'gmap-embed'),
			'regionalarea' => __('Please select a regional area', 'gmap-embed'),
			'licencekey' => __('Invalid license key', 'gmap-embed'),
			'success' => __('Successfully installed.', 'gmap-embed'),
			'failed_to_finish' => __('Something went wrong, please reload and try again', 'gmap-embed'),
		);

		// Setup wizard get api key modal content
		$wpgmap_setup_api_key_modal_data = array(
			'faq_title' => __('Help Manual', 'gmap-embed'),
			'faq_item_title' => __(' Click here to see Help Manual on how to get API key', 'gmap-embed'),
			'faq_item_url' => esc_url('https://wpgooglemap.com/documentation/wp-google-map-quick-installation?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=documentation&utm_content=setup-wizard-faq-link'),
			'video_title' => __('Video Tutorial', 'gmap-embed'),
			'video_url' => esc_url('//www.youtube.com/embed/1G2VksP-uX0'),
			'api_key_title' => __('Get API Key', 'gmap-embed'),
			'api_key_url' => esc_url('//console.developers.google.com/flows/enableapi?apiid=maps_backend,places_backend,geolocation,geocoding_backend,directions_backend&keyType=CLIENT_SIDE&reusekey=true'),
		);

		// what we collect modal content
		$wgm_wwc_msg = array(
			'title' => __('What we collect?', 'gmap-embed'),
			'desc' => __('We collect non-sensitive diagnostic data and plugin usage information. Your site URL, WordPress & PHP version, plugins & themes and email address to send you the discount coupon. This data lets us make sure this plugin always stays compatible with the most popular plugins and themes. No spam, we promise.', 'gmap-embed'),
		);
		$wgm_admin_locales = array(
			'dt' => array(
				'no_map_created' => __('No map created yet, please click on Add New to create your map.', 'gmap-embed'),
				'no_marker_created' => __('No marker created yet.', 'gmap-embed'),
			),
			'sweet_alert' => array(
				'oops' => __('Opps...', 'gmap-embed'),
				// translators: %s: Premium version URL.
				'notice_unlimited_maps' => sprintf(__('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to create <b>Unlimited Maps</b>.', 'gmap-embed'), esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=limit-maps-lock')),
				// translators: %s: Premium version URL.
				'notice_unlimited_marker' => sprintf(__('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to create <b>Unlimited Markers</b>.', 'gmap-embed'), esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=limit-markers-lock')),
				// translators: %s: Premium version URL.
				'notice_to_use_feature' => sprintf(__('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to use <b>this feature</b>.', 'gmap-embed'), esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=generic-feature-lock')),
			),
		);

		$wgm_localized = array(
			// Common data
			'api_key' => $this->config->get_api_key(),
			'is_premium_user' => $this->config->is_premium(),
			'get_p_v_url' => esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=premium-feature-lock-js'),
			'site_url' => site_url(),
			'setup_wizard' => array(
				'setup_validation_msg' => $wpgmap_setup_validation_data,
				'setup_api_key_modal' => $wpgmap_setup_api_key_modal_data,
				'wgm_wwc_msg' => $wgm_wwc_msg,
			),
			'plugin_url' => WGM_PLUGIN_URL,
			'locales' => $wgm_admin_locales,
			'wgm_ajax_nonce' => wp_create_nonce('wgm_ajax_req'),
			'wgm_map_create_nonce' => wp_create_nonce('wgm_create_map'),
			'wgm_marker_nonce' => wp_create_nonce('wgm_create_marker'),
			'wgm_preview_nonce' => wp_create_nonce('wgm_preview_map'),
			'wgm_sw_nonce' => wp_create_nonce('wgm_setup_wizard'),
			'nonces' => [
				'wpgmapembed_save_map_data' => wp_create_nonce('wpgmapembed_save_map_data'),
				'wpgmapembed_load_map_data' => wp_create_nonce('wpgmapembed_load_map_data'),
				'wpgmapembed_popup_load_map_data' => wp_create_nonce('wpgmapembed_popup_load_map_data'),
				'wpgmapembed_get_wpgmap_data' => wp_create_nonce('wpgmapembed_get_wpgmap_data'),
				'wpgmapembed_remove_wpgmap' => wp_create_nonce('wpgmapembed_remove_wpgmap'),
				'wpgmapembed_save_setup_wizard' => wp_create_nonce('wpgmapembed_save_setup_wizard'),
				'wgm_get_all_maps' => wp_create_nonce('wgm_get_all_maps'),
				'wpgmapembed_save_map_markers' => wp_create_nonce('wpgmapembed_save_map_markers'),
				'wpgmapembed_update_map_markers' => wp_create_nonce('wpgmapembed_update_map_markers'),
				'wpgmapembed_get_marker_icons' => wp_create_nonce('wpgmapembed_get_marker_icons'),
				'wpgmapembed_save_marker_icon' => wp_create_nonce('wpgmapembed_save_marker_icon'),
				'wpgmapembed_get_markers_by_map_id' => wp_create_nonce('wpgmapembed_get_markers_by_map_id'),
				'wpgmapembed_p_get_markers_by_map_id' => wp_create_nonce('wpgmapembed_p_get_markers_by_map_id'),
				'wgm_get_markers_by_map_id' => wp_create_nonce('wgm_get_markers_by_map_id'),
				'wpgmapembed_delete_marker' => wp_create_nonce('wpgmapembed_delete_marker'),
				'wpgmapembed_get_marker_data_by_marker_id' => wp_create_nonce('wpgmapembed_get_marker_data_by_marker_id'),
				'wgm_save_category' => wp_create_nonce('wgm_save_category'),
				'wgm_update_category' => wp_create_nonce('wgm_update_category'),
				'wgm_delete_category' => wp_create_nonce('wgm_delete_category'),
				'wgm_get_categories' => wp_create_nonce('wgm_get_categories'),
				'wgm_get_category_data' => wp_create_nonce('wgm_get_category_data'),
			],
			// Import related localized strings
			'import_messages' => array(
				'no_file_title' => __('No file', 'gmap-embed'),
				'no_file_text' => __('Please choose a file to import.', 'gmap-embed'),
				'file_type_mismatch_title' => __('File type mismatch', 'gmap-embed'),
				'file_type_mismatch_text' => __('The selected file does not appear to match the chosen format. Please select the correct file or change the file type.', 'gmap-embed'),
				'large_file_title' => __('Large file', 'gmap-embed'),
				'large_file_text' => __('The selected file is {file} which exceeds the server upload limit of {limit}. The upload will likely fail. Do you still want to continue?', 'gmap-embed'),
				'destructive_title' => __('Destructive action', 'gmap-embed'),
				'destructive_text' => __('You chose Replace and Create new map. This will delete existing maps and markers. This action cannot be undone. Do you want to continue?', 'gmap-embed'),
				'preview_error_title' => __('Preview error', 'gmap-embed'),
				'preview_error_text' => __('Preview failed', 'gmap-embed'),
				// Server-side import error messages (used when redirecting back to the Import tab)
				'server_error_invalid_mapping' => __('Invalid CSV column mapping. Please review the mapping and try again.', 'gmap-embed'),
				'server_error_create_map_failed' => __('Failed to create target map for marker import. Check site permissions and try again.', 'gmap-embed'),
				'server_error_empty_csv' => __('The uploaded CSV appears to be empty or malformed.', 'gmap-embed'),
				'server_error_unknown_csv_type' => __('Unknown CSV import data type selected.', 'gmap-embed'),
				'server_error_create_map_permission' => __('Insufficient permissions to create maps. You need the proper capability to import into a new map.', 'gmap-embed'),
				// Confirm / cancel labels
				'confirm_button' => __('Continue', 'gmap-embed'),
				'cancel_button' => __('Cancel', 'gmap-embed'),
				// Dry run messages
				'dryrun_title' => __('Dry Run Result', 'gmap-embed'),
				'dryrun_success' => __('Dry run completed successfully. {count} items would be imported.', 'gmap-embed'),
				'dryrun_json_success' => __('Dry run completed successfully. {maps} maps, {markers} markers and {categories} categories would be imported.', 'gmap-embed'),
				'ajax_error' => __('An error occurred while processing the request. Please try again.', 'gmap-embed'),
			),
		);

		if (isset($_GET['tag']) && sanitize_text_field(wp_unslash($_GET['tag'])) == 'edit') {
			if (isset($_GET['wgm_map_create_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['wgm_map_create_nonce'])), 'wgm_create_map')) {
				// Nonce verified for map edit action
				$map_id = isset($_GET['id']) ? intval(sanitize_text_field(wp_unslash($_GET['id']))) : 0;
				$wgm_admin_center_str = sanitize_text_field(get_post_meta($map_id, 'wpgmap_center_lat_lng', true));
				if (empty($wgm_admin_center_str)) {
					$wgm_admin_center_str = sanitize_text_field(get_post_meta($map_id, 'wpgmap_latlng', true));
				}
				$wgm_admin_current_lat_lng = explode(',', $wgm_admin_center_str);
				$current_map_marker_lat = (isset($wgm_admin_current_lat_lng[0]) && is_numeric($wgm_admin_current_lat_lng[0])) ? floatval($wgm_admin_current_lat_lng[0]) : 40.73359922990751;
				$current_map_marker_lng = (isset($wgm_admin_current_lat_lng[1]) && is_numeric($wgm_admin_current_lat_lng[1])) ? floatval($wgm_admin_current_lat_lng[1]) : -74.02791395625002;
				$wgm_map_zoom = get_post_meta($map_id, 'wpgmap_map_zoom', true);
				$wgm_map_type = get_post_meta($map_id, 'wpgmap_map_type', true);
				
				$wgm_admin_gmap_data = $this->get_wpgmapembed_data(intval($map_id));
				$wgm_admin_single_map = json_decode($wgm_admin_gmap_data);

				$wgm_localized['edit_data'] = [
					'center_lat' => $current_map_marker_lat,
					'center_lng' => $current_map_marker_lng,
					'map_id'     => intval($map_id),
					'map_zoom'   => $wgm_map_zoom ? intval($wgm_map_zoom) : 10,
					'map_type'   => $wgm_map_type ? esc_html($wgm_map_type) : 'ROADMAP',
					'theme_json' => ($wgm_admin_single_map && isset($wgm_admin_single_map->wgm_theme_json)) ? wp_kses_data($wgm_admin_single_map->wgm_theme_json) : '',
				];
				
				// Fetch categories for localization
				global $wpdb;
				$wgm_admin_categories = wp_cache_get('wgm_categories_all', 'gmap-embed');
				if (false === $wgm_admin_categories) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$wgm_admin_categories = $wpdb->get_results("SELECT id, name, parent_id FROM {$wpdb->prefix}wgm_categories ORDER BY parent_id ASC, name ASC");
					wp_cache_set('wgm_categories_all', $wgm_admin_categories, 'gmap-embed', 12 * HOUR_IN_SECONDS);
				}
				$wgm_localized['edit_data']['categories'] = $wgm_admin_categories;

				$wgm_localized['wgm_object'] = $wgm_localized['edit_data'];
			} else {
				// Nonce missing or invalid for map edit action
				$wgm_redirect_url = esc_url_raw(
					add_query_arg(
						array('page' => 'wpgmapembed'),
						admin_url('admin.php')
					)
				);
				wp_safe_redirect($wgm_redirect_url);
				exit;
			}
		}

		// Settings page data
		$wgm_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
		if ($wgm_page === 'wpgmapembed-settings') {
			$wgm_localized['settings_data'] = [
				'max_upload' => wp_max_upload_size(),
			];
			$wgm_localized['strings'] = [
				'csv_preview' => __('CSV Preview', 'gmap-embed'),
				'json_preview' => __('JSON Preview', 'gmap-embed'),
				'column_mapping' => __('Column Mapping', 'gmap-embed'),
				'column_mapping_desc' => __('Map each CSV column to plugin fields before importing.', 'gmap-embed'),
				'json_preview_label' => __('JSON Preview', 'gmap-embed'),
			];
			$wgm_localized['sample_files'] = [
				'json' => [
					'url' => esc_url(WGM_PLUGIN_URL . 'admin/assets/sample/example_markers_categories.json'),
					'label' => __('Download sample JSON', 'gmap-embed'),
				],
				'csv_markers' => [
					'url' => esc_url(WGM_PLUGIN_URL . 'admin/assets/sample/example_markers.csv'),
					'label' => __('Download sample CSV (Markers)', 'gmap-embed'),
				],
				'csv_categories' => [
					'url' => esc_url(WGM_PLUGIN_URL . 'admin/assets/sample/example_categories.csv'),
					'label' => __('Download sample CSV (Categories)', 'gmap-embed'),
				],
			];
		}

		return $wgm_localized;
	}

	public function getPluginStatus()
	{
		// API status variable ->debugging purpose
		return wp_json_encode(
			array(
				'p_v' => WGM_PLUGIN_VERSION,
				'p_d_v' => WGM_PLUGIN_DEV_VERSION,
				'l_api' => $this->config->get('_wgm_load_map_api_condition', 'always'),
				'p_api' => $this->config->get('_wgm_prevent_other_plugin_theme_api_load', 'N'),
				'i_p' => $this->config->is_premium(),
				'd_f_s_c' => $this->config->get('_wgm_disable_full_screen_control', 'N'),
				'd_s_v' => $this->config->get('_wgm_disable_street_view', 'N'),
				'd_z_c' => $this->config->get('_wgm_disable_zoom_control', 'N'),
				'd_p_c' => $this->config->get('_wgm_disable_pan_control', 'N'),
				'd_m_t_c' => $this->config->get('_wgm_disable_map_type_control', 'N'),
				'd_m_w_z' => $this->config->get('_wgm_disable_mouse_wheel_zoom', 'N'),
				'd_m_d' => $this->config->get('_wgm_disable_mouse_dragging', 'N'),
				'd_m_d_c_z' => $this->config->get('_wgm_disable_mouse_double_click_zooming', 'N'),
				'e_d_f_a_c' => $this->config->get('_wgm_enable_direction_form_auto_complete', 'N'),
				'lng' => $this->config->get('srm_gmap_lng', 'en'),
				'reg' => $this->config->get('srm_gmap_region', 'US'),
				'd_u' => $this->config->get('_wgm_distance_unit', 'km'),
				'm_r' => $this->config->get('_wgm_minimum_role_for_map_edit', 'administrator'),
				'php_v' => PHP_VERSION,
				'wp_v' => get_bloginfo('version'),
			)
		);
	}

	/**
	 * Register common scripts
	 */
	private function registerCommonScripts()
	{
		$srm_gmap_lng = $this->config->get('srm_gmap_lng', 'en');
		$srm_gmap_region = $this->config->get('srm_gmap_region', 'US');
		//phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion,WordPress.WP.EnqueuedResourceParameters.NotInFooter
		wp_register_script(
			'wp-gmap-api',
			esc_url_raw('https://maps.google.com/maps/api/js?key=' . rawurlencode($this->wpgmap_api_key) . '&libraries=places&language=' . rawurlencode($srm_gmap_lng) . '&region=' . rawurlencode($srm_gmap_region) . '&callback=wgm_gmap_api_loaded'),
			['jquery']
		);

		// Datatables
		wp_register_style('wgm-datatable-css', WGM_PLUGIN_URL . 'admin/assets/third-party/datatables/css/jquery.dataTables.min.css', array(), '1.13.7');
		wp_register_script('wgm-datatable-js', WGM_PLUGIN_URL . 'admin/assets/third-party/datatables/js/jquery.dataTables.min.js', array('jquery'), '1.13.7', true);

		// Swiper
		wp_register_style('wgm-swiper-css', WGM_PLUGIN_URL . 'admin/assets/third-party/swiper/css/swiper-bundle.min.css', array(), '11.0.5');
		wp_register_script('wgm-swiper-js', WGM_PLUGIN_URL . 'admin/assets/third-party/swiper/js/swiper-bundle.min.js', array(), '11.0.5', true);
	}

	/**
	 * Add async attribute to Google Maps API script
	 */
	public function wgm_add_async_attribute($tag, $handle, $src) {
		if ($handle === 'wp-gmap-api') {
			return str_replace(' src', ' async defer src', $tag);
		}
		return $tag;
	}

	/**
	 * Print Google Maps API Callback Script
	 */
	public function wgm_head_callback_script() {
		?>
		<script type="text/javascript">
			window.wgm_map_queue = window.wgm_map_queue || [];
			window.wgm_gmap_api_loaded = function() {
				window.wgm_map_queue.forEach(function(f) { f(); });
				window.wgm_map_queue = [];
			};
		</script>
		<?php
	}

	/**
	 * Register frontend scripts
	 */
	public function register_frontend_scripts()
	{
		wp_register_script(
			'wp-gmap-embed-front-js',
			WGM_PLUGIN_URL . 'public/assets/js/wgm-frontend.js',
			array('jquery', 'wp-gmap-api'),
			filemtime(WGM_PLUGIN_PATH . 'public/assets/js/wgm-frontend.js'),
			true
		);
	}

	public function wgm_enqueue_front_assets() {
		wp_enqueue_style('wgm-design-system-css', WGM_PLUGIN_URL . 'admin/assets/css/wgm-design-system.css', array(), filemtime(WGM_PLUGIN_PATH . 'admin/assets/css/wgm-design-system.css'));
		wp_enqueue_style('wp-gmap-front-custom-style-css', WGM_PLUGIN_URL . 'public/assets/css/front_custom_style.css', array('wgm-design-system-css'), filemtime(WGM_PLUGIN_PATH . 'public/assets/css/front_custom_style.css'));
		$custom_css_styles = esc_textarea(get_option('wpgmap_s_custom_css'));
		if (strlen($custom_css_styles) !== 0) {
			wp_add_inline_style('wp-gmap-front-custom-style-css', "$custom_css_styles");
		}
	}

	/**
	 * To enqueue CSS & JS for frontend
	 */
	public function gmap_front_enqueue_scripts()
	{
		// Register common scripts (includes: google maps api)
		$this->registerCommonScripts();

		// Based on user defined condition, enqueue Google Map API script
		if (in_array($this->config->get('_wgm_load_map_api_condition', 'always'), array('where-required', 'always', 'only-front-end'))) {
			wp_enqueue_script('wp-gmap-api');
		}

		// Custom JS script and Plugin status including
		$wgm_settings_status = $this->getPluginStatus();
		$custom_js_scripts = get_option('wpgmap_s_custom_js');
		$custom_js_scripts .= "\nvar wgm_status = $wgm_settings_status;";
		$custom_js_ca_data_enclosed = "/* <![CDATA[ */\n" . $custom_js_scripts . "\n/* ]]> */";
		wp_add_inline_script('wp-gmap-api', $custom_js_ca_data_enclosed);

		// Centralized Frontend Logic
		wp_enqueue_script('wp-gmap-embed-front-js');

		// Select2 for frontend filters
		wp_enqueue_style('wgm-select2-css', WGM_PLUGIN_URL . 'admin/assets/third-party/select2/css/select2.min.css', array(), '4.1.0-rc.0');
		wp_enqueue_script('wgm-select2-js', WGM_PLUGIN_URL . 'admin/assets/third-party/select2/js/select2.min.js', array('jquery'), '4.1.0-rc.0', true);

		$this->wgm_enqueue_front_assets();
	}

	/**
	 * To enqueue scripts for admin-panel
	 */
	function enqueue_admin_gmap_scripts()
	{
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
		// Nonce verification should be added if this triggers sensitive actions.
		global $pagenow;
		if ($pagenow === 'post.php' || $pagenow === 'post-new.php' || ($page == 'wpgmapembed' || $page === 'wpgmapembed-settings' || $page === 'wpgmapembed-new' || $page === 'wgm_setup_wizard' || $page === 'wpgmapembed-support' || $page === 'wpgmapembed-categories')) {

			// Registering common scripts (Included: Google API)
			$this->registerCommonScripts();

			// Including Google Map API for only New Map and Edit Map page
			if (in_array(esc_html(get_option('_wgm_load_map_api_condition', 'always')), array('where-required', 'always', 'only-backend-end')) && ($page === 'wpgmapembed' or $page === 'wpgmapembed-new')) {
				wp_enqueue_script('wp-gmap-api');
			}

			/** Common assets */
			wp_enqueue_script('wp-gmap-common-js', WGM_PLUGIN_URL . 'admin/assets/js/common.js', array(), filemtime(WGM_PLUGIN_PATH . 'admin/assets/js/common.js'), false);
			$wgm_localized = $this->getLocalizedScripts();
			wp_localize_script('wp-gmap-common-js', 'wgm_l', $wgm_localized);
			/** Common admin styles */
			wp_enqueue_style('wgm-design-system-css', WGM_PLUGIN_URL . 'admin/assets/css/wgm-design-system.css', array(), filemtime(WGM_PLUGIN_PATH . 'admin/assets/css/wgm-design-system.css'));
			wp_enqueue_style('wp-gmap-style-css', WGM_PLUGIN_URL . 'admin/assets/css/wp-gmap-style.css', array('wgm-design-system-css'), filemtime(WGM_PLUGIN_PATH . 'admin/assets/css/wp-gmap-style.css'));
			// Font awesome
			wp_enqueue_style('wp-gmap-fontawasome-css', WGM_PLUGIN_URL . 'admin/assets/third-party/font-awesome/5/css/font-awesome.css', array(), filemtime(WGM_PLUGIN_PATH . 'admin/assets/third-party/font-awesome/5/css/font-awesome.css'));
			// Sweet alert related
			wp_enqueue_style('wp-gmap-sweetalert2-css', WGM_PLUGIN_URL . 'admin/assets/third-party/sweetalert2/css/sweetalert2.min.css', array(), filemtime(WGM_PLUGIN_PATH . 'admin/assets/third-party/sweetalert2/css/sweetalert2.min.css'));
			wp_enqueue_script('wp-gmap-sweetalert2-js', WGM_PLUGIN_URL . 'admin/assets/third-party/sweetalert2/js/sweetalert2.min.js', array(), filemtime(WGM_PLUGIN_PATH . 'admin/assets/third-party/sweetalert2/js/sweetalert2.min.js'), true);
			// Media upload
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			wp_enqueue_script('wpgmap-media-upload');
			wp_enqueue_media();
			wp_enqueue_style('thickbox');

			/** Edit and Add Map page */
			if ($pagenow === 'post.php' || $pagenow === 'post-new.php' || ($page === 'wpgmapembed' or $page === 'wpgmapembed-new')) {
				wp_enqueue_script('wgm-map-curd-js', WGM_PLUGIN_URL . 'admin/assets/js/wgm_map_crud.js', array('wp-gmap-common-js', 'wgm-datatable-js'), filemtime(WGM_PLUGIN_PATH . 'admin/assets/js/wgm_map_crud.js'), true);
				wp_enqueue_script('wp-gmap-markers-js', WGM_PLUGIN_URL . 'admin/assets/js/wgm_marker_crud.js', array('wp-gmap-common-js'), filemtime(WGM_PLUGIN_PATH . 'admin/assets/js/wgm_marker_crud.js'), true);
				wp_enqueue_script('wgm-geo-based-map-edit-js', WGM_PLUGIN_URL . 'admin/assets/js/geo_based_map_edit.js', array('wp-gmap-common-js', 'wgm-select2-js'), filemtime(WGM_PLUGIN_PATH . 'admin/assets/js/geo_based_map_edit.js'), true);
				wp_enqueue_script('wgm-icon-selector-js', WGM_PLUGIN_URL . 'admin/assets/js/wgm-icon-selector.js', array('jquery'), filemtime(WGM_PLUGIN_PATH . 'admin/assets/js/wgm-icon-selector.js'), true);
				// Select2
				wp_enqueue_style('wgm-select2-css', WGM_PLUGIN_URL . 'admin/assets/third-party/select2/css/select2.min.css', array(), '4.1.0-rc.0');
				wp_enqueue_script('wgm-select2-js', WGM_PLUGIN_URL . 'admin/assets/third-party/select2/js/select2.min.js', array('jquery'), '4.1.0-rc.0', true);
				// Datatables
				wp_enqueue_style('wgm-datatable-css');
				wp_enqueue_script('wgm-datatable-js');
			}

			/** Setup Wizard */
			if ($page === 'wgm_setup_wizard') {
				wp_enqueue_style('wp-gmap-setup-wizard-css', WGM_PLUGIN_URL . 'admin/assets/css/setup_wizard.css', array(), filemtime(WGM_PLUGIN_PATH . '/admin/assets/css/setup_wizard.css'));
				wp_enqueue_script('wp-gmap-setup-wizard-js', WGM_PLUGIN_URL . 'admin/assets/js/setup_wizard.js', array(), filemtime(WGM_PLUGIN_PATH . 'admin/assets/js/setup_wizard.js'), true);
			}

			/** Page-specific admin overrides */

			if ($page === 'wpgmapembed-settings') {
				wp_enqueue_script('wgm-admin-settings-js', WGM_PLUGIN_URL . 'admin/assets/js/wgm-admin-settings.js', array('wp-gmap-common-js'), filemtime(WGM_PLUGIN_PATH . 'admin/assets/js/wgm-admin-settings.js'), true);
			}
			if ($page === 'wpgmapembed' || $page === 'wpgmapembed-new') {
				wp_enqueue_script('wgm-admin-edit-js', WGM_PLUGIN_URL . 'admin/assets/js/wgm-admin-edit.js', array('wp-gmap-common-js'), filemtime(WGM_PLUGIN_PATH . 'admin/assets/js/wgm-admin-edit.js'), true);
			}
			if ($page === 'wpgmapembed-categories') {
				wp_enqueue_script('wgm-categories-js', WGM_PLUGIN_URL . 'admin/assets/js/categories.js', array('wp-gmap-common-js', 'wgm-datatable-js', 'wp-gmap-sweetalert2-js'), filemtime(WGM_PLUGIN_PATH . 'admin/assets/js/categories.js'), true);
				wp_enqueue_style('wgm-datatable-css');
				wp_enqueue_script('wgm-datatable-js');
			}
		}
	}
}
