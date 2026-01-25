<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Helpers;

final class PathHelper
{
    public static function normalize(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
