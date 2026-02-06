<?php

namespace WGMSRM\Traits;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Trait MediaButtons: for plugins media buttons including
 */
trait MediaButtons
{

	public function add_wp_google_map_media_button()
	{
		// path to my icon
		$img = WGM_PLUGIN_URL . 'gmap_icon_18.png';

		// the id of the container I want to show in the popup
		$container_id = 'wp_gmap_popup_container';

		// our popup's title
		$title = 'Select your desired map to insert into post';

		// append the icon
		$context = "<a class='button  thickbox' title='" . esc_attr($title) . "'
    href='#TB_inline?width=700&height=450&inlineId=" . esc_attr($container_id) . "'>
    " . '<span class="wp-media-buttons-icon" style="background: url(' . esc_url($img) . ') no-repeat; background-position: left bottom;"></span>' . 'WP Google Map</a>';

		$allowed_html = [
			'a' => [
				'class' => [],
				'id' => [],
				'title' => [],
				'href' => [],
			],
			'span' => [
				'class' => [],
				'style' => []
			],

		];
		// Output is escaped using wp_kses, allowed tags and attributes are strictly defined
		echo wp_kses($context, $allowed_html);
	}

	public function wp_google_map_media_button_content()
	{ ?>
		<div id="wp_gmap_popup_container" style="display:none;">
			<!--modal contents-->
			<div id="wgm_all_maps">
				<!---------------------------new map tab-------------->
				<div class="wp-gmap-tab-content active" id="wp-gmap-all">
					<span class="wpgmap_msg_error" style="width:80%;">

					</span>
					<!--all map tab-->
					<div class="wp-gmap-list">
						<a href="<?php echo esc_url(admin_url() . 'admin.php?page=wpgmapembed&amp;tag=new'); ?>"
							data-id="wp-gmap-new" class="media-menu-item"
							style="float:right;"><?php esc_html_e('Create New Map', 'gmap-embed'); ?>
						</a>
						<span class="spinner is-active" style="margin: 0px !important;float:left;"></span>
						<div id="wpgmapembed_list"></div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
