<?php
namespace WGMSRM\Traits;

if (!defined('ABSPATH')) {
	exit;
}
/**
 * Trait CommonFunctions
 */
trait CommonFunctions
{

	/**
	 * Update post meta
	 *
	 * @param int    $post_id
	 * @param string $field_name
	 * @param string $value
	 */
	public function wgm_update_post_meta($post_id, $field_name, $value = '')
	{
		// Sanitize and validate input before updating post meta
		$post_id = intval($post_id);
		$field_name = sanitize_key($field_name);
		// $value should be sanitized/validated before calling this function, or sanitize here if generic
		if (!get_post_meta($post_id, $field_name)) {
			add_post_meta($post_id, $field_name, $value);
		} else {
			update_post_meta($post_id, $field_name, $value);
		}
	}
}
