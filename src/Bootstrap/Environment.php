<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Bootstrap;

final class Environment
{
    public static function detect(): string
    {
        if (!\defined('WP_ENV') || !\is_string(WP_ENV) || WP_ENV === '') {
            throw new \RuntimeException('WP_ENV must be defined and non-empty for Kernel environment detection.');
        }

        return WP_ENV;
    }
}
