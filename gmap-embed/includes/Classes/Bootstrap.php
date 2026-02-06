<?php

namespace WGMSRM\Classes;

use WGMSRM\Traits\ActionLinks;
use WGMSRM\Traits\ActivationHooks;
use WGMSRM\Traits\AdminInitActions;
use WGMSRM\Traits\AssetHandler;
use WGMSRM\Traits\CommonFunctions;
use WGMSRM\Traits\Filters;
use WGMSRM\Traits\InitActions;use WGMSRM\Traits\MapCRUD;
use WGMSRM\Traits\MarkerCRUD;
use WGMSRM\Traits\CategoryCRUD;
use WGMSRM\Traits\MediaButtons;
use WGMSRM\Traits\Menu;
use WGMSRM\Traits\Notice;
use WGMSRM\Traits\PluginsLoadedActions;
use WGMSRM\Traits\Settings;
use WGMSRM\Traits\SetupWizard;
use WGMSRM\Traits\TemplateRenderer;
use WGMSRM\Traits\ImportExport;

if (!defined('ABSPATH')) {
	exit;
}

class Bootstrap
{

	use Settings, MapCRUD, Notice, Menu, AssetHandler, CommonFunctions, ActionLinks, PluginsLoadedActions, ActivationHooks, InitActions, SetupWizard, Filters, MarkerCRUD, CategoryCRUD, AdminInitActions, MediaButtons, TemplateRenderer, ImportExport;

	private static $instance = null;
	private $plugin_name = 'WP Google Map';
	private $plugin_slug = 'gmap-embed';
	public $wpgmap_api_key = '';
	private $config;
	private $capability = 'manage_options';
	// limit import file size (bytes) - 5MB default
	private $max_import_size = 5242880;

	public function __construct()
	{
		$this->config = new Config();

		// Sanitize and validate capability option
		$cap = $this->config->get('_wgm_minimum_role_for_map_edit', 'manage_options');
		$this->capability = $cap;

		// API key
		$this->wpgmap_api_key = $this->config->get_api_key();

		$this->register_hooks();
		$this->load_dependencies();
	}

	/**
	 * Get Config instance.
	 * 
	 * @return Config
	 */
	public function config()
	{
		return $this->config;
	}

	/**
	 * Generating instance
	 *
	 * @return Bootstrap|null
	 */
	public static function instance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register all hooks
	 */
	private function register_hooks()
	{
		add_action('init', array($this, 'do_init_actions'));
		add_action('plugins_loaded', array($this, 'wpgmap_do_after_plugins_loaded'));
		add_action('widgets_init', array($this, 'register_widget'));
		add_action('activated_plugin', array($this, 'wpgmap_do_after_activation'), 10, 2);
		add_action('wp_enqueue_scripts', array($this, 'gmap_front_enqueue_scripts'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_gmap_scripts'));
		add_action('admin_menu', array($this, 'gmap_create_menu'));
		add_action('admin_init', array($this, 'do_admin_init_actions'));
		add_action('admin_init', array($this, 'gmapsrm_settings'));
		add_action('admin_notices', array($this, 'gmap_embed_notice_generate'));
		add_filter('plugin_action_links_gmap-embed/srm_gmap_embed.php', array($this, 'gmap_srm_settings_link'), 10, 4);
		add_action('media_buttons', array($this, 'add_wp_google_map_media_button'));
		add_action('admin_footer', array($this, 'wp_google_map_media_button_content'));
		// Ensure callback is defined early for Google Maps API
		add_action('wp_head', array($this, 'wgm_head_callback_script'), 1);
		add_action('admin_head', array($this, 'wgm_head_callback_script'), 1);
		// Add async attribute safely
		add_filter('script_loader_tag', array($this, 'wgm_add_async_attribute'), 10, 3);
		$this->ajax_hooks();
		$this->post_hooks();

		// Hardening: Centralized POST handlers for settings
		add_action('admin_post_wgm_save_api_key', array($this, 'wgm_save_api_key'));
		add_action('admin_post_wgm_save_license', array($this, 'wgm_save_license'));

		/** To prevent others plugin loading Google Map API(with checking user consent) */
		if (get_option('_wgm_prevent_other_plugin_theme_api_load') === 'Y') {
			add_filter('script_loader_tag', array($this, 'do_prevent_others_google_maps_tag'), 10000000, 3);
		}

		// Validate user capability before allowing admin actions
		add_action('admin_init', function () {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
			if (!empty($page) && (strpos($page, 'wpgmapembed') !== false || $page === 'gmap-embed')) {
				if (!current_user_can($this->capability)) {
					// Escape output for XSS protection
					wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'gmap-embed'));
				}
			}
		});
	}

