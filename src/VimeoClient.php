<?php

namespace Hirasso\ACFVimeoField;

class VimeoClient
{
    protected string $baseUrl = 'https://api.vimeo.com';

    protected array $headers;

    /**
     * Simple Vimeo API Client to retrieve video files and thumbnails.
     *
     * @param string $clientId Vimeo API Client Id
     * @param string $clientSecret Vimeo API Client Secret
     * @param string $accessToken Vimeo API Access Token
     */
    public function __construct(
        protected string $clientId,
        protected string $clientSecret,
        protected string $accessToken
    ) {
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Accept'        => 'application/vnd.vimeo.*+json;version=3.4',
        ];
    }

    /**
     * Send a GET request to Vimeo API
     */
    protected function get(string $endpoint, array $query = []): ?VimeoClientResponse
    {
        $url = $this->baseUrl . $endpoint;

        if (!empty($query)) {
            $url = add_query_arg($query, $url);
        }

        $response = wp_remote_get($url, [
            'headers' => $this->headers,
        ]);

        if (is_wp_error($response)) {
            return null;
        }

        return new VimeoClientResponse($response);
    }

    /**
     * Get video information by video id.
     *
     * @param string $videoId Vimeo Video Id.
     */
    public function requestVideo(string $videoId)
    {
        return $this->get("/videos/$videoId");
    }

    /**
     * Get a video ID from a URL
     */
    public function getVideoIdFromUrl(string $url): ?string
    {
        if (preg_match('/vimeo\.com\/(?:video\/|manage\/videos\/)?(?<id>\d+)/', $url, $matches)) {
            return $matches['id'];
        }
        return null;
    }
}
