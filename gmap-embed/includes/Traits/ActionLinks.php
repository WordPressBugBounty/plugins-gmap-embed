<?php

namespace WGMSRM\Traits;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Trait ActionLinks: for plugins list action links viewing
 */
trait ActionLinks
{

	/**
	 * Adding Upgrade to pro, rate us and support link
	 *
	 * @param $actions
	 * @return mixed
	 */
	public function gmap_srm_settings_link($actions)
	{
		$links = array();
		if (!_wgm_is_premium()) {
			$links['upgrade-to-pro'] = '<a target="_blank" style="color: #11967A;font-weight:bold;" href="' . esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=plugin-action-link') . '">' . esc_html__('Upgrade To Pro ($19 only)', 'gmap-embed') . '</a>';
		}
		$links['rate-us'] = '<a target="_blank" href="' . esc_url('https://wordpress.org/support/plugin/gmap-embed/reviews/#new-post') . '">' . esc_html__('Rate Us', 'gmap-embed') . '</a>';
		$links['support'] = '<a target="_blank" href="' . esc_url('https://wordpress.org/support/plugin/gmap-embed/#new-topic-0') . '">' . esc_html__('Support', 'gmap-embed') . '</a>';
		$links['settings'] = '<a href="' .
			esc_url(admin_url('admin.php?page=wpgmapembed-settings')) .
			'">' . esc_html__('Settings', 'gmap-embed') . '</a>';
		return array_merge($links, $actions);
	}
}
