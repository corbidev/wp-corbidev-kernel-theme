<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Autoload PSR-4 du thème
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'CorbiDev\\Theme\\';
    $baseDir = __DIR__ . '/includes/';

    if (str_starts_with($class, $prefix)) {
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_readable($file)) {
            require_once $file;
        }
    }
});

use CorbiDev\Kernel\Theme\Kernel;

/**
 * Boot du kernel CorbiDev avec chargement progressif
 *
 * STRATÉGIES DISPONIBLES :
 * 
 * 1. 'progressive' (RECOMMANDÉ) :
 *    - HTML minimal chargé en premier → Affichage immédiat
 *    - Spinner affiché pendant le chargement
 *    - Assets Vite/Vue chargés en différé
 *    - Transition smooth quand tout est prêt
 *    → First Contentful Paint : < 0.5s
 *    → Time to Interactive : ~1-2s
 * 
 * 2. 'critical' :
 *    - Critical CSS inline dans le <head>
 *    - Reste du CSS chargé en différé
 *    - JS chargé en différé
 *    → First Contentful Paint : < 0.3s
 *    → Nécessite assets/css/critical.css
 * 
 * 3. 'blocking' :
 *    - Chargement classique (tous assets dans head)
 *    - Pas de spinner
 *    - Chargement séquentiel
 *    → First Contentful Paint : ~2-3s
 */
Kernel::boot([
    'theme' => 'starter',
    
    /**
     * Stratégie de chargement
     * 
     * Changer cette valeur pour tester les différentes stratégies :
     * - 'progressive' : HTML minimal + spinner + chargement différé
     * - 'critical' : Critical CSS inline + reste différé
     * - 'blocking' : Chargement classique
     */
    'loading_strategy' => 'progressive', // ← Modifier ici
    
    /**
     * Service providers du thème
     */
    'providers' => [
        CorbiDev\Theme\Infrastructure\ThemeServiceProvider::class,
    ],
]);

/**
 * === EXEMPLE D'UTILISATION DES HELPERS ===
 * 
 * Dans les templates, vous pouvez utiliser :
 * 
 * // Dans header.php
 * corbidev_critical_css();        // Inline le CSS critique
 * corbidev_progressive_loader();  // Affiche le spinner
 * 
 * // Dans n'importe quel template
 * if (is_progressive_loading()) {
 *     // Code spécifique au mode progressif
 * }
 * 
 * $strategy = corbidev_loading_strategy(); // 'progressive', 'critical' ou 'blocking'
 */

/**
 * === ÉVÉNEMENTS DISPONIBLES ===
 * 
 * Le kernel dispatche des événements pour le chargement :
 * 
 * - kernel.loading.registered : Après enregistrement des services
 * - kernel.loading.booted : Après boot des services
 * 
 * Vous pouvez les écouter dans votre ServiceProvider :
 * 
 * $dispatcher->on('kernel.loading.booted', function(Event $e) {
 *     $strategy = $e->get('strategy');
 *     // Actions selon la stratégie
 * });
 */

/**
 * === PERFORMANCE ATTENDUE ===
 * 
 * Mode 'progressive' :
 * ✅ First Contentful Paint : 0.3-0.5s
 * ✅ Time to Interactive : 1-2s
 * ✅ Lighthouse Performance : 95-100
 * 
 * Mode 'critical' :
 * ✅ First Contentful Paint : 0.2-0.3s
 * ✅ Time to Interactive : 1.5-2.5s
 * ✅ Lighthouse Performance : 98-100
 * 
 * Mode 'blocking' :
 * ⚠️ First Contentful Paint : 1-3s
 * ⚠️ Time to Interactive : 3-5s
 * ⚠️ Lighthouse Performance : 70-85
 */
