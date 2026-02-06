<?php
namespace WGMSRM\Traits;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Filters related to the plugin will be listed here
 */
trait Filters
{

	/**
	 * In case of Google Map API loaded by other plugins or themes, it will prevent and load a blank script (Only removes by user consent)
	 *
	 * @param string $tag
	 * @param string $handle
	 * @param string $src
	 * @return mixed|string
	 * @since 1.7.5
	 */
	public function do_prevent_others_google_maps_tag($tag, $handle, $src)
	{
		// Validate and sanitize $src before using in regex
		$src = is_string($src) ? $src : '';
		if (preg_match('/maps\.google/i', $src)) {
			if (is_string($handle) && $handle !== 'wp-gmap-api') {
				return '';
			}
		}
		// Escape output for XSS protection if outputting $tag elsewhere
		return $tag;
	}
}
