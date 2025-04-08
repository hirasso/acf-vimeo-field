<?php

namespace Hirasso\ACFVimeoField;

/**
 * Represents a Vimeo Video File
 */
final readonly class VimeoVideoFile
{
    public function __construct(
        public string $quality,
        public string $rendition,
        public string $type,
        public ?int $width,
        public ?int $height,
        public string $link,
        public string $created_time,
        public float $fps,
        public int $size,
        public string $size_short,
        public string $public_name,
        public ?string $md5,
    ) {}
}
