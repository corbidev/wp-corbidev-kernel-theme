<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Core;

use CorbiDev\Theme\Contracts\ServiceInterface;
use CorbiDev\Theme\Services\AssetsService;
use CorbiDev\Theme\Services\I18nService;
use CorbiDev\Theme\Services\RolesService;
use CorbiDev\Theme\Services\SecurityService;
use CorbiDev\Theme\Services\MultisiteService;

final class ServiceContainer
{
    /** @var array<string, object> */
    private array $services = [];

    public function __construct(
        private readonly ThemeContext $themeContext,
        private readonly HookRegistry $hookRegistry,
    ) {
    }

    public function getThemeContext(): ThemeContext
    {
        return $this->themeContext;
    }

    public function getHookRegistry(): HookRegistry
    {
        return $this->hookRegistry;
    }

    public function registerServices(): void
    {
        $this->get(AssetsService::class)->register();
        $this->get(I18nService::class)->register();
        $this->get(RolesService::class)->register();
        $this->get(SecurityService::class)->register();
        $this->get(MultisiteService::class)->register();
    }

    public function get(string $id): object
    {
        if (!isset($this->services[$id])) {
            $this->services[$id] = $this->createService($id);
        }

        return $this->services[$id];
    }

    private function createService(string $id): object
    {
        return match ($id) {
            AssetsService::class => new AssetsService($this),
            I18nService::class => new I18nService($this),
            RolesService::class => new RolesService($this),
            SecurityService::class => new SecurityService($this),
            MultisiteService::class => new MultisiteService($this),
            default => throw new \InvalidArgumentException('Unknown service: ' . $id),
        };
    }
}
