<?php

namespace Hirasso\ACFVimeoField;

/**
 * An ACF field to access raw video files through the Vimeo API
 */
class ACFVimeoField extends \acf_field
{
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
        add_action('wp_ajax_acf/fields/vimeo-video/search', [$this, 'ajax_query']);

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

    /*
     *  prepare_field
     *
     *  This function will prepare the field for input
     *
     */
    public function prepare_field(?array $field)
    {
        return $field;
    }

    /*
     *  The callback for the wp_ajax search
     */
    public function ajax_query()
    {
        if (!acf_verify_ajax()) {
            die();
        }

        wp_send_json($this->get_ajax_query($_POST));
    }


    /*
    *  get_ajax_query
    *
    *  This function will return an array of data formatted for use in a select2 AJAX response
    *
    *  @type	function
    *  @date	15/10/2014
    *  @since	5.0.9
    *
    *  @param	$options (array)
    *  @return	(array)
    */

    public function get_ajax_query($args = [])
    {
        // defaults
        $args = acf_parse_args($args, [
            's'				=> '',
            'field_key'		=> '',
        ]);

        // load field
        $field = acf_get_field($args['field_key']);
        if (!$field) {
            return ['error' => "Couldn't find the related ACF field"];
        }

        // Query Vimeo API
        // Get Vimeo Video ID from Query Url
        $video_id = $this->plugin->vimeoClient()->getVideoIdFromUrl($args['s']);

        if (!$video_id) {
            return ['error' => 'URL is not a valid Vimeo Video'];
        }

        $api_response = @$this->plugin->vimeoClient()->requestVideo($video_id);

        if (!$api_response) {
            return [
                'error' => 'Vimeo API Error.',
            ];
        }

        $video_files = $api_response['files'] ?? [];

        // Get URL to thumbnail image
        $thumbnail = $this->getThumbnailUrl($api_response);

        // Get Video Texttracks
        $texttracks = @$this->plugin->vimeoClient()->getVideoTexttracks($video_id);

        // vars
        $response = [
            'ID'            => $video_id,
            'url'	        => $args['s'],
            'width'         => $api_response['width'],
            'height'        => $api_response['height'],
            'html'	        => $this->get_video_player_html($this->get_video_player_source($video_files)),
            'thumbnail'     => $thumbnail,
            'files'         => $video_files,
            'texttracks'    => $texttracks,
        ];

        // return
        return $response;
    }

    /*
    *  render_field()
    *
    *  Create the HTML interface for your field
    *
    *  @param	$field - an array holding all the field's data
    *
    *  @type	action
    *  @since	3.6
    *  @date	23/01/13
    */

    public function render_field($field)
    {
        $atts = [
            'class' => 'acf-oembed acf-vimeo-video',
        ];

        $value = $field['value'] ?: '';

        if ($value) {
            $atts['class'] .= ' has-value';
        }

        $_value = json_decode($value);

        $data = [
            'atts'  => $atts,
            'value' => $field['value'],
            'field' => $field,
            'url'   => $_value->url ?? false,
            'iframe' => $_value ? $this->get_video_player_html($this->get_video_player_source($_value->files)) : false
        ];

        echo $this->plugin->template('field', $data);
    }

    public function get_video_player_html($source)
    {
        return "<video controls preload='metadata' width='{$source->width}' height='{$source->height}'>" .
            "<source src='{$source->link}' type='{$source->type}' />" .
        "</video>";
    }

    public function get_video_player_source(array $files)
    {
        // Cast all entries of $files to objects
        $files = array_map(fn ($file) => (object) $file, $files);

        $_files = array_filter($files, function ($file) {
            return $file->quality === 'hd' || $file->quality === 'sd';
        });

        usort($_files, function ($a, $b) {
            $a_size = $a->width * $a->height;
            $b_size = $b->width * $b->height;

            return $a_size <=> $b_size;
        });

        // Return first element in array which resolution is equal or greater than 640 * 320
        foreach ($_files as $file) {
            if ($file->width * $file->height >= 640 * 320) {
                return $file;
            }

            if ($file->width >= 640 && $file->height >= 320) {
                return $file;
            }
        }

        return $_files[0];
    }


    /*
    *  render_field_settings()
    *
    *  Create extra options for your field. This is rendered when editing a field.
    *  The value of $field['name'] can be used (like bellow) to save extra data to the $field
    *
    *  @param	$field	- an array holding all the field's data
    *
    *  @type	action
    *  @since	3.6
    *  @date	23/01/13
    */

    public function render_field_settings($field)
    {
        // acf_render_field_setting($field, [
        //     'label'			=> __('Thumbhash', 'acf'),
        //     'type'			=> 'true_false',
        //     'name'			=> 'blurhash',
        //     'ui'            => 1
        // ]);
    }

