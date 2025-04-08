<?php

namespace Hirasso\ACFVimeoField;

/**
 * Represents a Vimeo Video
 */
final readonly class VimeoVideo
{
    /**
     * @param VimeoVideoFile[] $files
     */
    public function __construct(
        public string $ID,
        public string $url,
        public int $width,
        public int $height,
        public array $files,
    ) {}
}
