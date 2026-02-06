<?php

namespace WGMSRM\Traits;

use WGMSRM\Classes\Migration;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Trait InitActions: Init action hooks defined here
 */
trait AdminInitActions
{

	public function do_admin_init_actions()
	{
		// Security: Only allow admins to trigger migrations or admin actions
		// if (!current_user_can('manage_options')) {
		// 	wp_die(esc_html__('You do not have permission to access this page.', 'gmap-embed'));
		// }
	}
}
