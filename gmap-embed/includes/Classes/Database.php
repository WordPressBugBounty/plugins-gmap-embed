<?php

namespace WGMSRM\Classes;

class Database {

	private $_version = '1.2';

	public function __construct() {
		// Do not escape here, just sanitize and validate for logic
		$existing_db_version = get_option('_wgm_db_version', 'none');
		$existing_db_version = is_string($existing_db_version) ? sanitize_text_field($existing_db_version) : 'none';
		if ($existing_db_version === 'none' || version_compare($existing_db_version, $this->_version, '<')) {
			$this->install();
		}
	}

	public function install() {
		$this->createMarkersTable();
		$this->createMarkersIconTable();
		$this->createCategoriesTable();
		update_option('_wgm_db_version', $this->_version);
	}

	public function createMarkersTable() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		if (!function_exists('dbDelta')) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}
		$schema = "CREATE TABLE `{$wpdb->prefix}wgm_markers` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`map_id` int(11) NOT NULL,
			`marker_name` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
			`marker_desc` text COLLATE utf8_unicode_ci DEFAULT NULL,
			`marker_image` text COLLATE utf8_unicode_ci DEFAULT NULL,
			`icon` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
			`address` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
			`lat_lng` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
			`have_marker_link` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
			`marker_link` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
			`marker_link_new_tab` tinyint(4) DEFAULT NULL,
			`animation` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
			`category_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0',
			`show_desc_by_default` tinyint(4) DEFAULT NULL,
			`created_at` datetime DEFAULT NULL,
			`created_by` bigint(20) unsigned NOT NULL,
			`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			`updated_by` bigint(20) unsigned NOT NULL,
			PRIMARY KEY (`id`)
		) $charset_collate;";
		// Use dbDelta for schema changes (WordPress best practice)
		dbDelta($schema);
	}

	public function createMarkersIconTable() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		if (!function_exists('dbDelta')) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}
		$schema = "CREATE TABLE `{$wpdb->prefix}wgm_icons` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`type` varchar(100) DEFAULT NULL,
			`title` varchar(500) DEFAULT NULL,
			`desc` varchar(500) DEFAULT NULL,
			`file_name` varchar(500) DEFAULT NULL,
			PRIMARY KEY (`id`)
		) $charset_collate;";
		dbDelta($schema);
	}

	public function createCategoriesTable() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		if (!function_exists('dbDelta')) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}
		$schema = "CREATE TABLE `{$wpdb->prefix}wgm_categories` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			`icon` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
			`parent_id` int(11) unsigned DEFAULT 0,
			`created_at` datetime DEFAULT NULL,
			`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		) $charset_collate;";
		dbDelta($schema);
	}
}
