<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Guards;

final class WordPressGuard
{
    public static function assertRunningInWordPress(): void
    {
        if (!\defined('ABSPATH')) {
            throw new \RuntimeException('CorbiDev Theme Kernel must run within WordPress (ABSPATH missing).');
        }

        if (!\function_exists('wp_get_theme')) {
            throw new \RuntimeException('CorbiDev Theme Kernel must run after WordPress core is loaded (wp_get_theme missing).');
        }

        if (\function_exists('did_action') && \did_action('after_setup_theme') === 0) {
            throw new \RuntimeException('CorbiDev Theme Kernel must be booted in a theme context (after_setup_theme not fired).');
        }
    }
}
