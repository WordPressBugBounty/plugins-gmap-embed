<?php
defined( 'ABSPATH' ) || exit;
if (isset($_GET['message']) && isset($_GET['wgm_settings_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['wgm_settings_nonce'])), 'wgm_settings') && !isset($_GET['settings-updated'])) {
	?>
	<div class="message">
		<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
			<p>
				<strong>
					<?php
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
					$gmap_embed_allowed_html = [
						'a' => [
							'class' => [],
							'id' => [],
							'title' => [],
							'href' => [],
							'target' => [],
						],
						'i' => [
							'style' => []
						],
						'span' => [
							'class' => [],
							'style' => []
						],

					];
					$gmap_embed_message_status = isset($_GET['message']) ? intval(sanitize_text_field(wp_unslash($_GET['message']))) : 0;
					switch ($gmap_embed_message_status) {
						case 1:
							// translators: %s: YouTube tutorial URL.
							echo wp_kses(
								sprintf(
									// translators: %s: YouTube tutorial URL.
									__('Map has been created Successfully. <a href="%s" target="_blank"> See How to use &gt;&gt;</a>', 'gmap-embed'),
									esc_url('https://youtu.be/ErRy5lqTPjY?t=255')
								),
								$gmap_embed_allowed_html
							);
							break;
						case 3:
							// translators: %s: Add New menu admin URL.
							echo wp_kses(
								// translators: %s: Add New menu admin URL.
								sprintf(
									// translators: %s: Add New menu admin URL.
									__('API key updated Successfully, Please click on <a href="%s"><i style="color: green;">Add New</i></a> menu to add new map.', 'gmap-embed'),
									esc_url(admin_url('admin.php?page=wpgmapembed-new'))
								),
								$gmap_embed_allowed_html
							);
							break;
						case 4:
							echo esc_html__('License key updated successfully. Now you can enjoy premium features!', 'gmap-embed');
							break;
						case 5:
							$gmap_embed_api_base_url = 'https://wpgooglemap.com';
							echo wp_kses(
								sprintf(
									// translators: %s: License pricing URL.
									__('Invalid license key, please get your license key. <a target="_blank" href="%s">Get License Key</a>', 'gmap-embed'),
									esc_url($gmap_embed_api_base_url . '/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=settings-license-error-msg')
								),
								$gmap_embed_allowed_html
							);
							break;
						case 6:
							echo esc_html__('License key is required.', 'gmap-embed');
							break;
						case -1:
							echo esc_html__('Map Deleted Successfully.', 'gmap-embed');
							break;
						default:
							// Handle legacy wgm_message_raw if still passed (fallback)
							if (isset($gmap_embed_message_raw)) {
								echo wp_kses($gmap_embed_message_raw, $gmap_embed_allowed_html);
							}
							break;
					}
					?>
				</strong>
			</p>
			<button type="button" class="notice-dismiss"><span
					class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'gmap-embed'); ?></span>
			</button>
		</div>
	</div>
	<?php
}

// Handle Import Messages
if (isset($_GET['wgm_import'])) {
	$gmap_embed_import_status = sanitize_text_field(wp_unslash($_GET['wgm_import']));
	$gmap_embed_import_type = isset($_GET['type']) ? sanitize_text_field(wp_unslash($_GET['type'])) : '';
	$gmap_embed_import_count = isset($_GET['count']) ? intval($_GET['count']) : 0;
	$gmap_embed_import_err_code = isset($_GET['error_code']) ? sanitize_text_field(wp_unslash($_GET['error_code'])) : '';
	$gmap_embed_import_err_msg = isset($_GET['error_msg']) ? sanitize_text_field(wp_unslash($_GET['error_msg'])) : '';

	$gmap_embed_notice_class = ($gmap_embed_import_status === 'success' || $gmap_embed_import_status === 'dryrun') ? 'updated' : 'error';
	?>
	<div class="message">
		<div class="notice notice-<?php echo esc_attr($gmap_embed_notice_class); ?> is-dismissible">
			<p>
				<strong>
					<?php
					if ($gmap_embed_import_status === 'success') {
						if ($gmap_embed_import_type === 'categories') {
							/* translators: %d: categories count */
							printf(esc_html__('%d categories imported successfully.', 'gmap-embed'), intval($gmap_embed_import_count));
						} elseif ($gmap_embed_import_type === 'markers') {
							/* translators: %d: markers count */
							printf(esc_html__('%d markers imported successfully.', 'gmap-embed'), intval($gmap_embed_import_count));
						} else {
							esc_html_e('Data imported successfully.', 'gmap-embed');
						}
					} elseif ($gmap_embed_import_status === 'dryrun') {
						if ($gmap_embed_import_type === 'categories') {
							/* translators: %d: categories count */
							printf(esc_html__('Simulation: %d categories would be imported.', 'gmap-embed'), intval($gmap_embed_import_count));
						} elseif ($gmap_embed_import_type === 'markers') {
							/* translators: %d: markers count */
							printf(esc_html__('Simulation: %d markers would be imported.', 'gmap-embed'), intval($gmap_embed_import_count));
						} else {
							// JSON dryrun case
							$gmap_embed_m_sim = isset($_GET['maps_simulated']) ? intval($_GET['maps_simulated']) : 0;
							$gmap_embed_mk_sim = isset($_GET['markers_simulated']) ? intval($_GET['markers_simulated']) : 0;
							/* translators: 1: maps count, 2: markers count */
							printf(esc_html__('Simulation: %1$d maps and %2$d markers would be imported.', 'gmap-embed'), intval($gmap_embed_m_sim), intval($gmap_embed_mk_sim));
						}
					} elseif ($gmap_embed_import_status === 'error') {
						/* translators: %s: error message */
						printf(esc_html__('Import failed: %s', 'gmap-embed'), esc_html($gmap_embed_import_err_msg ?: $gmap_embed_import_err_code));
					}
					?>
				</strong>
			</p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'gmap-embed'); ?></span></button>
		</div>
	</div>
	<?php
}
?>