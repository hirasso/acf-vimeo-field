<?php

namespace Hirasso\ACFVimeoField;

use Vimeo\Vimeo;

class VimeoClient
{
    protected Vimeo $client;

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
        // Init Vimeo Api client
        $this->client = new Vimeo($clientId, $clientSecret, $accessToken);
    }

    /**
     * Get video information by video id.
     *
     * @param string $videoId Vimeo Video Id.
     */
    public function requestVideo(string $videoId): ?array
    {
        return $this->client->request("/videos/$videoId");
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
