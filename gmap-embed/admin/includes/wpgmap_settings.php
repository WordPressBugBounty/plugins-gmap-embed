<?php
// Ensure no whitespace or output before this tag!

if (!defined('ABSPATH')) {
	exit;
}
/* Plugin-prefixed global name */
/* Plugin-prefixed global name */
$gmap_embed_admin_messages_group = 'wgm_messages';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only used for displaying a message based on URL parameter.
if (isset($_GET['settings-updated'])) {
	// add settings saved message with the class of "updated"
	add_settings_error($gmap_embed_admin_messages_group, 'wgm_message', __('Settings updated successfully.', 'gmap-embed'), 'updated');
}
settings_errors($gmap_embed_admin_messages_group);
/* Plugin-prefixed global name */
$gmap_embed_admin_settings_nonce = wp_create_nonce('wgm_settings');
$gmap_embed_admin_api_base_url = esc_url('https://wpgooglemap.com');



// Updating api key


// Messages are handled via wgm_messages_viewer.php and redirect parameters.
$gmap_embed_admin_settings_nonce = wp_create_nonce('wgm_settings');
$gmap_embed_admin_api_base_url = esc_url('https://wpgooglemap.com');
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e('Settings', 'gmap-embed'); ?></h1>
	<?php
	if (!_wgm_is_premium()) {
		echo '<a target="_blank" href="' . esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=settings-page-header-upgrade-btn') . '" class="button wgm_btn" style="float:right;width:auto;padding: 5px 7px;font-size: 11px;margin-left:5px;"><i style="line-height: 25px;" class="dashicons dashicons-star-filled"></i> ' . esc_html__('Upgrade ($19 only)', 'gmap-embed') . '</a>';
	}
	echo '<a target="_blank" href="' . esc_url('https://tawk.to/chat/6083e29962662a09efc1acd5/1f41iqarp') . '" class="button wgm_btn" style="float:right;width:auto;padding: 5px 7px;font-size: 11px;margin-right:5px;background-color: #cb5757 !important;color: white !important;"><i style="line-height: 28px;" class="dashicons dashicons-format-chat"></i> ' . esc_html__('LIVE Chat', 'gmap-embed') . '</a>';
	echo '<a href="' . esc_url(admin_url('admin.php?page=wpgmapembed-support')) . '" class="button wgm_btn" style="float:right;width:auto;padding: 5px 7px;font-size: 11px;margin-right:5px;"><i style="line-height: 25px;" class="dashicons  dashicons-editor-help"></i> ' . esc_html__('Documentation', 'gmap-embed') . '</a>';
	?>
	<hr class="wp-header-end">
	<!--Settings Tabs-->
	<div class="wgm-settings-menu">
		<ul>
			<li class="active" data-tab="wgm_general_settings"><a href="#wgm_general_settings">General Settings</a></li>
			<li data-tab="wgm_advanced_settings"><a href="#wgm_advanced_settings">Advanced Settings</a></li>
			<li data-tab="wgm_export_settings"><a href="#wgm_export_settings">Export</a></li>
			<li data-tab="wgm_import_settings"><a href="#wgm_import_settings">Import</a></li>
			<li data-tab="wgm_marker_settings"><a href="#wgm_marker_settings">Marker Settings</a></li>
		</ul>
	</div>
	<div id="gmap_container_inner" style="margin-top: 0;border-top: none;">
		<?php require_once WGM_PLUGIN_PATH . 'admin/includes/wgm_messages_viewer.php'; ?>
		<div class="wgm_settings_tabs" id="wgm_general_settings" style="display: block;padding: 20px 25px 25px 25px;">
			<div class="wpgmapembed_get_api_key">
				<h2><?php esc_html_e('API Key and License Information', 'gmap-embed'); ?></h2>
				<hr />
				<table class="form-table" role="presentation">

					<tbody>
						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
							<input type="hidden" name="action" value="wgm_save_api_key" />
							<?php wp_nonce_field('wgm_settings_api_key_update', '_wp_nonce'); ?>
							<tr>
								<th scope="row">
									<label for="wpgmapembed_key">
										<?php esc_html_e('Enter API Key: ', 'gmap-embed'); ?>
									</label>
								</th>
								<td scope="row">
									<input type="text" name="wpgmapembed_key"
										value="<?php echo esc_html(get_option('wpgmap_api_key')); ?>" size="45"
										class="regular-text" style="width:100%" id="wpgmapembed_key" />
									<p class="description" id="tagline-description" style="font-style: italic;">
										<?php esc_html_e('The API key may take up to 5 minutes to take effect', 'gmap-embed'); ?>
									</p>
								</td>
								<td width="30%" style="vertical-align: top;">
									<button class="button wgm_btn"
										style="padding: 4px 10px;font-size: 11px;margin-right:5px;width: auto;"><i
											class="dashicons dashicons-update-alt" style="line-height: 23px;"></i>
										<?php esc_html_e('Update', 'gmap-embed'); ?>
									</button>

									<a style="margin-left: 5px;" title="Go to Quick Setup Wizard to get an API key"
										href="
					<?php echo esc_url(admin_url() . 'admin.php?page=wgm_setup_wizard'); ?>"
										class="button media-button button-default button-large"><i
											class="dashicons dashicons-controls-forward" style="line-height: 30px;"></i>
										<?php esc_html_e('Go to Quick Setup', 'gmap-embed'); ?>
									</a>
								</td>
							</tr>
						</form>

						

						<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
							<input type="hidden" name="action" value="wgm_save_license" />
							<?php wp_nonce_field('wgm_settings_lc_key_update', '_wp_nonce'); ?>
							<tr>
								<th scope="row">
									<label for="wpgmapembed_license">
										<?php esc_html_e('License Key: ', 'gmap-embed'); ?>
									</label>
								</th>
								<td scope="row">
									<input type="text" name="wpgmapembed_license"
										value="<?php echo esc_html(get_option('wpgmapembed_license')); ?>" size="45"
										class="regular-text" style="width:100%" id="wpgmapembed_license" />
									<p class="description" id="tagline-description" style="font-style: italic;">
										<?php esc_html_e('After payment you will get an email with license key', 'gmap-embed'); ?>
									</p>
								</td>
								<td width="30%" style="vertical-align: top;">
									<button class="button wgm_btn"
										style="padding: 4px 10px;font-size: 11px;margin-right:5px;width: auto;"><i
											class="dashicons dashicons-yes-alt" style="line-height: 23px;"></i>
										<?php esc_html_e('Validate', 'gmap-embed'); ?>
									</button>

									<?php
									if (strlen(trim(get_option('wpgmapembed_license'))) !== 32) {
										?>
										<a target="_blank"
											href="<?php echo esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=settings-license-tab'); ?>"
											class="button media-button button-default button-large"><?php esc_html_e('GET LICENSE KEY', 'gmap-embed'); ?></a>
										<?php
									}
									?>
								</td>
							</tr>
						</form>
					</tbody>
				</table>
			</div>
			<div data-columns="8">
				<form method="POST" action="options.php">
					<div class="wpgmap_lng_custom_script_settings">
						<?php
						settings_fields('wpgmap_general_settings');
						do_settings_sections('gmap-embed-settings-page-ls');
						do_settings_sections('gmap-embed-settings-page-cs');
						do_settings_sections('gmap-embed-general-settings');
						submit_button();
						?>
					</div>
				</form>
			</div>
		</div>
		<div class="wgm_settings_tabs" id="wgm_advanced_settings" style="display: none;padding: 20px 25px 25px 25px;">
			<form method="POST" action="options.php">
				<div class="wpgmap_lng_custom_script_settings">
					<?php
					settings_fields('wgm_advance_settings');
					do_settings_sections('wgm_advance_settings-page');
					submit_button();
					?>
				</div>
			</form>
		</div>
		<div class="wgm_settings_tabs" id="wgm_marker_settings" style="display: none;padding: 20px 25px 25px 25px;">
			<form method="POST" action="options.php">
				<div class="wpgmap_lng_custom_script_settings">
					<?php
					settings_fields('wgm_marker_settings');
					do_settings_sections('wgm_marker_settings-page');
					submit_button();
					?>
				</div>
			</form>
		</div>

		<div class="wgm_settings_tabs" id="wgm_export_settings" style="display: none; padding: 20px 25px 25px 25px; position: relative;">
			<?php if (!_wgm_is_premium()) : ?>
				<!-- Pro Upgrade Banner for Export -->
				<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-left: 5px solid #ffd700; position: relative; z-index: 100;">
					<div style="display: flex; align-items: center; gap: 15px;">
						<span class="dashicons dashicons-star-filled" style="font-size: 28px; color: #ffd700; width: 28px; height: 28px;"></span>
						<div style="flex: 1;">
							<h3 style="margin: 0 0 5px 0; color: white; font-size: 16px;"><?php esc_html_e('Unlock Export Feature (Premium)', 'gmap-embed'); ?></h3>
							<p style="margin: 0; font-size: 13px; opacity: 0.95; line-height: 1.4;">
								<?php esc_html_e('Export your maps and markers to JSON or CSV/Excel for backups or migration. Upgrade now to unlock this feature.', 'gmap-embed'); ?>
							</p>
						</div>
						<a href="<?php echo esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=settings-export-tab'); ?>" target="_blank" class="button" style="background: #ffd700; color: #764ba2; border: none; padding: 8px 16px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.2); height: auto; border-radius: 4px;"><?php esc_html_e('Upgrade Now', 'gmap-embed'); ?></a>
					</div>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php?action=wgm_export')); ?>" class="<?php echo !_wgm_is_premium() ? 'wgm-locked-feature' : ''; ?>">
				<?php wp_nonce_field('wgm_export_nonce', '_wgm_export_nonce'); ?>
				<!-- removed hidden wgm_export_action input because admin-post action handles it -->

				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><?php esc_html_e('Export format', 'gmap-embed'); ?></th>
							<td>
								<?php
								// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Non-sensitive initialization of view variable.
								$gmap_embed_admin_export_type = isset($_REQUEST['wgm_export_type']) ? sanitize_text_field(wp_unslash($_REQUEST['wgm_export_type'])) : 'csv';
								?>
								<select name="wgm_export_type" id="wgm_export_type" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?>>
									<option value="csv" <?php selected($gmap_embed_admin_export_type, 'csv'); ?>>CSV</option>
									<option value="json" <?php selected($gmap_embed_admin_export_type, 'json'); ?>>JSON
									</option>
								</select>
								<p class="description">
									<?php esc_html_e('Choose the file format to download.', 'gmap-embed'); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e('Data type', 'gmap-embed'); ?></th>
							<td>
								<?php
								// Determine defaults / incoming selections
								// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Non-sensitive initialization of view variable.
								$gmap_embed_admin_csv_choice = isset($_REQUEST['wgm_export_data']) ? sanitize_text_field(wp_unslash($_REQUEST['wgm_export_data'])) : 'markers';
								// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Non-sensitive initialization of view variable.
								$gmap_embed_admin_json_choices = isset($_REQUEST['wgm_export_data_types']) && is_array($_REQUEST['wgm_export_data_types']) ? array_map('sanitize_text_field', wp_unslash($_REQUEST['wgm_export_data_types'])) : array('markers');
								// Available options
								$gmap_embed_admin_export_options = array(
									'markers' => __('Markers', 'gmap-embed'),
									'categories' => __('Categories', 'gmap-embed'),
								);
								?>
								<!-- CSV / single-select control -->
								<div id="wgm_export_data_csv" style="display: none;">
									<select name="wgm_export_data_type" id="wgm_export_data" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?>>
										<?php foreach ($gmap_embed_admin_export_options as $gmap_embed_admin_val => $gmap_embed_admin_label): ?>
											<option value="<?php echo esc_attr($gmap_embed_admin_val); ?>" <?php selected($gmap_embed_admin_csv_choice, $gmap_embed_admin_val); ?>>
												<?php echo esc_html($gmap_embed_admin_label); ?>
											</option>
										<?php endforeach; ?>
									</select>
									<p class="description">
										<?php esc_html_e('For CSV/Excel choose a single data type to export.', 'gmap-embed'); ?>
									</p>
								</div>

								<!-- JSON / multi-checkbox control -->
								<div id="wgm_export_data_json" style="display: none;">
									<?php foreach ($gmap_embed_admin_export_options as $gmap_embed_admin_val => $gmap_embed_admin_label): ?>
										<label style="display:block;margin-bottom:4px;">
											<input type="checkbox" name="wgm_export_data_types[]"
												value="<?php echo esc_attr($gmap_embed_admin_val); ?>" <?php checked(in_array($gmap_embed_admin_val, $gmap_embed_admin_json_choices, true)); ?> <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?> />
											<?php echo esc_html($gmap_embed_admin_label); ?>
										</label>
									<?php endforeach; ?>
									<p class="description">
										<?php esc_html_e('For JSON you may select one or more data types to include.', 'gmap-embed'); ?>
									</p>
								</div>

							</td>
						</tr>
						<tr class="wgm_maps_select_row">
							<th scope="row"><?php esc_html_e('Select maps', 'gmap-embed'); ?></th>
							<td>
								<?php
								// Try to list maps for selection (best-effort)
								$gmap_embed_admin_select_maps = array();
								$gmap_embed_admin_post_type = 'wpgmapembed';
								$gmap_embed_admin_found = get_posts(array('post_type' => $gmap_embed_admin_post_type, 'post_status' => 'any', 'numberposts' => -1));
								if (!empty($gmap_embed_admin_found)) {
									foreach ($gmap_embed_admin_found as $gmap_embed_admin_p) {
										// prefer stored meta title if present, otherwise post_title
										$gmap_embed_admin_title = get_post_meta($gmap_embed_admin_p->ID, 'wpgmap_title', true);
										if (empty($gmap_embed_admin_title)) {
											$gmap_embed_admin_title = $gmap_embed_admin_p->post_title;
										}
										$gmap_embed_admin_select_maps[$gmap_embed_admin_p->ID] = $gmap_embed_admin_title;
									}
								}
								if (!empty($gmap_embed_admin_select_maps)) {
									// Top controls
									?>
									<div style="margin-bottom:6px;">
										<input type="text" id="wgm_map_search"
											placeholder="<?php esc_attr_e('Search maps...', 'gmap-embed'); ?>"
											style="width:300px;padding:6px;margin-right:8px;" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?> />
										<button type="button" id="wgm_select_all"
											class="button" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?>><?php esc_html_e('Select All', 'gmap-embed'); ?></button>
										<button type="button" id="wgm_select_none"
											class="button" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?>><?php esc_html_e('Select None', 'gmap-embed'); ?></button>
									</div>

									<div class="wgm-map-list"
										style="border:1px solid #ddd;padding:8px;max-height:260px;overflow:auto;background:#fff;">
										<?php
										foreach ($gmap_embed_admin_select_maps as $gmap_embed_admin_id => $gmap_embed_admin_title_val) {
											$gmap_embed_admin_escaped_id = esc_attr($gmap_embed_admin_id);
											$gmap_embed_admin_escaped_title = esc_html($gmap_embed_admin_title_val);
											echo '<div class="wgm-map-item" style="padding:4px 2px;border-bottom:1px solid #f1f1f1;">';
											echo '<label style="cursor:pointer;"><input type="checkbox" name="wgm_maps[]" value="' . esc_attr($gmap_embed_admin_id) . '" style="margin-right:8px;" ' . (!_wgm_is_premium() ? 'disabled="disabled"' : '') . ' />' . esc_html($gmap_embed_admin_title_val) . '</label>';
											echo '</div>';
										}
										?>
									</div>

									<!-- Bottom controls for convenience -->
									<div style="margin-top:6px;">
										<button type="button" id="wgm_select_all_bottom"
											class="button" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?>><?php esc_html_e('Select All', 'gmap-embed'); ?></button>
										<button type="button" id="wgm_select_none_bottom"
											class="button" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?>><?php esc_html_e('Select None', 'gmap-embed'); ?></button>
									</div>


									<?php
								} else {
									echo '<em>' . esc_html__('No maps found to select. Export "All maps" instead or create maps first.', 'gmap-embed') . '</em>';
								}
								?>
							</td>
						</tr>



						<tr>
							<th scope="row"></th>
							<td>
								<button type="submit"
									class="button button-primary" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?>><?php esc_html_e('Export', 'gmap-embed'); ?></button>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
			<?php if (!_wgm_is_premium()) : ?>
				<div class="wgm-lock-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.2); backdrop-filter: blur(1.1px); border-radius: inherit; z-index: 10;">
					<span class="dashicons dashicons-lock" style="font-size: 50px; color: #764ba2; width: 50px; height: 50px; opacity: 0.6;"></span>
				</div>
			<?php endif; ?>
		</div>
		<div class="wgm_settings_tabs" id="wgm_import_settings" style="display: none; padding: 20px 25px 25px 25px; position: relative;">
			<h2><?php esc_html_e('Import', 'gmap-embed'); ?></h2>
			
			<?php if (!_wgm_is_premium()) : ?>
				<!-- Pro Upgrade Banner for Import -->
				<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-left: 5px solid #ffd700; position: relative; z-index: 100;">
					<div style="display: flex; align-items: center; gap: 15px;">
						<span class="dashicons dashicons-star-filled" style="font-size: 28px; color: #ffd700; width: 28px; height: 28px;"></span>
						<div style="flex: 1;">
							<h3 style="margin: 0 0 5px 0; color: white; font-size: 16px;"><?php esc_html_e('Unlock Import Feature (Premium)', 'gmap-embed'); ?></h3>
							<p style="margin: 0; font-size: 13px; opacity: 0.95; line-height: 1.4;">
								<?php esc_html_e('Easily import markers and maps from other plugins or your own backups via JSON or CSV. Upgrade now to unlock this feature.', 'gmap-embed'); ?>
							</p>
						</div>
						<a href="<?php echo esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=settings-import-tab'); ?>" target="_blank" class="button" style="background: #ffd700; color: #764ba2; border: none; padding: 8px 16px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.2); height: auto; border-radius: 4px;"><?php esc_html_e('Upgrade Now', 'gmap-embed'); ?></a>
					</div>
				</div>
			<?php endif; ?>

			<p class="description">
				<?php esc_html_e('Import maps or markers from a JSON or CSV file. Choose how to merge data and select a target map if applicable.', 'gmap-embed'); ?>
			</p>

			<?php
			?>

			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php?action=wgm_import')); ?>"
				enctype="multipart/form-data" class="<?php echo !_wgm_is_premium() ? 'wgm-locked-feature' : ''; ?>">
				<?php wp_nonce_field('wgm_import_nonce', '_wgm_import_nonce'); ?>

				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><?php esc_html_e('File type', 'gmap-embed'); ?></th>
							<td>
								<select name="wgm_import_type" id="wgm_import_type" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?>>
									<option value=""><?php esc_html_e('-- Select --', 'gmap-embed'); ?></option>
									<option value="json">JSON</option>
									<option value="csv">CSV</option>
								</select>
								<p class="description">
									<?php esc_html_e('Select the format of the file you will upload.', 'gmap-embed'); ?>
								</p>
							</td>
						</tr>

						<tr id="wgm_import_data_type_row" style="display:none;">
							<th scope="row"><?php esc_html_e('CSV data type', 'gmap-embed'); ?></th>
							<td>
								<select name="wgm_import_data_type" id="wgm_import_data_type" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?>>
									<option value=""><?php esc_html_e('-- Select --', 'gmap-embed'); ?></option>
									<option value="markers"><?php esc_html_e('Markers', 'gmap-embed'); ?></option>
									<option value="categories"><?php esc_html_e('Categories', 'gmap-embed'); ?></option>
								</select>
								<p class="description">
									<?php esc_html_e('When importing CSV, choose whether the file contains maps, markers, or categories.', 'gmap-embed'); ?>
								</p>
							</td>
						</tr>

						<tr id="wgm_upload_row" style="display:none;">
							<th scope="row"><?php esc_html_e('Upload file', 'gmap-embed'); ?></th>
							<td>
								<input type="file" name="wgm_import_file" id="wgm_import_file"
									accept=".json,text/json,.csv,text/csv" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?> />
								<p class="description">
									<?php esc_html_e('Choose a .json or .csv file exported from this plugin (or compatible format). Files larger than your PHP upload limit will fail.', 'gmap-embed'); ?>
								</p>
								<p class="description" id="wgm_sample_link_container" style="display:none;">
									<!-- Dynamic sample link will be inserted here by JS -->
								</p>
								<!-- Preview and mapping UI -->
								<input type="hidden" name="wgm_import_mapping" id="wgm_import_mapping" value="" />
								<div id="wgm_import_preview" style="margin-top:12px; width:0; min-width:100%; overflow-x:auto;"></div>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e('Import mode', 'gmap-embed'); ?></th>
							<td>
								<label><input type="radio" name="wgm_import_mode" value="merge" checked <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?> />
									<?php esc_html_e('Merge with existing', 'gmap-embed'); ?></label><br />
								<label><input type="radio" name="wgm_import_mode" value="replace" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?> />
									<?php esc_html_e('Replace existing (destructive)', 'gmap-embed'); ?></label>
								<p class="description">
									<?php esc_html_e('Merge will add records and update where applicable. Replace will remove existing items of the selected type first.', 'gmap-embed'); ?>
								</p>
							</td>
						</tr>

						<tr id="wgm_import_target_row">
							<th scope="row"><?php esc_html_e('Target map', 'gmap-embed'); ?></th>
							<td>
								<label style="display:block;"><input type="radio" name="wgm_import_target" value="new"
										checked <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?> />
									<?php esc_html_e('Create new map from import (when applicable)', 'gmap-embed'); ?></label>
								<label style="display:block;"><input type="radio" name="wgm_import_target"
										value="existing" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?> />
									<?php esc_html_e('Import into an existing map', 'gmap-embed'); ?></label>

								<div id="wgm_import_existing_maps"
									style="margin-top:8px;display:none;border:1px solid #eee;padding:8px;max-height:200px;overflow:auto;background:#fff;">
									<?php
									$gmap_embed_admin_maps_for_import = get_posts(array(
										'post_type' => 'wpgmapembed',
										'post_status' => 'any',
										'numberposts' => -1,
									));
									if (!empty($gmap_embed_admin_maps_for_import)) {
										foreach ($gmap_embed_admin_maps_for_import as $gmap_embed_admin_m) {
											$gmap_embed_admin_label = get_post_meta($gmap_embed_admin_m->ID, 'wpgmap_title', true);
											if (empty($gmap_embed_admin_label)) {
												$gmap_embed_admin_label = $gmap_embed_admin_m->post_title;
											}
											echo '<label style="display:block;padding:4px 0;"><input type="radio" name="wgm_target_map_id" value="' . esc_attr($gmap_embed_admin_m->ID) . '" ' . (!_wgm_is_premium() ? 'disabled="disabled"' : '') . ' /> ' . esc_html($gmap_embed_admin_label) . '</label>';
										}
									} else {
										echo '<em>' . esc_html__('No existing maps found. A new map will be created.', 'gmap-embed') . '</em>';
									}
									?>
								</div>
								<p class="description">
									<?php esc_html_e('Choose whether to create a new map or import into one of your existing maps (if applicable to the file).', 'gmap-embed'); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e('Preview & options', 'gmap-embed'); ?></th>
							<td>
								<label><input type="checkbox" name="wgm_import_dry_run" value="1" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?> />
									<?php esc_html_e('Dry run (validate file, do not write)', 'gmap-embed'); ?></label>
								<p class="description">
									<?php esc_html_e('When enabled the import will be validated and a report returned instead of making changes.', 'gmap-embed'); ?>
								</p>
							</td>
						</tr>

						<tr>
							<td>
								<button type="submit"
									class="button button-primary" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?>><?php esc_html_e('Import', 'gmap-embed'); ?></button>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
			<?php if (!_wgm_is_premium()) : ?>
				<div class="wgm-lock-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.2); backdrop-filter: blur(1.1px); border-radius: inherit; z-index: 10;">
					<span class="dashicons dashicons-lock" style="font-size: 50px; color: #764ba2; width: 50px; height: 50px; opacity: 0.6;"></span>
				</div>
			<?php endif; ?>

		</div>
	</div>
</div>