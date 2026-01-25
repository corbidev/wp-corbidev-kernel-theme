<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Guards;

final class EnvironmentGuard
{
    public static function assertCompatible(): void
    {
        if (\version_compare(PHP_VERSION, '8.4.0', '<')) {
            throw new \RuntimeException('CorbiDev Theme Kernel requires PHP >= 8.4.');
        }

        if (!\defined('WP_ENV')) {
            throw new \RuntimeException('WP_ENV constant must be defined.');
        }
    }
}
