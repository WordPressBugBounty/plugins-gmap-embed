<?php
namespace WGMSRM\Traits;

defined( 'ABSPATH' ) || exit;

trait ImportExport
{
	/**
	 * Register import/export hooks
	 */
	public function register_import_export_hooks()
	{
		add_action('admin_post_wgm_export', array($this, 'wgm_export_data'));
		add_action('admin_post_wgm_import', array($this, 'wgm_handle_import'));
		
		// Preview import (AJAX)
		add_action('wp_ajax_wgm_import_preview', array($this, 'wgm_import_preview'));
		add_action('wp_ajax_wgm_import', array($this, 'wgm_handle_import'));
	}

	/**
	 * Export data handler
	 */
	public function wgm_export_data()
	{
		// Nonce validation
		check_admin_referer('wgm_export_nonce', '_wgm_export_nonce');

		if (!current_user_can($this->capability)) {
			wp_die(esc_html__('You do not have permission to export data.', 'gmap-embed'));
		}

		$export_type = isset($_POST['wgm_export_type']) ? sanitize_text_field(wp_unslash($_POST['wgm_export_type'])) : 'json';
		$map_ids = [];
		if (isset($_POST['wgm_maps'])) {
			$raw_maps = map_deep(wp_unslash($_POST['wgm_maps']), 'sanitize_text_field');
			if (is_array($raw_maps)) {
				$map_ids = array_map('intval', $raw_maps);
			} else {
				$map_ids[] = intval($raw_maps);
			}
		}

		// Initial export structure
		$export_data = [
			'creator' => 'WPGoogleMap',
			'plugin_version' => WGM_PLUGIN_VERSION,
			'json_version' => '1.1',
			'maps' => [],
			'markers' => [],
			'categories' => [],
		];

		// 1. Always fetch Map data for selected IDs
		$maps = $this->get_all_maps($map_ids);
		$export_data['maps'] = $maps ? $maps : [];

		// 2. Fetch ALL markers for these maps (needed for category calculation even if not exporting)
		$map_ids_for_query = array_filter(array_column($export_data['maps'], 'id'));
		$related_markers = [];
		if (!empty($map_ids_for_query)) {
			global $wpdb;
			$ids_placeholders = implode(',', array_fill(0, count($map_ids_for_query), '%d'));
			// Building query string with placeholders for IN clause
			$markers_query = "SELECT id, map_id, marker_name, marker_desc, icon, address, lat_lng,
					have_marker_link, marker_link, marker_link_new_tab, animation, category_id, show_desc_by_default
					FROM {$wpdb->prefix}wgm_markers WHERE map_id IN ($ids_placeholders)";
			
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			$related_markers = $wpdb->get_results(
				$wpdb->prepare(
					$markers_query, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$map_ids_for_query
				),
				ARRAY_A
			);
		}


		// Determine selected types from UI based on format
		$selected_types = [];
		if ($export_type === 'json') {
			$selected_types = isset($_POST['wgm_export_data_types']) ? array_map('sanitize_text_field', wp_unslash($_POST['wgm_export_data_types'])) : ['markers', 'categories'];
		} elseif ($export_type === 'csv') {
			// For CSV, we only allow one type, but we map it to our internal array structure
			$csv_type = isset($_POST['wgm_export_data_type']) ? sanitize_text_field(wp_unslash($_POST['wgm_export_data_type'])) : 'markers';
			$selected_types = [$csv_type];
		}
		
		// 3. Add Markers to export if selected
		if (in_array('markers', $selected_types)) {
			$export_data['markers'] = $related_markers ? $related_markers : [];
		}

		// 4. Calculate and Add Categories only if selected
		if (in_array('categories', $selected_types) && !empty($related_markers)) {
			// Collect used category IDs
			$raw_cat_ids = array_column($related_markers, 'category_id');
			$used_cat_ids = [];
			foreach ($raw_cat_ids as $val) {
				if (empty($val)) continue;
				// Handle multiple categories (comma separated)
				$parts = explode(',', $val);
				foreach ($parts as $p) {
					$p = intval(trim($p));
					if ($p > 0) {
						$used_cat_ids[] = $p;
					}
				}
			}
			$used_cat_ids = array_unique($used_cat_ids);
			
			if (!empty($used_cat_ids)) {
				global $wpdb;
				$categories_table = $wpdb->prefix . 'wgm_categories';
				
				// Recursive fetch for parents
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$all_cats_pool = $wpdb->get_results("SELECT * FROM {$categories_table}", ARRAY_A);
				
				// Index by ID for easier lookup
				$cat_lookup = [];
				foreach ($all_cats_pool as $c) {
					$cat_lookup[intval($c['id'])] = $c;
				}

				$final_export_cats = [];
				$processed_ids = [];
				$queue = $used_cat_ids;

				while (!empty($queue)) {
					$cid = array_pop($queue);
					$cid = intval($cid);
					
					if (isset($processed_ids[$cid])) continue;
					if (!isset($cat_lookup[$cid])) continue;

					$processed_ids[$cid] = true;
					$cat_obj = $cat_lookup[$cid];
					$final_export_cats[] = $cat_obj;

					// Add parent to queue
					if (!empty($cat_obj['parent_id'])) {
						$queue[] = intval($cat_obj['parent_id']);
					}
				}

				$export_data['categories'] = $final_export_cats;
			}
		}

		if ($export_type === 'json') {
			header('Content-Disposition: attachment; filename="gmap-export.json"');
			header('Content-Type: application/json; charset=utf-8');
			echo wp_json_encode($export_data, JSON_PRETTY_PRINT);
			exit;
		} elseif ($export_type === 'csv') {
			$export_data_type_csv = isset($_POST['wgm_export_data_type']) ? sanitize_text_field(wp_unslash($_POST['wgm_export_data_type'])) : '';
			// CSV logic remains largely similar, just pulling from our new filtered dataset
			// Note: CSV export in this plugin generally implies flat single-type export.
			// Maps or Markers are the options in UI.
			
			$data_csv = [];
			if ($export_data_type_csv === 'maps') {
				$data_csv = $export_data['maps'];
			} elseif ($export_data_type_csv === 'markers') {
				$data_csv = $export_data['markers'];
			} elseif ($export_data_type_csv === 'categories') {
				// Use the processed categories (which includes dependencies)
				$data_csv = $export_data['categories'];
			}

			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="gmap-export.csv"');
			
			if (!empty($data_csv)) {
				// Get headers from first row
				$all_headers = array_keys(reset($data_csv));
				// Filter out timestamps
				$headers = array_filter($all_headers, function($h) {
					return !in_array($h, ['created_at', 'updated_at']);
				});

				$csv_content = '';
				
				// Helper for manual CSV generation
				$to_csv_row = function($data) {
					foreach ($data as &$val) {
						$val = '"' . str_replace('"', '""', $val) . '"';
					}
					return implode(',', $data) . "\n";
				};

				$csv_content .= $to_csv_row($headers);
				foreach ($data_csv as $row) {
					$filtered_row = [];
					foreach($headers as $h) {
						$filtered_row[] = isset($row[$h]) ? $row[$h] : '';
					}
					$csv_content .= $to_csv_row($filtered_row);
				}
				echo $csv_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			exit;
		}
	}

	/**
	 * Import data handler
	 */
	public function wgm_handle_import()
	{
		$redirect_with_error = function ($code, $message = '') {
			$args = array('wgm_import' => 'error', 'error_code' => $code);
			if (!empty($message)) {
				$args['error_msg'] = rawurlencode(sanitize_text_field(wp_strip_all_tags($message)));
			}
			
			if (defined('DOING_AJAX') && DOING_AJAX) {
				wp_send_json_error(array('message' => $message ?: $code));
			}

			wp_safe_redirect(add_query_arg($args, wp_get_referer() ?: admin_url('admin.php?page=wpgmapembed-settings')));
			exit;
		};

		$nonce = isset($_POST['_wgm_import_nonce']) ? sanitize_text_field(wp_unslash($_POST['_wgm_import_nonce'])) : '';
		if (empty($nonce) || !wp_verify_nonce($nonce, 'wgm_import_nonce')) {
			$redirect_with_error('invalid_nonce', 'Invalid import request.');
		}

		if (!current_user_can($this->capability)) {
			$redirect_with_error('no_permission', 'You do not have permission to import data.');
		}

		if (
			empty($_FILES['wgm_import_file']) || 
			!isset($_FILES['wgm_import_file']['error']) || 
			$_FILES['wgm_import_file']['error'] !== UPLOAD_ERR_OK || 
			!isset($_FILES['wgm_import_file']['tmp_name']) || 
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Path sanitized below/validated.
			!is_uploaded_file($_FILES['wgm_import_file']['tmp_name'])
		) {
			$redirect_with_error('no_file', 'No import file uploaded or upload error.');
		}

		if (isset($_FILES['wgm_import_file']['size']) && $_FILES['wgm_import_file']['size'] > $this->max_import_size) {
			$redirect_with_error('file_too_large', 'Uploaded file exceeds maximum allowed size.');
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$uploaded = $_FILES['wgm_import_file'];
		$ext = strtolower(pathinfo($uploaded['name'], PATHINFO_EXTENSION));
		
		// Fallback for extension check if needed
		if ($ext !== 'json' && $ext !== 'csv') {
			$redirect_with_error('invalid_file_type', 'Unsupported file type. Only JSON and CSV are allowed.');
		}

		$import_type_ui = isset($_POST['wgm_import_type']) ? sanitize_text_field(wp_unslash($_POST['wgm_import_type'])) : 'json';
		// If UI says json but file is csv (or vice versa), trust file extension or UI? Usually trust extension for processing logic.
		$process_type = ($ext === 'json') ? 'json' : 'csv';

		$import_mode = isset($_POST['wgm_import_mode']) ? sanitize_text_field(wp_unslash($_POST['wgm_import_mode'])) : 'merge';
		$import_target = isset($_POST['wgm_import_target']) ? sanitize_text_field(wp_unslash($_POST['wgm_import_target'])) : 'new';
		$target_map_id = isset($_POST['wgm_target_map_id']) ? intval(wp_unslash($_POST['wgm_target_map_id'])) : 0;
		$dry_run = isset($_POST['wgm_import_dry_run']) && sanitize_text_field(wp_unslash($_POST['wgm_import_dry_run'])) === '1';

		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
		global $wp_filesystem;

		$file_contents = $wp_filesystem->get_contents($uploaded['tmp_name']);
		if ($file_contents === false) {
			$redirect_with_error('read_error', 'Failed to read uploaded file.');
		}

		global $wpdb;
		$markers_table = "{$wpdb->prefix}wgm_markers";
		$categories_table = "{$wpdb->prefix}wgm_categories";

		// Helper to sanitize meta
		$sanitize_meta = function ($key, $value) {
			if (is_array($value) || is_object($value)) {
				return wp_json_encode($value);
			}
			if ($key === 'marker_desc' || $key === 'wgm_marker_desc') {
				return wp_kses_post((string) $value);
			}
			if ($key === 'wgm_theme_json') {
				return (string) $value;
			}
			return sanitize_text_field((string) $value);
		};

		// ID Mappings
		$map_id_map = []; // old_id => new_id
		$cat_id_map = []; // old_id => new_id

		// --------------------- DELETE LOGIC ---------------------
		if (!$dry_run && $import_mode === 'replace') {
			$data_type = isset($_POST['wgm_import_data_type']) ? sanitize_text_field(wp_unslash($_POST['wgm_import_data_type'])) : 'markers';

			if ($process_type === 'csv' && $data_type === 'categories') {
				// selective category wipe
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$wpdb->query("TRUNCATE TABLE {$categories_table}");
			} elseif ($import_target === 'new') {
				// Wipe EVERYTHING (Standard for JSON or Marker->New Map batch)
				$existing_maps = get_posts(array('post_type' => 'wpgmapembed', 'numberposts' => -1, 'post_status' => 'any'));
				foreach ($existing_maps as $p) wp_delete_post($p->ID, true);
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$wpdb->query("TRUNCATE TABLE {$markers_table}");
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$wpdb->query("TRUNCATE TABLE {$categories_table}");
			} elseif ($import_target === 'existing' && $target_map_id) {
				// Just wipe markers for this map
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query($wpdb->prepare("DELETE FROM {$markers_table} WHERE map_id = %d", intval($target_map_id)));
			}
		}

		// Existing category lookup (by name) - MUST BE AFTER WIPE
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$existing_cats = $wpdb->get_results("SELECT id, name FROM {$categories_table}", ARRAY_A);
		$cat_name_to_id = [];
		foreach ($existing_cats as $ecat) {
			$cat_name_to_id[strtolower($ecat['name'])] = intval($ecat['id']);
		}

		// --------------------- JSON PROCESSING ---------------------
		if ($process_type === 'json') {
			// Remove UTF-8 BOM if present
			$bom = pack('H*', 'EFBBBF');
			if (substr($file_contents, 0, 3) === $bom) {
				$file_contents = substr($file_contents, 3);
			}
			$payload = json_decode($file_contents, true);
			if (!is_array($payload)) $redirect_with_error('invalid_json', 'Invalid JSON file.');

			$maps = isset($payload['maps']) ? $payload['maps'] : [];
			$markers = isset($payload['markers']) ? $payload['markers'] : [];
			$categories = isset($payload['categories']) ? $payload['categories'] : [];

			// 1. Import Categories
			$created_cats = 0;
			$matched_cats = 0;
			if (!empty($categories)) {
				foreach ($categories as $cat) {
					$old_id = isset($cat['id']) ? intval($cat['id']) : 0;
					// remove id to auto-increment, remove dates to use current
					unset($cat['id'], $cat['created_at'], $cat['updated_at']);
					
					// Sanitize
					$cat['name'] = sanitize_text_field($cat['name']);
					$cat['icon'] = sanitize_text_field($cat['icon']);
					
					// Check for existing category by name
					$lower_name = strtolower($cat['name']);
					if (isset($cat_name_to_id[$lower_name])) {
						if ($old_id) $cat_id_map[$old_id] = $cat_name_to_id[$lower_name];
						$matched_cats++;
						continue;
					}

					// Parent ID logic is tricky if IDs change. 
					// Simplified: Reset parent to 0 for now to avoid broken trees, or try to map if parent imported first.
					// Ideally we should do a second pass potential parents, but for now lets default 0 if mapping not found.
					$cat['parent_id'] = 0; 

					if (!$dry_run) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
						$wpdb->insert($categories_table, $cat);
						$new_id = $wpdb->insert_id;
						if ($old_id) $cat_id_map[$old_id] = $new_id;
						// Add to local cache to prevent duplicates in same file
						$cat_name_to_id[strtolower($cat['name'])] = $new_id;
					}
					$created_cats++;
				}
				
				// Fix parents if possible (if we had improved logic we'd do a second pass update here using $cat_id_map)
			}

			// 2. Import Maps
			$created_maps = 0;
			foreach ($maps as $map) {
				$orig_id = isset($map['id']) ? intval($map['id']) : 0;
				$title = isset($map['wpgmap_title']) ? sanitize_text_field($map['wpgmap_title']) : __('Imported Map', 'gmap-embed');

				// Skip map creation if importing into existing target
				if ($import_target === 'existing' && $target_map_id) {
					if ($orig_id) $map_id_map[$orig_id] = $target_map_id;
					// Optionally update options of the existing map? For now, assume we just want markers.
					continue;
				}

				if ($dry_run) {
					$map_id_map[$orig_id] = -($orig_id ?: ++$created_maps);
					$created_maps++;
					continue;
				}

				$new_id = $this->initiate_new_map($title);

				if ($new_id && !is_wp_error($new_id)) {
					$map_id_map[$orig_id] = $new_id;
					// Import Meta
					foreach ($map as $key => $val) {
						if ($key === 'id') continue;
						update_post_meta($new_id, $key, $sanitize_meta($key, $val));
					}
					$created_maps++;
				}
			}

			// 2b. Map Fallback for Markers
			// If we are merging into an existing map but valid JSON didn't have map objects, map 0 => target
			if ($import_target === 'existing' && $target_map_id && empty($maps)) {
				$map_id_map[0] = $target_map_id;
			}

			// 3. Import Markers
			$inserted_markers = 0;
			foreach ($markers as $marker) {
				// Resolve Map ID
				$orig_map = isset($marker['map_id']) ? intval($marker['map_id']) : 0;
				$new_map = isset($map_id_map[$orig_map]) ? $map_id_map[$orig_map] : 0;
				
				if ($import_target === 'existing' && $target_map_id) {
					$new_map = $target_map_id;
				}

				// If we have no valid map destination, skip or create default? 
				// If new_map is 0 and we are in new mode, we need a map. 
				// Logic: create one catch-all map if needed? For now, skip orphans.
				if (!$new_map && !$dry_run) continue;

				// Resolve Category ID
				$orig_cat = isset($marker['category_id']) ? intval($marker['category_id']) : 0;
				$new_cat = isset($cat_id_map[$orig_cat]) ? $cat_id_map[$orig_cat] : 0;

				$insert = array(
					'map_id' => $new_map,
					'marker_name' => isset($marker['marker_name']) ? sanitize_text_field($marker['marker_name']) : '',
					'marker_desc' => isset($marker['marker_desc']) ? $sanitize_meta('marker_desc', $marker['marker_desc']) : '',
					'icon' => isset($marker['icon']) ? sanitize_text_field($marker['icon']) : '',
					'address' => isset($marker['address']) ? sanitize_text_field($marker['address']) : '',
					'lat_lng' => isset($marker['lat_lng']) ? sanitize_text_field($marker['lat_lng']) : '',
					'have_marker_link' => isset($marker['have_marker_link']) ? sanitize_text_field($marker['have_marker_link']) : '',
					'marker_link' => isset($marker['marker_link']) ? sanitize_text_field($marker['marker_link']) : '',
					'marker_link_new_tab' => isset($marker['marker_link_new_tab']) ? intval($marker['marker_link_new_tab']) : 0,
					'show_desc_by_default' => isset($marker['show_desc_by_default']) ? intval($marker['show_desc_by_default']) : 0,
					'category_id' => $new_cat
				);

				if (!$dry_run) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
					$wpdb->insert($markers_table, $insert);
				}
				$inserted_markers++;
			}

			// Success Redirect
			if ($dry_run) {
				if (defined('DOING_AJAX') && DOING_AJAX) {
					wp_send_json_success(array(
						'type' => 'json',
						'maps' => $created_maps,
						'markers' => $inserted_markers,
						'categories' => $created_cats + $matched_cats
					));
				}
				$query_args = array('wgm_import' => 'dryrun', 'maps_simulated' => $created_maps, 'markers_simulated' => $inserted_markers);
			} else {
				$query_args = array('wgm_import' => 'success');
			}
			wp_safe_redirect(add_query_arg($query_args, wp_get_referer() ?: admin_url('admin.php')));
			exit;
		}

			// --------------------- CSV PROCESSING ---------------------
		if ($process_type === 'csv') {
			$data_type = isset($_POST['wgm_import_data_type']) ? sanitize_text_field(wp_unslash($_POST['wgm_import_data_type'])) : 'markers';
			
			global $wp_filesystem;
			WP_Filesystem();
			$csv_content = $wp_filesystem->get_contents($uploaded['tmp_name']);
			
			if (empty($csv_content)) {
				wp_safe_redirect(add_query_arg(array('wgm_import' => 'error', 'error_code' => 'file_open_failed'), wp_get_referer()));
				exit;
			}

			$csv_rows = str_getcsv($csv_content, "\n");
			$header_row = array_shift($csv_rows);
			$header = $header_row ? str_getcsv($header_row) : [];

			if (!$header) {
				wp_safe_redirect(add_query_arg(array('wgm_import' => 'error', 'error_code' => 'empty_csv'), wp_get_referer()));
				exit;
			}

			// Map Mapping Logic
			$mapping = [];
			if (!empty($_POST['wgm_import_mapping'])) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized recursively using map_deep below.
				$mapping = json_decode(wp_unslash($_POST['wgm_import_mapping']), true);
				$mapping = map_deep($mapping, 'sanitize_text_field');
			}

			if ($data_type === 'categories') {
				// Pass 1: Insert Categories (ignore parents initially)
				$created_cats = 0;
				$temp_parent_map = []; // new_id => old_parent_id

				foreach ($csv_rows as $row_str) {
					if (empty(trim($row_str))) continue;
					$row = str_getcsv($row_str);

					if (count($row) !== count($header)) continue;
					$item = array_combine($header, $row);

					// Apply mapping
					if (!empty($mapping)) {
						$mapped_item = [];
						foreach ($item as $k => $v) {
							$key = isset($mapping[$k]) && $mapping[$k] ? $mapping[$k] : $k;
							$mapped_item[$key] = $v;
						}
						$item = $mapped_item;
					}

					$old_id = isset($item['id']) ? intval($item['id']) : 0;
					$old_parent_id = isset($item['parent_id']) ? intval($item['parent_id']) : 0;
					$cat_name = isset($item['name']) ? sanitize_text_field($item['name']) : 'Imported Category';

					// Check for existing category by name
					$lower_name = strtolower($cat_name);
					if (isset($cat_name_to_id[$lower_name])) {
						if ($old_id) $cat_id_map[$old_id] = $cat_name_to_id[$lower_name];
						continue;
					}

					if (!$dry_run) {
						$insert = [
							'name' => $cat_name,
							'icon' => isset($item['icon']) ? sanitize_text_field($item['icon']) : '',
							'parent_id' => 0, // Set to 0 initially
							'created_at' => current_time('mysql'),
							'updated_at' => current_time('mysql'),
						];
						
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
						$wpdb->insert($categories_table, $insert);
						$new_id = $wpdb->insert_id;
						
						if ($old_id) {
							$cat_id_map[$old_id] = $new_id;
						}
						
						// Add to local cache
						$cat_name_to_id[$lower_name] = $new_id;

						// Store parent ref for Pass 2 if it exists
						if ($old_parent_id) {
							$temp_parent_map[$new_id] = $old_parent_id;
						}
					}
					$created_cats++;
				}

				// Pass 2: Update Parents
				if (!$dry_run && !empty($temp_parent_map)) {
					foreach ($temp_parent_map as $child_new_id => $old_parent_id) {
						if (isset($cat_id_map[$old_parent_id])) {
							$new_parent_id = $cat_id_map[$old_parent_id];
							// Update DB
							// Update DB
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							$wpdb->update(
								$categories_table,
								['parent_id' => $new_parent_id],
								['id' => $child_new_id],
								['%d'],
								['%d']
							);
						}
					}
				}

				$status = $dry_run ? 'dryrun' : 'success';
				if ($dry_run && defined('DOING_AJAX') && DOING_AJAX) {
					wp_send_json_success(array(
						'type' => 'categories',
						'count' => $created_cats
					));
				}
				wp_safe_redirect(add_query_arg(array('wgm_import' => $status, 'type' => 'categories', 'count' => $created_cats), wp_get_referer()));
				exit;
			} elseif ($data_type === 'markers') {
				$inserted = 0;
				foreach ($csv_rows as $row_str) {
					if (empty(trim($row_str))) continue;
					$row = str_getcsv($row_str);

					if (count($row) !== count($header)) continue;
					$item = array_combine($header, $row);
					
					// Apply mapping
					if ($mapping) {
						$newItem = [];
						foreach ($item as $k => $v) {
							$key = isset($mapping[$k]) && $mapping[$k] ? $mapping[$k] : $k;
							$newItem[$key] = $v;
						}
						$item = $newItem;
					}

					// Resolve Map
					$target = $target_map_id; // Default to selected
					if ($import_target === 'new') {
						static $new_csv_map_id = 0;
						if (!$new_csv_map_id && !$dry_run) {
							$new_csv_map_id = $this->initiate_new_map(__('Imported Map (CSV)', 'gmap-embed'));
						}
						$target = $new_csv_map_id;
					}

					$cat_id = isset($item['category_id']) ? intval($item['category_id']) : 0;

					if (!$dry_run && $target) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
					$wpdb->insert($markers_table, array(
							'map_id' => $target,
							'marker_name' => isset($item['marker_name']) ? sanitize_text_field($item['marker_name']) : '',
							'lat_lng' => isset($item['lat_lng']) ? sanitize_text_field($item['lat_lng']) : '',
							'address' => isset($item['address']) ? sanitize_text_field($item['address']) : '',
							'category_id' => $cat_id,
							'icon' => isset($item['icon']) ? sanitize_text_field($item['icon']) : '',
							'animation' => isset($item['animation']) ? sanitize_text_field($item['animation']) : '',
							'have_marker_link' => isset($item['have_marker_link']) ? sanitize_text_field($item['have_marker_link']) : '0',
							'marker_link' => isset($item['marker_link']) ? sanitize_text_field($item['marker_link']) : '',
							'marker_link_new_tab' => isset($item['marker_link_new_tab']) ? intval($item['marker_link_new_tab']) : 0,
							'show_desc_by_default' => isset($item['show_desc_by_default']) ? intval($item['show_desc_by_default']) : 0,
							'marker_desc' => isset($item['marker_desc']) ? $sanitize_meta('marker_desc', $item['marker_desc']) : ''
						));
					}
					$inserted++;
				}
				// Redirect...
				$status = $dry_run ? 'dryrun' : 'success';
				if ($dry_run && defined('DOING_AJAX') && DOING_AJAX) {
					wp_send_json_success(array(
						'type' => 'markers',
						'count' => $inserted
					));
				}
				wp_safe_redirect(add_query_arg(array('wgm_import' => $status, 'type' => 'markers', 'count' => $inserted), wp_get_referer()));
				exit;
			}
		}
	}

	/**
	 * Preview Import Handler
	 * Returns a small sample preview of uploaded content (CSV columns or JSON counts)
	 */
	public function wgm_import_preview() {
		// Nonce validation
		$nonce = isset($_POST['_wgm_import_nonce']) ? sanitize_text_field(wp_unslash($_POST['_wgm_import_nonce'])) : '';
		/* phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- verified via wp_verify_nonce below */
		if (empty($nonce) || !wp_verify_nonce($nonce, 'wgm_import_nonce')) {
			wp_send_json_error(['message' => __('Invalid security nonce. Please reload the page.', 'gmap-embed')]);
		}

		if (!current_user_can($this->capability)) {
			wp_send_json_error(['message' => __('You do not have permission to preview imports.', 'gmap-embed')]);
		}

		$type = isset($_POST['preview_type']) ? sanitize_text_field(wp_unslash($_POST['preview_type'])) : 'json';
		$content = isset($_POST['content']) ? wp_unslash($_POST['content']) : '';
		$max_rows = isset($_POST['max_rows']) ? intval($_POST['max_rows']) : 5;

		if (empty($content)) {
			wp_send_json_error(['message' => __('No content detected in the file.', 'gmap-embed')]);
		}

		if ($type === 'csv') {
			$rows_all = str_getcsv($content, "\n");
			$header_row = array_shift($rows_all);
			$all_columns = $header_row ? str_getcsv($header_row) : [];

			if (!$all_columns) {
				wp_send_json_error(['message' => __('Failed to parse CSV header.', 'gmap-embed')]);
			}
			
			// Filter out columns we don't want to show (timestamps)
			$columns = array_values(array_filter($all_columns, function($c) {
				return !in_array($c, ['created_at', 'updated_at']);
			}));
			
			if (empty($columns)) {
				wp_send_json_error(['message' => __('Could not find valid columns in the CSV header.', 'gmap-embed')]);
			}

			$rows = [];
			$total_rows = 0;
			foreach ($rows_all as $row_str) {
				if (empty(trim($row_str))) continue;
				$row_data = str_getcsv($row_str);
				
				if (count($row_data) === count($all_columns)) {
					$total_rows++;
					// Only add to rows array if we haven't reached max_rows
					if (count($rows) < $max_rows) {
						$combined = array_combine($all_columns, $row_data);
						$filtered_row = [];
						foreach($columns as $c) {
							$filtered_row[$c] = isset($combined[$c]) ? $combined[$c] : '';
						}
						$rows[] = $filtered_row;
					}
				}
			}

			wp_send_json_success([
				'type' => 'csv',
				'columns' => $columns,
				'rows' => $rows,
				'total_rows' => $total_rows
			]);
		} elseif ($type === 'json') {
			// Remove UTF-8 BOM if present
			$bom = pack('H*', 'EFBBBF');
			if (substr($content, 0, 3) === $bom) {
				$content = substr($content, 3);
			}

			// JSON preview might fail if content is truncated (chunked)
			$data = json_decode($content, true); 
			if (json_last_error() !== JSON_ERROR_NONE) {
				// If it failed and content is large, it's likely truncated
				if (strlen($content) >= 190000) {
					wp_send_json_error(['message' => __('The JSON file is too large to preview (truncated), but you can still attempt the import.', 'gmap-embed')]);
				}
				wp_send_json_error(['message' => __('Invalid JSON structure: ', 'gmap-embed') . json_last_error_msg()]);
			}

			wp_send_json_success([
				'type' => 'json',
				'maps' => isset($data['maps']) ? $data['maps'] : [],
				'markers' => isset($data['markers']) ? $data['markers'] : [],
				'categories' => isset($data['categories']) ? $data['categories'] : [],
			]);
		}

		wp_send_json_error(['message' => __('Unsupported preview format.', 'gmap-embed')]);
	}
}
