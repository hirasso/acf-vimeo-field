<?php

namespace Hirasso\ACFVimeoField;

/**
 * The main plugin class
 */
final readonly class Plugin
{
    public string $url;
    public string $path;

    public function __construct(string $pluginFile)
    {
        $this->url = untrailingslashit(plugin_dir_url($pluginFile));
        $this->path = untrailingslashit(plugin_dir_path($pluginFile));
    }

    /**
     * Init hook
     */
    public function init()
    {
        add_action('acf/include_field_types', [$this, 'includeField']);
    }

    /**
     * Include the field
     */
    public function includeField(): void
    {
        $field = new ACFVimeoField($this);

        // For early debugging
        // $response = $field->getAjaxResponse([
        //     "action" => "acf/fields/vimeo-video/search",
        //     "url" => "https://vimeo.com/1078646862",
        //     "field_key" => "field_67f50c9fa66b4",
        //     "nonce" => "d7e8aee5ba",
        //     "post_id" => "140",
        // ]);
        // dd($response);
    }

    /**
     * Render a template
     */
    public function template(string $name, mixed $data)
    {
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
     * Create a VimeoClient with Vimeo API credentials from constants
     *
     * @requires VIMEO_ACCESS_TOKEN
     *
     */
    public function vimeoClient(): VimeoClient
    {
        /** @var ?VimeoClient $client */
        static $client;

        if (isset($client)) {
            return $client;
        }

        $missingConstants = collect(['VIMEO_ACCESS_TOKEN'])
            ->reject(fn ($const) => defined($const) && !!$const)
            ->map(fn ($const) => trim($const))
            ->join(', ');

        if ($missingConstants) {
            throw new \RuntimeException("Please setup Vimeo API credentials in your wp-config.php before use. Missing: $missingConstants");
        }

        $accessToken = defined('VIMEO_ACCESS_TOKEN') && VIMEO_ACCESS_TOKEN ? VIMEO_ACCESS_TOKEN : '';

        $client = new VimeoClient($accessToken);

        return $client;
    }
}
