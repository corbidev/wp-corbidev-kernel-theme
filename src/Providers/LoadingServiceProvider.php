<?php

declare(strict_types=1);

namespace CorbiDev\Kernel\Providers;

use CorbiDev\Kernel\Container\Container;
use CorbiDev\Kernel\Contracts\ServiceProviderInterface;
use CorbiDev\Kernel\Loading\ProgressiveLoadingService;
use CorbiDev\Kernel\Events\EventDispatcher;

/**
 * Service Provider pour le système de chargement progressif
 */
final class LoadingServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        $config = $container->get('config');
        $strategy = $config['loading_strategy'] ?? 'progressive';

        $progressiveService = new ProgressiveLoadingService($strategy);
        $container->set(ProgressiveLoadingService::class, $progressiveService);

        /** @var EventDispatcher $dispatcher */
        $dispatcher = $container->get(EventDispatcher::class);
        $dispatcher->dispatch('kernel.loading.registered', [
            'strategy' => $strategy,
        ]);
    }

    public function boot(Container $container): void
    {
        /** @var ProgressiveLoadingService $progressiveService */
        $progressiveService = $container->get(ProgressiveLoadingService::class);
        $progressiveService->registerHooks();

        /** @var EventDispatcher $dispatcher */
        $dispatcher = $container->get(EventDispatcher::class);
        $dispatcher->dispatch('kernel.loading.booted', [
            'strategy' => $progressiveService->getStrategy(),
        ]);
    }
}
