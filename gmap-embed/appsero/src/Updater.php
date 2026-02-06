<?php

namespace GmapEmbed\Appsero;

defined( 'ABSPATH' ) || exit;


use stdClass;

/**
 * Appsero Updater
 *
 * This class will show new updates project
 */
class Updater
{

    /**
     * GmapEmbed\Appsero\Client
     *
     * @var object
     */
    protected $client;

    /**
     * Cache key
     *
     * @var string
     */
    protected $cache_key;

    /**
     * Initialize the class
     *
     * @param GmapEmbed\Appsero\Client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->cache_key = 'appsero_' . md5($this->client->slug) . '_version_info';

        // Run hooks.
        if ($this->client->type === 'plugin') {
            $this->run_plugin_hooks();
        } elseif ($this->client->type === 'theme') {
            $this->run_theme_hooks();
        }
    }

    /**
     * Set up WordPress filter to hooks to get update.
     *
     * @return void
     */
    public function run_plugin_hooks()
    {
        add_filter('plugins_api', [$this, 'plugins_api_filter'], 10, 3);
    }

    /**
     * Set up WordPress filter to hooks to get update.
     *
     * @return void
     */
    public function run_theme_hooks()
    {
    }



    /**
     * Get version info from database
     *
     * @return object or Boolean
     */
    private function get_cached_version_info()
    {
        global $pagenow;

        // If updater page then fetch from API now
        if ('update-core.php' === $pagenow) {
            return false; // Force to fetch data
        }

        $value = get_transient($this->cache_key);

        if (!$value && !isset($value->name)) {
            return false; // Cache is expired
        }

        // We need to turn the icons into an array
        if (isset($value->icons)) {
            $value->icons = (array) $value->icons;
        }

        // We need to turn the banners into an array
        if (isset($value->banners)) {
            $value->banners = (array) $value->banners;
        }

        if (isset($value->sections)) {
            $value->sections = (array) $value->sections;
        }

        return $value;
    }

    /**
     * Set version info to database
     */
    private function set_cached_version_info($value)
    {
        if (!$value) {
            return;
        }

        set_transient($this->cache_key, $value, 3 * HOUR_IN_SECONDS);
    }

    /**
     * Get plugin info from Appsero
     */
    private function get_project_latest_version()
    {
        $license = $this->client->license()->get_license();

        $params = [
            'version' => $this->client->project_version,
            'name' => $this->client->name,
            'slug' => $this->client->slug,
            'basename' => $this->client->basename,
            'license_key' => !empty($license) && isset($license['key']) ? $license['key'] : '',
        ];

        $route = 'update/' . $this->client->hash . '/check';

        $response = $this->client->send_request($params, $route, true);

        if (is_wp_error($response)) {
            return false;
        }

        $response = json_decode(wp_remote_retrieve_body($response));

        if (!isset($response->slug)) {
            return false;
        }

        if (isset($response->icons)) {
            $response->icons = (array) $response->icons;
        }

        if (isset($response->banners)) {
            $response->banners = (array) $response->banners;
        }

        if (isset($response->sections)) {
            $response->sections = (array) $response->sections;
        }

        return $response;
    }

    /**
     * Updates information on the "View version x.x details" page with custom data.
     *
     * @param mixed  $data
     * @param string $action
     * @param object $args
     *
     * @return object $data
     */
    public function plugins_api_filter($data, $action = '', $args = null)
    {
        if ($action !== 'plugin_information') {
            return $data;
        }

        if (!isset($args->slug) || ($args->slug !== $this->client->slug)) {
            return $data;
        }

        return $this->get_version_info();
    }



    /**
     * Get version information
     */
    private function get_version_info()
    {
        $version_info = $this->get_cached_version_info();

        if (false === $version_info) {
            $version_info = $this->get_project_latest_version();
            $this->set_cached_version_info($version_info);
        }

        return $version_info;
    }
}
