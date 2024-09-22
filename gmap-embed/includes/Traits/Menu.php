<?php

namespace WGMSRM\Traits;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Trait Menu
 */
trait Menu
{

	/**
	 * To create menu in admin panel
	 */
	public function gmap_create_menu()
	{
		// create new top-level menu
		add_menu_page(
			$this->plugin_name,
			$this->plugin_name,
			$this->capability,
			'wpgmapembed',
			array(
				$this,
				'srm_gmap_main',
			),
			'dashicons-location',
			11
		);

		add_submenu_page(
			'wpgmapembed',
			__('All Maps', 'gmap-embed'),
			__('All Maps', 'gmap-embed'),
			$this->capability,
			'wpgmapembed',
			array(
				$this,
				'srm_gmap_main',
			),
			0
		);

		// to create sub menu
		if (_wgm_can_add_new_map()) {
			add_submenu_page(
				'wpgmapembed',
				__('Add new Map', 'gmap-embed'),
				__('Add New', 'gmap-embed'),
				$this->capability,
				'wpgmapembed-new',
				array(
					$this,
					'srm_gmap_new',
				),
				1
			);
		}

		// setup wizard menu
		add_submenu_page(
			'wpgmapembed',
			__('Quick Setup', 'gmap-embed'),
			__('Quick Setup', 'gmap-embed'),
			$this->capability,
			'wgm_setup_wizard',
			array(
				$this,
				'wpgmap_setup_wizard',
			),
			2
		);

		add_submenu_page(
			'wpgmapembed',
			__('Support', 'gmap-embed'),
			__('Support', 'gmap-embed'),
			$this->capability,
			'wpgmapembed-support',
			array(
				$this,
				'wgm_support',
			),
			3
		);

		add_submenu_page(
			'wpgmapembed',
			__('Settings', 'gmap-embed'),
			__('Settings', 'gmap-embed'),
			$this->capability,
			'wpgmapembed-settings',
			array(
				$this,
				'wgm_settings',
			),
			4
		);
		if (!_wgm_is_premium()) {
			add_submenu_page('wpgmapembed', __('<img draggable="false" role="img" class="emoji" alt="⭐" src="' . esc_url('https://s.w.org/images/core/emoji/13.0.1/svg/2b50.svg') . '"> Upgrade to Pro', 'gmap-embed'), __('<span style="color:yellow"><img draggable="false" role="img" class="emoji" alt="⭐" src="' . esc_url('https://s.w.org/images/core/emoji/13.0.1/svg/2b50.svg') . '">  Upgrade to Pro</span>', 'gmap-embed'), $this->capability, esc_url('https://wpgooglemap.com/pricing?utm_source=admin_menu&utm_medium=admin_link&utm_campaign=menu_get_license'), false, 5);
		}
	}

	public function wgm_support()
	{
		require WGM_PLUGIN_PATH . 'admin/includes/wpgmap_support.php';
	}


	/**
	 * Google Map Embed Mail Page
	 */
	public function srm_gmap_main()
	{
		if (isset($_GET['tag']) && sanitize_text_field(wp_unslash($_GET['tag'])) === 'edit') {
			require WGM_PLUGIN_PATH . 'admin/includes/wpgmap_edit.php';
		} else {
			require WGM_PLUGIN_PATH . 'admin/includes/wpgmap_list.php';
		}
	}

	/**
	 * Google Map Embed Mail Page
	 */
	public function srm_gmap_new()
	{
		require WGM_PLUGIN_PATH . 'admin/includes/wpgmap_create.php';
	}

	public function wgm_settings()
	{
		require WGM_PLUGIN_PATH . 'admin/includes/wpgmap_settings.php';
	}
}
