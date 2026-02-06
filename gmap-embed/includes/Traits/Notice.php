<?php

namespace WGMSRM\Traits;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Trait Notice
 */
trait Notice
{

	/**
	 * Generate Different types of notices
	 */
	public function gmap_embed_notice_generate()
	{
		// Review admin notice
		$this->gmap_embed_generate_admin_review_notice();

		// No API key admin notice
		$this->wgm_no_api_key_added_notice();
	}


	public function wgm_no_api_key_added_notice()
	{
		if (get_option('wpgmap_api_key', 'none') === 'none') {
			?>
			<div class="notice notice-info is-dismissible" style="border: 1px #db1c1c solid;">
				<p>
					<?php
					echo wp_kses(
						sprintf(
							/* translators: 1: Quick Setup URL, 2: Documentation URL */
							__('You must generate your own <b>API Key</b> to use Google Map, Now it\'s <b>Super Easy</b>, Please go to <span style="font-weight: bold;">WP Google Map</span><i class="dashicons  dashicons-arrow-right-alt2"></i><a href="%1$s" style="text-decoration: none;font-weight: bold;">Quick Setup</a> and follow easy steps, <i class="dashicons  dashicons-external"></i><a href="%2$s" target="_blank" style="text-decoration: none;font-weight: bold;">Documentation</a>', 'gmap-embed'),
							esc_url(admin_url('admin.php?page=wgm_setup_wizard')),
							esc_url('https://wpgooglemap.com/documentation/wp-google-map-quick-installation?utm_source=wp_admin&utm_medium=admin_link&utm_campaign=header_notice')
						),
						array(
							'b' => array(),
							'span' => array('style' => array()),
							'i' => array('class' => array()),
							'a' => array(
								'href' => array(),
								'style' => array(),
								'target' => array(),
							),
						)
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Do something on review already button click
	 */
	public function review_already_did()
	{
		// Nonce verification for dismiss review
		if (!isset($_GET['wgm_review_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['wgm_review_nonce'])), 'wgm_review_action')) {
			wp_die(esc_html__('Security check failed.', 'gmap-embed'));
		}
		update_option('gmap_embed_is_review_done', true);
	}

	/**
	 * Do something on review later button click
	 */
	public function review_later()
	{
		// Nonce verification for later review
		if (!isset($_GET['wgm_review_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['wgm_review_nonce'])), 'wgm_review_action')) {
			wp_die(esc_html__('Security check failed.', 'gmap-embed'));
		}
		update_option('gmap_embed_is_review_snoozed', true);
		update_option('gmap_embed_review_snoozed_at', time());
	}

	/**
	 * Redirect to URL generate
	 *
	 * @return string
	 */
	public function redirect_to()
	{
		$request_uri_raw = '';
		if (isset($_SERVER['REQUEST_URI'])) {
			$request_uri_raw = filter_var(wp_unslash($_SERVER['REQUEST_URI']), FILTER_SANITIZE_URL);
		}
		$request_uri = wp_parse_url(sanitize_text_field($request_uri_raw), PHP_URL_PATH);
		$query_string = wp_parse_url(sanitize_text_field($request_uri_raw), PHP_URL_QUERY);
		parse_str($query_string, $current_url);
		$unset_array = array('dismiss', 'plugin', 'wgm_review_nonce', 'later');

		foreach ($unset_array as $value) {
			if (isset($current_url[$value])) {
				unset($current_url[$value]);
			}
		}
		$current_url = http_build_query($current_url);
		$redirect_url = $request_uri . ($current_url ? '?' . $current_url : '');
		return esc_url($redirect_url);
	}

	/**
	 * Generate admin panel review notice for WP Google Map plugin
	 */
	public function gmap_embed_generate_admin_review_notice()
	{
		$activation_time = get_option('gmap_embed_activation_time', false);
		$is_review_snoozed = get_option('gmap_embed_is_review_snoozed');
		$snoozed_time = get_option('gmap_embed_review_snoozed_at');

		// How may day's passed after activation
		$seconds_diff = time() - $activation_time;
		$passed_days = ($seconds_diff / 3600) / 24;

		// Snoozed how many day's before
		$seconds_diff = time() - $snoozed_time;
		$snoozed_before = ($seconds_diff / 3600) / 24;
		$is_review_done = get_option('gmap_embed_is_review_done');

		/**
		 *
		 * Review section will shows based on following cases
		 * Case 1: Passed three(3) days and not snoozed
		 * Case 2: Snoozed before 7 day's
		 */
		if ($is_review_done == false && (($passed_days >= 3 && $is_review_snoozed == false) || ($is_review_snoozed == true && $snoozed_before >= 7))) {
			$request_uri_raw = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
			$scheme = (wp_parse_url(sanitize_text_field($request_uri_raw), PHP_URL_QUERY)) ? '&' : '?';
			$url = esc_url_raw(sanitize_text_field($request_uri_raw) . $scheme);
			$nonce = wp_create_nonce('wgm_review_action');
			$dismiss_link = add_query_arg(
				array(
					'plugin' => 'gmap-embed',
					'dismiss' => true,
					// Use a unique action name for nonce
					'wgm_review_nonce' => $nonce,
				),
				$url
			);
			$later_link = add_query_arg(
				array(
					'plugin' => 'gmap-embed',
					'later' => true,
					'wgm_review_nonce' => $nonce
				),
				$url
			);
			?>
			<div class="gmap_embed_review_section notice notice-success">
				<?php //phpscs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
				<img src="<?php echo esc_url(WGM_PLUGIN_URL . 'admin/assets/images/gmap_embed_logo.jpg'); ?>" width="60"
					style="float: left;margin: 9px 9px 0 5px !important" />
				<p>
					<?php
					echo wp_kses(
						sprintf(
							/* translators: %1$s: Plugin name */
							__("<span style='color:green;'>We hope you're enjoying using <b style='color:#007cba'>%1\$s</b> plugin. Could you please give us a BIG favour and give it a 5-star rating on WordPress to help us spread the word and boost our motivation!</span>", 'gmap-embed'),
							'WP Google Map'
						),
						array(
							'span' => array('style' => array()),
							'b' => array('style' => array()),
						)
					);
					?>
				</p>
				<ul>
					<li style="display: inline;margin-right:15px;"><a style="text-decoration: none"
							href="<?php echo esc_url('https://wpgooglemap.com/wp-review-forum?utm_source=wp_admin&utm_medium=admin_link&utm_campaign=review_notice'); ?>"
							target="_blank"><span class="dashicons dashicons-external"></span> Ok, you deserve it!</a></li>
					<li style="display: inline;margin-right:15px;"><a style="text-decoration: none"
							href="<?php echo esc_url($dismiss_link); ?>"><span class="dashicons dashicons-smiley"></span> I
							already did</a></li>
					<li style="display: inline;margin-right:15px;"><a style="text-decoration: none"
							href="<?php echo esc_url($later_link); ?>"><span class="dashicons dashicons-calendar-alt"></span>
							<?php esc_html_e('Maybe Later', 'gmap-embed'); ?></a></li>
					<li style="display: inline;margin-right:15px;"><a style="text-decoration: none"
							href="<?php echo esc_url('https://wpgooglemap.com/wp-support-forum?utm_source=wp_admin&utm_medium=admin_link&utm_campaign=review_notice'); ?>"
							target="_blank"><span class="dashicons dashicons-external"></span>
							<?php esc_html_e('I need help', 'gmap-embed'); ?></a></li>
					<li style="display: inline;margin-right:15px;"><a style="text-decoration: none"
							href="<?php echo esc_url($dismiss_link); ?>"><span class="dashicons dashicons-dismiss"></span>
							<?php esc_html_e('Never show again', 'gmap-embed'); ?></a></li>
				</ul>
			</div>
			<?php
		}
	}
}
