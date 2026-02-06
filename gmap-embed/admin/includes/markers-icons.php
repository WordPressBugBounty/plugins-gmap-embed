<?php defined( 'ABSPATH' ) || exit; ?>
<style>
/* Override ThickBox container padding */
#TB_ajaxContent {
	padding: 0 !important;
	width: 100% !important;
	height: auto !important;
	overflow: visible !important;
}
#TB_window {
	overflow: hidden;
}
.wgm-icon-selector-wrapper {
	padding: 15px;
	background: #fff;
	width: 100%;
	min-height: 100%;
	box-sizing: border-box;
	overflow: hidden;
}
.wgm-icon-search-container {
	margin-bottom: 20px;
	position: relative;
}
.wgm-icon-search-input {
	width: 100%;
	padding: 10px 40px 10px 15px;
	font-size: 14px;
	border: 2px solid #ddd;
	border-radius: 6px;
	box-sizing: border-box;
	transition: border-color 0.3s ease;
}
.wgm-icon-search-input:focus {
	outline: none;
	border-color: #2271b1;
	box-shadow: 0 0 0 1px #2271b1;
}
.wgm-icon-search-clear {
	position: absolute;
	right: 10px;
	top: 50%;
	transform: translateY(-50%);
	background: none;
	border: none;
	font-size: 20px;
	color: #999;
	cursor: pointer;
	padding: 5px 10px;
	line-height: 1;
	display: none;
}
.wgm-icon-search-clear:hover {
	color: #333;
}
.wgm-icon-search-input:not(:placeholder-shown) ~ .wgm-icon-search-clear {
	display: block;
}
.wgm_gmap_embed_marker_icons {
	list-style: none;
	margin: 0;
	padding: 0;
	display: grid;
	grid-template-columns: repeat(9, 1fr);
	gap: 8px;
	max-width: 100%;
}
.wgm_gmap_embed_marker_icons li {
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 6px;
	border: 2px solid #ddd;
	border-radius: 6px;
	cursor: pointer;
	transition: all 0.2s ease;
	background: #fff;
	aspect-ratio: 1;
	box-sizing: border-box;
}
.wgm_gmap_embed_marker_icons li:hover {
	border-color: #2271b1;
	background: #f0f6fc;
	transform: translateY(-2px);
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.wgm_gmap_embed_marker_icons li.wgm-icon-hidden {
	display: none;
}
.wgm_gmap_embed_marker_icons li img {
	max-width: 32px;
	max-height: 32px;
	width: auto;
	height: auto;
	display: block;
}
.wgm-no-results {
	text-align: center;
	padding: 40px 20px;
	color: #666;
	font-size: 14px;
	display: none;
}
.wgm-no-results.wgm-show {
	display: block;
}

/* Responsive breakpoints */
@media (max-width: 600px) {
	.wgm_gmap_embed_marker_icons {
		grid-template-columns: repeat(6, 1fr);
	}
}

@media (max-width: 400px) {
	.wgm_gmap_embed_marker_icons {
		grid-template-columns: repeat(4, 1fr);
	}
}
</style>

<div class="wgm-icon-selector-wrapper">
	<div class="wgm-icon-search-container">
		<input 
			type="text" 
			id="wgm-icon-search" 
			class="wgm-icon-search-input" 
			placeholder="<?php esc_attr_e('Search icons...', 'gmap-embed'); ?>"
			autocomplete="off"
			aria-label="<?php esc_attr_e('Search marker icons', 'gmap-embed'); ?>"
		/>
		<button type="button" class="wgm-icon-search-clear" aria-label="<?php esc_attr_e('Clear search', 'gmap-embed'); ?>">&times;</button>
	</div>
	
	<div class="wgm-no-results" id="wgm-no-results">
		<?php esc_html_e('No icons found matching your search.', 'gmap-embed'); ?>
	</div>
	
	<ul class="wgm_gmap_embed_marker_icons" id="wgm-icon-list">
		<?php
		global $wpdb;
		$gmap_embed_admin_icon_fetch = false;
		$gmap_embed_admin_marker_icons = wp_cache_get('gmap_embed_marker_icons', 'gmap_embed');
		if (false === $gmap_embed_admin_marker_icons) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$gmap_embed_admin_marker_icons = $wpdb->get_results("SELECT type, file_name FROM {$wpdb->prefix}wgm_icons", OBJECT);
			wp_cache_set('gmap_embed_marker_icons', $gmap_embed_admin_marker_icons, 'gmap_embed');
		}

		if (count($gmap_embed_admin_marker_icons) > 0) {
			foreach ($gmap_embed_admin_marker_icons as $gmap_embed_icon_key => $gmap_embed_marker_icon) {
				$gmap_embed_admin_icon_fetch = true;
				$gmap_embed_admin_image_path = $gmap_embed_marker_icon->file_name;
				if ($gmap_embed_marker_icon->type === 'pre_uploaded_icon') {
					$gmap_embed_admin_image_path = plugin_dir_url(__FILE__) . '../assets/images/markers/' . basename($gmap_embed_marker_icon->file_name);
				}
				$gmap_embed_admin_icon_name = basename($gmap_embed_admin_image_path, '.' . pathinfo($gmap_embed_admin_image_path, PATHINFO_EXTENSION));
				?>
				<li data-icon-name="<?php echo esc_attr(strtolower($gmap_embed_admin_icon_name)); ?>">
					<img width="32" height="32" src="<?php echo esc_url($gmap_embed_admin_image_path); ?>"
						onclick="wpgmapChangeCurrentMarkerIcon(this);"
						alt="<?php echo esc_attr($gmap_embed_admin_icon_name); ?>"
						title="<?php echo esc_attr($gmap_embed_admin_icon_name); ?>" />
				</li>
				<?php
			}
		}
		// Load default icons from directory
		$gmap_embed_dir = WGM_ICONS_DIR;
		$gmap_embed_file_display = array('jpg', 'jpeg', 'png', 'gif');

		if (file_exists($gmap_embed_dir) == false) {
			echo '<li style="grid-column: 1/-1; text-align: center; padding: 20px;">';
			echo esc_html__('Directory not found!', 'gmap-embed');
			echo '</li>';
		} else {
			$gmap_embed_admin_icon_fetch = true;
			$gmap_embed_dir_contents = scandir($gmap_embed_dir);
			foreach ($gmap_embed_dir_contents as $gmap_embed_file) {
				$gmap_embed_image_data = explode('.', $gmap_embed_file);
				$gmap_embed_file_type = strtolower(end($gmap_embed_image_data));
					if ('.' !== $gmap_embed_file && '..' !== $gmap_embed_file && true === in_array($gmap_embed_file_type, $gmap_embed_file_display, true)) {
						$gmap_embed_admin_icon_name = $gmap_embed_image_data[0];
						?>
						<li data-icon-name="<?php echo esc_attr(strtolower($gmap_embed_admin_icon_name)); ?>">
							<img onclick="wpgmapChangeCurrentMarkerIcon(this);" 
								alt="<?php echo esc_attr($gmap_embed_admin_icon_name); ?>"
								title="<?php echo esc_attr($gmap_embed_admin_icon_name); ?>"
								src="<?php echo esc_url(WGM_ICONS . $gmap_embed_file); ?>" />
						</li>
					<?php
					}
			}
		}

		if (!$gmap_embed_admin_icon_fetch) {
			echo '<li style="grid-column: 1/-1; text-align: center; padding: 20px;">';
			echo esc_html__('No icon found, please upload icon by clicking the Upload Icon button', 'gmap-embed');
			echo '</li>';
		}
		?>
	</ul>
</div>
