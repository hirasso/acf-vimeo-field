<?php

declare(strict_types=1);

namespace Hirasso\ACFVimeoField;

use Exception;

/**
 * An ACF field to access video files through the Vimeo API
 */
class ACFVimeoField extends \acf_field
{
    public array $settings = [];

    public function __construct(
        protected Plugin $plugin,
    ) {
        $this->name = 'vimeo_video';
        $this->label = __('Vimeo Video', 'acf-vimeo-field');
        $this->description = __('A video from Vimeo', 'acf-vimeo-field');
        $this->category = 'content';

        $this->defaults = [
            'width'		=> 640,
            'height'	=> 320,
            'blurhash'  => false,
        ];

        // extra
        add_action('wp_ajax_acf/fields/vimeo-video/search', [$this, 'ajaxCallback']);

        parent::__construct();
    }

    /**
     * Enqueue styles and scripts for this field
     */
    public function input_admin_enqueue_scripts()
    {
        wp_enqueue_script('acf-vimeo-video', $this->assetURI("resources/input.js"), ['acf-input'], null);
        wp_enqueue_style('acf-vimeo-video', $this->assetURI("resources/input.css"), [], null);
    }

    /**
     * Helper function to get versioned asset urls
     */
    public function assetURI(string $path): string
    {
        $path = ltrim($path, '/');

        $uri = "{$this->plugin->url}/$path";
        $file = "{$this->plugin->path}/$path";

        if (file_exists($file)) {
            $uri .= "?v=" . hash_file('crc32', $file);
        }

        return $uri;
    }

    /**
     * Format the value for frontend output
     */
    public function format_value(mixed $value)
    {
        if (empty($value)) {
            return $value;
        }
        return $this->parseVimeoVideo($value);
    }

