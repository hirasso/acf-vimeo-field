<?php

declare(strict_types=1);

namespace Hirasso\ACFVimeoField;

final readonly class AjaxResponse
{
    public function __construct(
        public VimeoVideo $value,
        public string $html
    ) {
    }
}