	private function ajax_hooks()
	{
		// All AJAX handlers should validate nonce and user capability
		$ajax_actions = [
			'wpgmapembed_save_map_data' => 'save_wpgmapembed_data',
			'wpgmapembed_load_map_data' => 'load_wpgmapembed_list',
			'wpgmapembed_popup_load_map_data' => 'load_popup_wpgmapembed_list',
			'wpgmapembed_get_wpgmap_data' => 'get_wpgmapembed_data',
			'wpgmapembed_remove_wpgmap' => 'remove_wpgmapembed_data',
			'wpgmapembed_save_setup_wizard' => 'wpgmap_save_setup_wizard',
			'wgm_get_all_maps' => 'wgm_get_all_maps',
			'wpgmapembed_save_map_markers' => 'save_map_marker',
			'wpgmapembed_update_map_markers' => 'update_map_marker',
			'wpgmapembed_get_marker_icons' => 'get_marker_icons',
			'wpgmapembed_save_marker_icon' => 'save_marker_icon',
			'wpgmapembed_get_markers_by_map_id' => 'get_markers_by_map_id',
			'wpgmapembed_p_get_markers_by_map_id' => 'p_get_markers_by_map_id',
			'wgm_get_markers_by_map_id' => 'wgm_get_markers_by_map_id_for_dt',
			'wpgmapembed_delete_marker' => 'delete_marker',
			'wpgmapembed_get_marker_data_by_marker_id' => 'get_marker_data_by_marker_id',
			'wgm_save_category' => 'save_category',
			'wgm_update_category' => 'update_category',
			'wgm_delete_category' => 'delete_category',
			'wgm_get_categories' => 'get_categories_for_dt',
			'wgm_get_category_data' => 'get_category_data',
		];

		foreach ($ajax_actions as $action => $method) {
			add_action("wp_ajax_{$action}", function () use ($method, $action) {
				$nonce_field = '_wpnonce';
				$nonce_action = $action;

				// Special handling for wpgmapembed_p_get_markers_by_map_id nonce (Cache compatibility)
				if ($action === 'wpgmapembed_p_get_markers_by_map_id') {
					$this->$method();
					return;
				}

				$nonce = isset($_REQUEST[$nonce_field]) ? sanitize_text_field(wp_unslash($_REQUEST[$nonce_field])) : '';
				if (empty($nonce) || !wp_verify_nonce($nonce, $nonce_action)) {
					wp_send_json_error(['message' => esc_html__('Invalid nonce.', 'gmap-embed')], 403);
				}
				if (!current_user_can($this->capability)) {
					wp_send_json_error(['message' => esc_html__('Unauthorized', 'gmap-embed')], 403);
				}
				// call the class method
				$this->$method();
			});
		}

		// Preview import (AJAX) - returns a small sample preview of uploaded content
		add_action('wp_ajax_wgm_import_preview', function () {
			if (!current_user_can($this->capability)) {
				wp_send_json_error(['message' => esc_html__('Unauthorized', 'gmap-embed')], 403);
			}
			// delegate to class method for easier testing
			$this->wgm_import_preview();
		});

		// For nopriv actions, only allow safe ones
		add_action('wp_ajax_nopriv_wpgmapembed_p_get_markers_by_map_id', function () {
			$this->p_get_markers_by_map_id();
		});
	}

	public function post_hooks()
	{
		// Add post hooks if needed
		$this->register_import_export_hooks();


	}

	public function load_dependencies()
	{
		// Define Shortcode.
		$shortcode_file = WGM_PLUGIN_PATH . '/public/includes/shortcodes.php';
		if (file_exists($shortcode_file)) {
			require_once $shortcode_file;
		}
	}

	public function register_widget()
	{
		// Defensive: Only register if class exists
		if (class_exists('WGMSRM\\Classes\\srmgmap_widget')) {
			register_widget('WGMSRM\\Classes\\srmgmap_widget');
		}
	}









}

