<?php

namespace WGMSRM\Traits;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Trait ActivationHooks: Do something on plugin activation
 */
trait ActivationHooks
{

	/**
	 * Do after activation
	 *
	 * @param $plugin
	 * @param $network_activation
	 */
	public function wpgmap_do_after_activation($plugin, $network_activation)
	{
		// In case of existing installation
		if (get_option('gmap_embed_activation_time', false) == false) {
			update_option('gmap_embed_activation_time', time());
		}

		// In case of existing installation
		if (get_option('_wgm_enable_direction_form_auto_complete', false) == false) {
			update_option('_wgm_enable_direction_form_auto_complete', 'Y');
		}

		// Validate $plugin value before comparison
		if (is_string($plugin) && $plugin === 'gmap-embed/srm_gmap_embed.php') {
			//wp_redirect( admin_url( 'admin.php?page=wgm_setup_wizard' ) );
			//exit;
		}
	}
}
