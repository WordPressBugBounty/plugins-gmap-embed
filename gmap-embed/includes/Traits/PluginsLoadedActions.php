<?php

namespace WGMSRM\Traits;

use WGMSRM\Classes\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait PluginsLoadedActions
 */
trait PluginsLoadedActions {

	/**
	 * Fires after plugins loaded
	 */
	public function wpgmap_do_after_plugins_loaded() {
		 new Database();
	}
}
