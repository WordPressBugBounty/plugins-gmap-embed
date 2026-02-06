<?php

namespace WGMSRM\Traits;

use WGMSRM\Classes\Database;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Trait PluginsLoadedActions
 */
trait PluginsLoadedActions
{

	/**
	 * Fires after plugins loaded
	 */
	public function wpgmap_do_after_plugins_loaded()
	{
		// Defensive: Only allow if user has install_plugins capability (for DB changes)
		if (current_user_can('install_plugins')) {
			new Database();
		}
	}
}
