<?php
namespace CorbiDev\Kernel\Core;

use CorbiDev\Kernel\Container\Container;
use CorbiDev\Kernel\Contracts\ServiceProviderInterface;
use CorbiDev\Kernel\Events\EventDispatcher;

class Application
{
    private Container $container;
    private array $providers = [];

    public static function create(array $config): self
    {
        return new self($config);
    }

    private function __construct(array $config)
    {
        $this->container = new Container();
        $this->container->set('config', $config);
        $this->container->set(EventDispatcher::class, new EventDispatcher());
        $this->container->set(Environment::class, Environment::detect($config));
    }

    public function register(ServiceProviderInterface $provider): void
    {
        $this->providers[] = $provider;
        $provider->register($this->container);
    }

    public function boot(): void
    {
        foreach ($this->providers as $provider) {
            $provider->boot($this->container);
        }
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}
