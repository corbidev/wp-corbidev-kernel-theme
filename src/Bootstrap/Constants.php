<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Bootstrap;

final class Constants
{
    public static function defineKernelConstants(string $kernelBasePath, string $kernelVersion): void
    {
        if (!\defined('CORBIDEV_THEME_KERNEL_PATH')) {
            \define('CORBIDEV_THEME_KERNEL_PATH', $kernelBasePath);
        }

        if (!\defined('CORBIDEV_THEME_KERNEL_VERSION')) {
            \define('CORBIDEV_THEME_KERNEL_VERSION', $kernelVersion);
        }
    }
}
