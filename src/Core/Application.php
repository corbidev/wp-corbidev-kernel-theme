<?php

declare(strict_types=1);

namespace CorbiDev\Kernel\Core;

use CorbiDev\Kernel\Container\Container;
use CorbiDev\Kernel\Contracts\ServiceProviderInterface;
use CorbiDev\Kernel\Events\EventDispatcher;

/**
 * Application principale du kernel
 *
 * Gère le cycle de vie complet : création, enregistrement des providers,
 * boot et dispatching d'événements.
 */
class Application
{
    /**
     * Conteneur de services
     */
    private Container $container;

    /**
     * Liste des service providers enregistrés
     *
     * @var array<ServiceProviderInterface>
     */
    private array $providers = [];

    /**
     * Indique si l'application a déjà été bootée
     */
    private bool $booted = false;

    /**
     * Crée une nouvelle instance d'application
     *
     * @param array<string, mixed> $config Configuration de l'application
     * @return self
     */
    public static function create(array $config): self
    {
        return new self($config);
    }

    /**
     * Constructeur privé (utiliser create())
     *
     * @param array<string, mixed> $config Configuration
     */
    private function __construct(array $config)
    {
        $this->container = new Container();
        $this->container->set('config', $config);
        $this->container->set(EventDispatcher::class, new EventDispatcher());
        $this->container->set(Environment::class, Environment::detect($config));

        // Dispatch de l'événement de création
        $this->dispatch('kernel.created', [
            'config' => $config,
            'context' => $config['context'] ?? 'unknown',
        ]);
    }

    /**
     * Enregistre un service provider
     *
     * @param ServiceProviderInterface $provider Provider à enregistrer
     * @return void
     */
    public function register(ServiceProviderInterface $provider): void
    {
        if ($this->booted) {
            throw new \RuntimeException(
                'Cannot register providers after application has been booted.'
            );
        }

        $this->providers[] = $provider;

        // Dispatch avant enregistrement
        $this->dispatch('kernel.provider.registering', [
            'provider' => get_class($provider),
        ]);

        $provider->register($this->container);

        // Dispatch après enregistrement
        $this->dispatch('kernel.provider.registered', [
            'provider' => get_class($provider),
        ]);
    }

    /**
     * Boot l'application et tous les providers
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        // Dispatch avant boot
        $this->dispatch('kernel.booting', [
            'providers_count' => count($this->providers),
        ]);

        foreach ($this->providers as $provider) {
            // Dispatch avant boot du provider
            $this->dispatch('kernel.provider.booting', [
                'provider' => get_class($provider),
            ]);

            $provider->boot($this->container);

            // Dispatch après boot du provider
            $this->dispatch('kernel.provider.booted', [
                'provider' => get_class($provider),
            ]);
        }

        $this->booted = true;

        // Dispatch après boot complet
        $this->dispatch('kernel.booted', [
            'providers_count' => count($this->providers),
        ]);
    }

    /**
     * Récupère le conteneur de services
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Vérifie si l'application a été bootée
     *
     * @return bool
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Dispatch un événement via l'EventDispatcher
     *
     * @param string $event Nom de l'événement
     * @param array<string, mixed> $data Données à passer
     * @return void
     */
    private function dispatch(string $event, array $data = []): void
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->container->get(EventDispatcher::class);
        $dispatcher->dispatch($event, $data);
    }
}
