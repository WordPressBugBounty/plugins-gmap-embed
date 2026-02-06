<?php
if (!defined('ABSPATH')) {
    exit;
}
// ************* WP Google Map Shortcode ***************
if (!function_exists('srm_gmap_embed_shortcode')) {

    /**
     * Generate map based on shortcode input
     *
     * @param $atts
     * @param $content
     *
     * @return string
     * @since 1.0.0
     */
    //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
    function srm_gmap_embed_shortcode($atts, $content)
    {
        static $count;
        if (!$count) {
            $count = 0;
        }
        $count++;
        // Sanitize and validate shortcode attributes
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts, 'gmap-embed');
        $wgm_map_id = intval($atts['id']);
        $bootstrap = \WGMSRM\Classes\Bootstrap::instance();
        
        $handler = new \WGMSRM\Classes\ShortcodeHandler($wgm_map_id, $count, $bootstrap->config());
        return $handler->handle();
    }
}

// ******* Defining Shortcode for WP Google Map
add_shortcode('gmap-embed', 'srm_gmap_embed_shortcode');