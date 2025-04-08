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
     * Get URL of largest video thumbnail by video id.
     *
     * @param string $videoId Vimeo Video id
     * @return string|bool URL to video thumbnail file or false
     */
    public function getVideoThumbnail(string $videoId): string|bool
    {
        $response = $this->client->request("/videos/$videoId/pictures");
        $response = $this->getResponseBody($response);

        if (!$response) {
            return false;
        }

        // Get Videos active thumbnail
        $activeThumbnail = current(array_filter($response['data'], function ($item) {
            return $item['active'] === true;
        }));

        return $activeThumbnail['sizes'];
    }

    /**
     * Get video information by video id.
     *
     * @param string $videoId Vimeo Video Id.
     * @return boolean|array Vimeo REST Api response array or false.
     */
    public function requestVideo(string $videoId)
    {
        $response = $this->client->request("/videos/$videoId");

        return $this->getResponseBody($response);
    }

    /**
     * Get List of available Video files.
     *
     * @param string $videoId   Vimeo Video Id.
     * @param array $quality    Only return files matching video format or quality description.
     *                          Will match against the files public_name or quality field.
     *                          - `UHD 1440p`
     *                          - `HD 1080p`
     *                          - `HD 720p`
     *                          - `sd`
     *                          - `hd`
     *                          - Any video format or quality description
     * @return array|bool    False if request was unsuccessful or array of video files.
     */
    public function getVideoFiles(string $videoId, array $quality = [])
    {
        $result = $this->requestVideo($videoId);

        if (!$result || !isset($result['files'])) {
            return false;
        }

        if (!empty($quality)) {
            $result = array_filter(
                $result['files'],
                function ($file) use ($quality) {
                    return in_array($file['public_name'], $quality) || in_array($file['quality'], $quality);
                }
            );

            return array_values($result);
        }

        return $result['files'];
    }

    /**
     * Get List of available Video texttracks.
     *
     * @param string $videoId   Vimeo Video Id.
     * @return array|bool    False if request was unsuccessful or array of video texttracks.
     */
    public function getVideoTexttracks(string $videoId): array|bool
    {
        $response = $this->client->request("/videos/$videoId/texttracks");
        $response = $this->getResponseBody($response);

        if (!$response) {
            return false;
        }

        return $response['data'];
    }


    public function getVideoIdFromUrl(string $url)
    {
        preg_match('/(?:vimeo\.com\/)(?<id>[\d]+$)/m', $url, $matches);
        return $matches['id'] ?? false;
    }

    /**
     * Retrieves body from Vimeo API response. Will return false if status code is not 200.
     *
     * @param array $response
     * @return boolean|array
     */
    protected function getResponseBody(array $response)
    {
        if ($response['status'] !== 200) {
            return false;
        }

        return $response['body'];
    }
}
