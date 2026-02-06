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
			esc_html($this->plugin_name),
			esc_html($this->plugin_name),
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
			esc_html__('All Maps', 'gmap-embed'),
			esc_html__('All Maps', 'gmap-embed'),
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
				esc_html__('Add new Map', 'gmap-embed'),
				esc_html__('Add New', 'gmap-embed'),
				$this->capability,
				'wpgmapembed-new',
				array(
					$this,
					'srm_gmap_new',
				),
				1
			);
		} else {
			if (
				isset($_GET['page']) &&
				sanitize_key(wp_unslash($_GET['page'])) === 'wpgmapembed-new'
			) {
				// If a nonce is present, verify it. If invalid, block the request.
				// If no nonce is present, this is a simple redirect and no further check is required.
				if ( ! empty( $_REQUEST['_wpnonce'] ) ) {
					$nonce = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );
					if ( ! wp_verify_nonce( $nonce, 'wgm_add_new_map' ) ) {
						wp_die( esc_html__( 'Security check failed.', 'gmap-embed' ) );
					}
				}

				// If the user tries to access the "Add New" page without permission, redirect them to the main page
				$redirect_url = esc_url_raw(
					add_query_arg(
						array( 'page' => 'wpgmapembed' ),
						admin_url( 'admin.php' )
					)
				);
				wp_safe_redirect( $redirect_url );
				exit;
			}
		}

		add_submenu_page(
			'wpgmapembed',
			esc_html__('Categories', 'gmap-embed'),
			esc_html__('Categories', 'gmap-embed'),
			$this->capability,
			'wpgmapembed-categories',
			array(
				$this,
				'wgm_categories_page',
			),
			2
		);

		// setup wizard menu
		add_submenu_page(
			'wpgmapembed',
			esc_html__('Quick Setup', 'gmap-embed'),
			esc_html__('Quick Setup', 'gmap-embed'),
			$this->capability,
			'wgm_setup_wizard',
			array(
				$this,
				'wpgmap_setup_wizard',
			),
			3
		);


		add_submenu_page(
			'wpgmapembed',
			esc_html__('Support', 'gmap-embed'),
			esc_html__('Support', 'gmap-embed'),
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
			esc_html__('Settings', 'gmap-embed'),
			esc_html__('Settings', 'gmap-embed'),
			$this->capability,
			'wpgmapembed-settings',
			array(
				$this,
				'wgm_settings',
			),
			4
		);
		if (!_wgm_is_premium()) {
			add_submenu_page(
				'wpgmapembed',
				// Escape HTML and URLs for security
				// phpscs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
				'<img draggable="false" role="img" class="emoji" alt="⭐" src="' . esc_url('https://s.w.org/images/core/emoji/13.0.1/svg/2b50.svg') . '"> ' . esc_html__('Upgrade to Pro', 'gmap-embed'),
				// phpscs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
				'<span style="color:yellow"><img draggable="false" role="img" class="emoji" alt="⭐" src="' . esc_url('https://s.w.org/images/core/emoji/13.0.1/svg/2b50.svg') . '">  ' . esc_html__('Upgrade to Pro', 'gmap-embed') . '</span>',
				$this->capability,
				esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=admin-menu-link'),
				false,
				5
			);
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
		if (
			isset($_GET['tag']) &&
			sanitize_text_field(wp_unslash($_GET['tag'])) === 'edit'
		) {
			if (
				isset($_GET['wgm_map_create_nonce']) &&
				wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['wgm_map_create_nonce'])), 'wgm_create_map')
			) {
				require WGM_PLUGIN_PATH . 'admin/includes/wpgmap_edit.php';
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
		} else {
			$this->render('admin/map-list', [], true);
		}
	}

	/**
	 * Google Map Embed Mail Page
	 */
	public function srm_gmap_new()
	{
		// Check if the user has the required capability to access this page
		if (!current_user_can($this->capability)) {
			wp_die(esc_html__('Unauthorized access. You do not have permission to view this page.', 'gmap-embed'));
		}

		// Check if the user can add a new map
		if (!_wgm_can_add_new_map()) {
			echo '<div class="message" style="margin-top:40px;">
			<div id="setting-error-settings_updated" class="settings-error notice is-dismissible" style="border-left-color:red;">
				<p style="font-size:15px;">
					<strong>';
			echo wp_kses(
				sprintf(
					/* translators: %s: premium version link */
					__('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to <b>Create Unlimited Maps</b>.', 'gmap-embed'),
					esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=admin-menu-add-new-map')
				),
				array(
					'a' => array(
						'href' => array(),
						'target' => array(),
						'rel' => array(),
					),
					'b' => array(),
					'strong' => array(),
				)
			);
			echo ' <a href="' . esc_url(admin_url('admin.php?page=wpgmapembed')) . '" style="margin-left:15px;">' . esc_html__('Back to Plugin Home', 'gmap-embed') . '</a>';
			echo '</strong></p></div></div>';
			exit;
		}

		// Initialize new map
		$map_id = $this->initiate_new_map();

		if (!is_numeric($map_id)) {
			wp_die(esc_html__('Invalid map ID.', 'gmap-embed'));
		}

		$nonce = wp_create_nonce('wgm_create_map');
		$redirect_url = add_query_arg(
			array(
				'page' => 'wpgmapembed',
				'tag' => 'edit',
				'id' => intval($map_id),
				'wgm_map_create_nonce' => esc_attr($nonce),
			),
			admin_url('admin.php')
		);

		echo '<script>window.location = ' . wp_json_encode($redirect_url) . ';</script>';
		exit;
	}

	public function wgm_settings()
	{
		require WGM_PLUGIN_PATH . 'admin/includes/wpgmap_settings.php';
	}

	public function wgm_categories_page()
	{
		require_once WGM_PLUGIN_PATH . 'admin/includes/categories.php';
	}
}
