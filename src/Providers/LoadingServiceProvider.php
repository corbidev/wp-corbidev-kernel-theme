<?php

declare(strict_types=1);

namespace CorbiDev\Kernel\Providers;

use CorbiDev\Kernel\Container\Container;
use CorbiDev\Kernel\Contracts\ServiceProviderInterface;
use CorbiDev\Kernel\Loading\ProgressiveLoadingService;
use CorbiDev\Kernel\Loading\CriticalCssService;
use CorbiDev\Kernel\Events\EventDispatcher;

/**
 * Service Provider pour le système de chargement progressif
 *
 * Enregistre automatiquement les services de chargement
 * en fonction de la stratégie configurée dans le kernel.
 */
final class LoadingServiceProvider implements ServiceProviderInterface
{
    /**
     * Enregistrement des services de chargement
     *
     * @param Container $container
     * @return void
     */
    public function register(Container $container): void
    {
        // Récupérer la configuration
        $config = $container->get('config');
        $strategy = $config['loading_strategy'] ?? 'progressive';

        // Enregistrer ProgressiveLoadingService
        $progressiveService = new ProgressiveLoadingService($strategy);
        $container->set(ProgressiveLoadingService::class, $progressiveService);

        // Enregistrer CriticalCssService
        $criticalCssService = new CriticalCssService();
        $container->set(CriticalCssService::class, $criticalCssService);

        // Dispatcher un événement
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $container->get(EventDispatcher::class);
        $dispatcher->dispatch('kernel.loading.registered', [
            'strategy' => $strategy,
        ]);
    }

    /**
     * Boot des services de chargement
     *
     * @param Container $container
     * @return void
     */
    public function boot(Container $container): void
    {
        /** @var ProgressiveLoadingService $progressiveService */
        $progressiveService = $container->get(ProgressiveLoadingService::class);

        // Enregistrer les hooks WordPress
        $progressiveService->registerHooks();

        // Dispatcher un événement
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $container->get(EventDispatcher::class);
        $dispatcher->dispatch('kernel.loading.booted', [
            'strategy' => $progressiveService->getStrategy(),
        ]);
    }
}
