<?php

declare(strict_types=1);

namespace CorbiDev\Kernel\Helpers;

use CorbiDev\Kernel\Loading\ProgressiveLoadingService;
use CorbiDev\Kernel\Loading\CriticalCssService;

/**
 * Helpers pour le chargement progressif
 *
 * Fonctions globales utilisables dans les templates WordPress.
 */
class LoadingHelpers
{
    /**
     * Instance du ProgressiveLoadingService
     */
    private static ?ProgressiveLoadingService $progressiveService = null;

    /**
     * Instance du CriticalCssService
     */
    private static ?CriticalCssService $criticalCssService = null;

    /**
     * Définit le service de chargement progressif
     *
     * @param ProgressiveLoadingService $service
     * @return void
     */
    public static function setProgressiveService(ProgressiveLoadingService $service): void
    {
        self::$progressiveService = $service;
    }

    /**
     * Définit le service de CSS critique
     *
     * @param CriticalCssService $service
     * @return void
     */
    public static function setCriticalCssService(CriticalCssService $service): void
    {
        self::$criticalCssService = $service;
    }

    /**
     * Affiche le loader progressif
     *
     * À placer dans header.php après <body>
     *
     * @return void
     */
    public static function renderProgressiveLoader(): void
    {
        if (self::$progressiveService === null) {
            return;
        }

        echo self::$progressiveService->renderProgressiveLoader();
    }

    /**
     * Affiche le CSS critique inline
     *
     * À placer dans header.php dans le <head>
     *
     * @return void
     */
    public static function renderCriticalCss(): void
    {
        if (self::$criticalCssService === null) {
            return;
        }

        echo self::$criticalCssService->renderCriticalCssTag();
    }

    /**
     * Récupère la stratégie de chargement actuelle
     *
     * @return string
     */
    public static function getLoadingStrategy(): string
    {
        if (self::$progressiveService === null) {
            return 'blocking';
        }

        return self::$progressiveService->getStrategy();
    }

    /**
     * Vérifie si le mode progressif est actif
     *
     * @return bool
     */
    public static function isProgressiveMode(): bool
    {
        return self::getLoadingStrategy() === 'progressive';
    }

    /**
     * Vérifie si le mode critical est actif
     *
     * @return bool
     */
    public static function isCriticalMode(): bool
    {
        return self::getLoadingStrategy() === 'critical';
    }
}

/**
 * Fonction globale : Affiche le loader progressif
 *
 * Usage dans header.php :
 * <body>
 * <?php corbidev_progressive_loader(); ?>
 */
if (!function_exists('corbidev_progressive_loader')) {
    function corbidev_progressive_loader(): void
    {
        \CorbiDev\Kernel\Helpers\LoadingHelpers::renderProgressiveLoader();
    }
}

/**
 * Fonction globale : Affiche le CSS critique
 *
 * Usage dans header.php :
 * <head>
 * <?php corbidev_critical_css(); ?>
 * </head>
 */
if (!function_exists('corbidev_critical_css')) {
    function corbidev_critical_css(): void
    {
        \CorbiDev\Kernel\Helpers\LoadingHelpers::renderCriticalCss();
    }
}

/**
 * Fonction globale : Récupère la stratégie de chargement
 */
if (!function_exists('corbidev_loading_strategy')) {
    function corbidev_loading_strategy(): string
    {
        return \CorbiDev\Kernel\Helpers\LoadingHelpers::getLoadingStrategy();
    }
}

/**
 * Fonction globale : Vérifie si mode progressif
 */
if (!function_exists('is_progressive_loading')) {
    function is_progressive_loading(): bool
    {
        return \CorbiDev\Kernel\Helpers\LoadingHelpers::isProgressiveMode();
    }
}
