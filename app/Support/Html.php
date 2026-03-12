<?php

namespace App\Support;

class Html
{
    public static function decode(?string $value = null): string
    {
        return html_entity_decode((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
