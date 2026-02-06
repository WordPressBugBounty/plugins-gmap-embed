<?php

namespace WGMSRM\Traits;

/**
 * Trait SetupWizard
 */
trait SetupWizard
{

	/**
	 * Setup Wizard view
	 *
	 * @since 1.7.5
	 */
	public function wpgmap_setup_wizard()
	{
		require WGM_PLUGIN_PATH . 'admin/includes/wpgmap_setup_wizard.php';
	}

	/**
	 * Save setup wizard information
	 *
	 * @since 1.7.5
	 */
	public function wpgmap_save_setup_wizard()
	{
		check_ajax_referer('wpgmapembed_save_setup_wizard', 'nonce');

		if (!current_user_can($this->capability)) {
			echo wp_json_encode(['responseCode' => 403, 'message' => esc_html__('Unauthorized access.', 'gmap-embed')]);
			die();
		}
		$api_key = isset($_POST['wgm_api_key']) ? sanitize_text_field(wp_unslash($_POST['wgm_api_key'])) : '';
		$language = isset($_POST['wgm_language']) ? sanitize_text_field(wp_unslash($_POST['wgm_language'])) : '';
		$regional_area = isset($_POST['wgm_regional_area']) ? sanitize_text_field(wp_unslash($_POST['wgm_regional_area'])) : '';
		if (empty($api_key)) {
			$response = array('responseCode' => 101);
			echo wp_json_encode($response);
			die();
		}
		if (empty($language)) {
			$response = array('responseCode' => 102);
			echo wp_json_encode($response);
			die();
		}
		if (empty($regional_area)) {
			$response = array('responseCode' => 103);
			echo wp_json_encode($response);
			die();
		}
		// Sanitize and validate before saving to DB
		update_option('wpgmap_api_key', sanitize_text_field($api_key), 'yes');
		update_option('srm_gmap_lng', sanitize_text_field($language), 'yes');
		update_option('srm_gmap_region', sanitize_text_field($regional_area), 'yes');
		update_option('wgm_is_quick_setup_done', 'Y', 'yes');
		$response = array('responseCode' => 200);
		echo wp_json_encode($response);
		die();
	}
}