    /*
     *  update_value()
     *
     *  This filter is applied to the $value before it is saved in the db
     *
     */
    public function update_value(mixed $value, string|int $post_id, array $field): mixed
    {
        if (empty($value)) {
            return $value;
        }

        $_value = json_decode(stripslashes($value));

        if (empty($_value)) {
            return $value;
        }

        // Create blurhash from Vimeo Video thumbnail
        $thumbnail_url = $_value->thumbnail[0]->link ?? false;

        if (!$thumbnail_url) {
            return $value;
        }

        // If the video has Texttracks store them to wordpress uploads folder
        if ($_value->texttracks ?? false) {
            $track_urls = $this->storeTexttracks($_value->texttracks, $_value->ID, $post_id);
            foreach ($track_urls as $id => $url) {
                // Find the texttrack object in $_value and and a local_url key with $url as value
                foreach ($_value->texttracks as &$texttrack) {
                    if ($texttrack->id === $id) {
                        $texttrack->local_url = $url;
                    }
                }
            }
        }

        $json = json_encode($_value, JSON_HEX_APOS | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS);

        $val = strtr($json, [
            '\n' => '\\\\n',
            '\r' => '\\\\r',
            '\t' => '\\\\t',
            '\f' => '\\\\f',
            '\d' => '\\\\d',
        ]);

        return $val;
    }

    /**
     * Stores texttracks to wordpress uploads folder and returns an array of the
     * texttracks local urls indexed by texttrack id.
     *
     * @param array $texttracks Array of Vimeo texttracks objects
     * @param int $video_id  Vimeo Video id
     * @param int $post_id  WordPress post id the texttracks belong to
     * @return array texttrack urls indexed by texttrack id
     */
    protected function storeTexttracks(
        array $texttracks,
        int $video_id,
        int $post_id,
        string $path = 'acf-vimeo-texttracks'
    ): array {
        $track_urls = [];
        $uploads_url = wp_upload_dir()['baseurl'];

        $upload_dir = wp_upload_dir();
        $upload_dir = $upload_dir['basedir'] . '/' . $path;

        $post = get_post($post_id);
        $upload_path = "{$post->post_name}-{$post_id}/{$video_id}";

        $upload_dir = "{$upload_dir}/{$upload_path}";

        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        foreach ($texttracks as $texttrack) {
            $url = $texttrack->link;
            $filename = basename($url);

            // Remove query string from filename
            $filename = explode('?', $filename)[0];

            $file = "{$upload_dir}/{$filename}";
            $url = "{$uploads_url}/{$path}/{$upload_path}/{$filename}";

            $file_content = file_get_contents($texttrack->link);
            $result = file_put_contents($file, $file_content);

            if ($result !== false) {
                // Get Url of WordPress uploads directory
                $url = "{$uploads_url}/{$path}/{$upload_path}/{$filename}";
                $track_urls[$texttrack->id] = $url;
            }
        }

        // Return array with complete texttacks urls
        return $track_urls;
    }

    /*
     *  format_value()
     *
     *  This filter is applied to the $value after it is loaded from the db and before it is returned to the template
     *
     */
    public function format_value(mixed $value, string|int $post_id, array $field)
    {
        // bail early if no value
        if (empty($value)) {
            return $value;
        }

        return json_decode($value);
    }

    /**
     * Get video Thumbnail url.
     *
     * @param array $video Array with fields as returned from Vimeo API.
     * @return string Url to image file.
     */
    protected function getThumbnailUrl(array $video): array
    {
        return $video['pictures']['sizes'];

        // $thumbnail_sizes = array_filter($video['pictures']['sizes'], function ($size) use ($video) {
        //     return $size['width'] >= $video['width'] &&
        //         $size['height'] >= $video['height'];
        // });

        // if (empty($thumbnail_sizes)) {
        //     $thumbnail = array_pop($video['pictures']['sizes']);
        // } else {
        //     $thumbnail = array_pop($thumbnail_sizes);
        // }

        // return $thumbnail['link'];
    }

    // protected function getVideoThumbnailBlurhash(string $url)
    // {
    //     $file = download_url($url);
    //     $blurhash = generateBlurhash($file);

    //     @unlink($file);

    //     return $blurhash;
    // }

    // protected function downloadThumbnail(string $url, int $post_id, string $desc = "")
    // {
    //     $file_array             = [];
    //     $file_array['name']     = wp_basename($url);
    //     $file_array['tmp_name'] = download_url($url);

    //     if (is_wp_error($file_array['tmp_name'])) {
    //         return $file_array['tmp_name'];
    //     }

    //     $image_type = exif_imagetype($file_array['tmp_name']);
    //     $image_extension = image_type_to_extension($image_type);

    //     // Create propper image file that WordPress will accept
    //     $parsed_url = parse_url($url);
    //     $file_name = wp_basename($parsed_url['path']);
    //     $file_name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file_name);
    //     $file_name .= $image_extension;

    //     $file_array['name'] = $file_name;

    //     // Do the validation and storage stuff.
    //     $id = media_handle_sideload($file_array, $post_id, $desc);

    //     // If error storing permanently, unlink.
    //     if (is_wp_error($id)) {
    //         @unlink($file_array['tmp_name']);
    //         return $id;
    //     }

    //     // Store the original attachment source in meta.
    //     add_post_meta($id, '_source_url', $url);

    //     return $id;
    // }
}
