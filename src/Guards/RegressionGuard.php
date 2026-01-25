<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Guards;

final class RegressionGuard
{
    public static function assertNoRuntimeRegression(): void
    {
        // Ce guard s’appuie sur l’outillage/CI pour détecter les
        // régressions (echo, HTML, appels _e()/__() dans le Kernel).
        //
        // En cas de détection, l’outillage peut définir une constante
        // CORBIDEV_THEME_KERNEL_REGRESSION_ERROR avec un message
        // explicite ; le guard bloque alors le boot du Kernel.

        if (\defined('CORBIDEV_THEME_KERNEL_REGRESSION_ERROR')) {
            $message = (string) \constant('CORBIDEV_THEME_KERNEL_REGRESSION_ERROR');

            if ($message === '') {
                $message = 'Regression detected in CorbiDev Theme Kernel (forbidden output or i18n usage).';
            }

            throw new \RuntimeException($message);
        }
    }
}
