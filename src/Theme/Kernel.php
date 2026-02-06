<?php

declare(strict_types=1);

namespace CorbiDev\Kernel\Theme;

use CorbiDev\Kernel\Core\Application;
use CorbiDev\Kernel\Contracts\ServiceProviderInterface;
use CorbiDev\Kernel\Providers\LoadingServiceProvider;
use CorbiDev\Kernel\Helpers\LoadingHelpers;
use CorbiDev\Kernel\Loading\ProgressiveLoadingService;
use CorbiDev\Kernel\Loading\CriticalCssService;

/**
 * Façade officielle du kernel pour les thèmes WordPress CorbiDev
 *
 * Point d'entrée UNIQUE côté thème avec support du chargement progressif.
 */
final class Kernel
{
    /**
     * Boot du kernel dans un contexte thème
     *
     * @param array{
     *   theme: string,
     *   loading_strategy?: string,
     *   providers?: array<ServiceProviderInterface|class-string<ServiceProviderInterface>>
     * } $config
     * 
     * Stratégies de chargement disponibles :
     * - 'blocking' : Chargement classique (tous assets dans head)
     * - 'progressive' : HTML minimal + spinner + chargement différé (défaut)
     * - 'critical' : Critical CSS inline + reste différé
     */
    public static function boot(array $config): void
    {
        if (
            !isset($config['theme'])
            || !is_string($config['theme'])
        ) {
            throw new \InvalidArgumentException(
                'Theme kernel boot requires a "theme" string.'
            );
        }

        // Définir la stratégie de chargement par défaut
        if (!isset($config['loading_strategy'])) {
            $config['loading_strategy'] = 'progressive';
        }

        // Valider la stratégie
        $validStrategies = ['blocking', 'progressive', 'critical'];
        if (!in_array($config['loading_strategy'], $validStrategies, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid loading_strategy "%s". Valid values: %s',
                    $config['loading_strategy'],
                    implode(', ', $validStrategies)
                )
            );
        }

        // Créer l'application
        $app = Application::create([
            'context' => 'theme',
            'theme'   => $config['theme'],
            'loading_strategy' => $config['loading_strategy'],
        ]);

        // Auto-enregistrer le LoadingServiceProvider
        $app->register(new LoadingServiceProvider());

        // Enregistrer les providers utilisateur
        foreach ($config['providers'] ?? [] as $provider) {
            if (is_string($provider)) {
                $provider = new $provider();
            }

            if (!$provider instanceof ServiceProviderInterface) {
                throw new \RuntimeException(
                    'Invalid service provider given to theme kernel.'
                );
            }

            $app->register($provider);
        }

        // Boot de l'application
        $app->boot();

        // Enregistrer les helpers globaux
        self::registerGlobalHelpers($app);
    }

    /**
     * Enregistre les fonctions helper globales
     *
     * @param Application $app
     * @return void
     */
    private static function registerGlobalHelpers(Application $app): void
    {
        $container = $app->getContainer();

        // Injecter les services dans les helpers
        LoadingHelpers::setProgressiveService(
            $container->get(ProgressiveLoadingService::class)
        );

        LoadingHelpers::setCriticalCssService(
            $container->get(CriticalCssService::class)
        );

        // Charger le fichier des fonctions globales
        require_once __DIR__ . '/../Helpers/LoadingHelpers.php';
    }
}
