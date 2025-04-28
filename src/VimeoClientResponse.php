<?php

namespace Hirasso\ACFVimeoField;

readonly class VimeoClientResponse
{
    public int $status;
    public array $body;

    public function __construct(array $response) {
        $this->status = wp_remote_retrieve_response_code($response);
        $this->body = json_decode(wp_remote_retrieve_body($response), true);
    }

    public function getErrorMessage() {
        return $this->body['error'] ?? null;
    }
}
