<?php

declare(strict_types=1);

namespace CorbiDev\Kernel\Theme;

use CorbiDev\Kernel\Core\Application;
use CorbiDev\Kernel\Contracts\ServiceProviderInterface;

/**
 * Façade officielle du kernel pour les thèmes WordPress CorbiDev
 *
 * Point d’entrée UNIQUE côté thème.
 */
final class Kernel
{
    /**
     * Boot du kernel dans un contexte thème
     *
     * @param array{
     *   theme: string,
     *   providers?: array<ServiceProviderInterface|class-string<ServiceProviderInterface>>
     * } $config
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

        $app = Application::create([
            'context' => 'theme',
            'theme'   => $config['theme'],
        ]);

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

        $app->boot();
    }
}