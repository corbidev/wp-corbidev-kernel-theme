<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Helpers;

final class UrlHelper
{
    public static function homeUrl(string $path = ''): string
    {
        if (\function_exists('home_url')) {
            return (string) \home_url($path);
        }

        return $path;
    }
}