    /*
     *  The callback for the wp_ajax search
     */
    public function ajaxCallback()
    {
        if (!acf_verify_ajax()) {
            die();
        }

        try {
            wp_send_json_success($this->getAjaxResponse($_POST));
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }


    /*
     *  Get the response for the admin ajax request
     */
    public function getAjaxResponse($args = []): AjaxResponse
    {
        $args = acf_parse_args($args, [
            'url' => '',
            'field_key' => '',
        ]);

        $field = acf_get_field($args['field_key']);
        if (!$field) {
            throw new Exception(esc_html("ACF vimeo field reference not found"));
        }

        $videoURL = trim($args['url']);

        $videoID = $this->plugin->vimeoClient()->getVideoIdFromUrl($videoURL);

        if (!$videoID) {
            throw new Exception(esc_html('Please enter a valid Vimeo Video URL.'));
        }

        $response = $this->plugin->vimeoClient()->requestVideo($videoID);

        if (!$response || $response->status !== 200) {
            throw new Exception(esc_html($response->getErrorMessage() ?? 'Error requesting the Vimeo API.'));
        }

        $data = [
            ...[
                'ID' => $videoID,
                'url' => $videoURL
            ],
            ...collect($response->body)
                ->only(['width', 'height', 'files'])
                ->all(),
        ];

        $video = $this->parseVimeoVideo($data);

        if (empty($video->files ?? null)) {
            throw new Exception(esc_html('No video files found in API response. Does this video belong to you?'));
        }

        return new AjaxResponse(
            value: $video,
            html: $this->getAdminPreviewPlayer($this->getAdminPreviewSource($video->files))
        );
    }

    /**
     * Parse a raw value to a VimeoVideo object
     *
     * @param array{
     *   ID: string,
     *   url: string,
     *   width: int,
     *   height: int,
     *   files: ?array,
     * } $data
     */
    private function parseVimeoVideo(?array $data): ?VimeoVideo
    {
        if (empty($data)) {
            return null;
        }

        try {
            return new VimeoVideo(
                ID: $data['ID'],
                url: $data['url'],
                width: $data['width'],
                height: $data['height'],
                files: $this->parseVideoFiles($data['files'] ?? [])
            );
        } catch (\Error $e) {
            return null;
        }

    }

    /**
     * Parse video files from a decoded JSON array
     * @return VimeoVideoFile[]
     */
    private function parseVideoFiles(array $files): array
    {
        return collect($files)
            ->map(fn ($file) => new VimeoVideoFile(
                quality: $file['quality'],
                rendition: $file['rendition'],
                type: $file['type'],
                width: $file['width'] ?? null,
                height: $file['height'] ?? null,
                link: $file['link'],
                created_time: $file['created_time'],
                fps: $file['fps'],
                size: $file['size'],
                size_short: $file['size_short'],
                public_name: $file['public_name'],
                md5: $file['md5'],
            ))
            /** Sort from smallest to largest */
            ->sort(function (VimeoVideoFile $a, VimeoVideoFile $b) {
                $aHasSize = $a->width && $a->height;
                $bHasSize = $b->width && $b->height;

                // If only one has size, prioritize the one with size
                if ($aHasSize && !$bHasSize) {
                    return -1;
                }

                if (!$aHasSize && $bHasSize) {
                    return 1;
                }
                return ($a->width * $a->height) <=> ($b->width * $b->height);
            })
            ->values()
            ->all();
    }

    /*
     *  render_field()
     *
     *  Create the HTML interface for your field
     */
    public function render_field(array $field): void
    {
        $atts = ['class' => 'acf-vimeo-video'];

        $video = $this->parseVimeoVideo($field['value'] ?: null);

        if ($video) {
            $atts['class'] .= ' has-value';
        }

        $templateData = [
            'atts'  => $atts,
            'value' => $field['value'],
            'field' => $field,
            'url'   => $video->url ?? null,
            'html' => $this->getAdminPreviewPlayer($this->getAdminPreviewSource($video->files ?? [])),
            'placeholder' => $field['placeholder'] ?? __("Enter URL", 'acf-vimeo-field')
        ];

        echo $this->plugin->template('field', $templateData);
    }

    /**
     * Get the markup for the video preview player
     */
    public function getAdminPreviewPlayer(?VimeoVideoFile $source): string
    {
        if (!$source) {
            return '';
        }
        $ratio = $source->width / $source->height;
        $orientation = $ratio < 1 ? 'portrait' : 'landscape';
        return "<video playsinline controls data-$orientation preload='metadata' width='$source->width' height='$source->height' src='$source->link' type='$source->type' style='--aspect-ratio: $ratio'>";
    }

    /**
     * Get the video player source for the admin preview
     *
     * @param VimeoVideoFile[] $files
     */
    public function getAdminPreviewSource(array $files): ?VimeoVideoFile
    {
        if (empty($files)) {
            return null;
        }

        return collect($files)->first(function ($file) {
            return $file->width * $file->height >= 640 * 320;
        }) ?? collect($files)->first();
    }


    /*
     *  render_field_settings()
     *
     *  Create extra options for your field. This is rendered when editing a field.
     *  The value of $field['name'] can be used (like bellow) to save extra data to the $field
     *
     */
    public function render_field_settings(array $field): void
    {
        acf_render_field_setting($field, [
            'label' => __('Placeholder', 'acf-vimeo-field'),
            'type' => 'text',
            'name' => 'placeholder',
            'default_value' => 'Vimeo Video URL',
        ]);
    }

    /*
     *  update_value()
     *
     *  This filter is applied to the $value before it is saved in the db
     */
    public function update_value(mixed $value, string|int $post_id, array $field): mixed
    {
        if (empty($value)) {
            return $value;
        }
        return json_decode(stripslashes($value), associative: true);
    }

    // protected function getVideoThumbnailBlurhash(string $url)
    // {
    //     $file = download_url($url);
    //     $blurhash = generateBlurhash($file);

    //     @unlink($file);

    //     return $blurhash;
    // }
}
