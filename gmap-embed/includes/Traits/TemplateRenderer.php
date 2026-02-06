<?php

namespace WGMSRM\Traits;

if (!defined('ABSPATH')) {
	exit;
}

trait TemplateRenderer
{
	/**
	 * Render a template file with data.
	 *
	 * @param string $template_name Relative path to template inside 'templates' dir.
	 * @param array  $data          Associative array of data to extract.
	 * @param bool   $echo          Whether to echo or return the content.
	 * @return string|void
	 */
	public function render($template_name, $data = [], $echo = false)
	{
		$template_path = WGM_PLUGIN_PATH . 'templates/' . $template_name . '.php';

		if (!file_exists($template_path)) {
			return '';
		}

		if (!empty($data)) {
			extract($data);
		}

		ob_start();
		include $template_path;
		$output = ob_get_clean();

		if ($echo) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			return $output;
		}
	}
}
