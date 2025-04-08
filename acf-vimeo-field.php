<?php

/**
 * ACF Vimeo Field
 *
 * @author            Rasso Hilber
 * @copyright         2025 Rasso Hilber
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: ACF Vimeo Field
 * Description: An ACF field to embed video files from vimeo.com directly on your site 🍿
 * Author: Rasso Hilber
 * Author URI: https://rassohilber.com/
 * Text Domain: acf-vimeo-field
 * Requires PHP: 8.2
 * Version: 0.0.0
 */

namespace Hirasso\ACFVimeoField;

use Hirasso\ACFVimeoField\ACFVimeoField;
use Hirasso\ACFVimeoField\VimeoClient;

if (is_readable(__DIR__.'/vendor/autoload.php')) {
    require_once __DIR__.'/vendor/autoload.php';
}

/**
 * The main plugin class
 */
final readonly class Plugin
{
    public string $url;
    public string $path;

    public function __construct()
    {
        $this->url = untrailingslashit(plugin_dir_url(__FILE__));
        $this->path = untrailingslashit(plugin_dir_path(__FILE__));

        // Include the field
        add_action('acf/include_field_types', function() {
            new ACFVimeoField($this);
        });
    }

    /**
     * Render a template
     */
    public function template(string $name, mixed $data) {
        $file = "$this->path/templates/$name.php";

        if (!file_exists($file)) {
            return "<p>$file: Template doesn't exist</p>";
        }

        ob_start();

        $this->includeIsolated($file, $data);

        return ob_get_clean();
    }

    /**
     * Loads a file with as little as possible in the variable scope
     */
    protected static function includeIsolated(
        string $file,
        mixed $data = [],
    ): void {
        $___file___ = $file;

        // allow to forward the full data to another snippet
        $___data___ = $data;

        if (is_array($data)) {
            extract($data);
        }

        include $___file___;
    }

    /**
     * Create a VimeoClient with Vimeo API credentials from ACF Option Fields
     */
    public function vimeoClient(): VimeoClient
    {
        /** @var ?VimeoClient $client */
        static $client;

        if (isset($client)) {
            return $client;
        }

        $missingConstants = collect(['VIMEO_CLIENT_ID', 'VIMEO_CLIENT_SECRET', 'VIMEO_ACCESS_TOKEN'])
            ->reject(fn ($const) => defined($const) && !!$const)
            ->map(fn ($const) => trim($const))
            ->join(', ');

        if ($missingConstants) {
            throw new \RuntimeException("Please setup Vimeo API credentials in your wp-config.php before use. Missing: $missingConstants");
        }

        $clientId = defined('VIMEO_CLIENT_ID') && VIMEO_CLIENT_ID ? VIMEO_CLIENT_ID : '';
        $clientSecret = defined('VIMEO_CLIENT_SECRET') && VIMEO_CLIENT_SECRET ? VIMEO_CLIENT_SECRET : '';
        $accessToken = defined('VIMEO_ACCESS_TOKEN') && VIMEO_ACCESS_TOKEN ? VIMEO_ACCESS_TOKEN : '';

        $client = new VimeoClient($clientId, $clientSecret, $accessToken);

        return $client;
    }
}

new Plugin();
