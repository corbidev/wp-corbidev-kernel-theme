<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Services;

use CorbiDev\Theme\Contracts\ServiceInterface;
use CorbiDev\Theme\Core\ServiceContainer;
use CorbiDev\Theme\Core\ThemeContext;
use CorbiDev\Theme\Core\HookRegistry;
use CorbiDev\Theme\Helpers\WPHelper;

final class RolesService implements ServiceInterface
{
    public function __construct(private readonly ServiceContainer $container)
    {
    }

    public function register(): void
    {
        $hookRegistry = $this->container->getHookRegistry();
        $themeContext = $this->container->getThemeContext();

        $hookRegistry->registerAction('init', function () use ($themeContext): void {
            $this->registerRolesAndCapabilities($themeContext);
        }, 11);
    }

    private function registerRolesAndCapabilities(ThemeContext $themeContext): void
    {
        $options = $themeContext->getOptions();
        $rolesConfig = $options['roles'] ?? [];

        if (!\is_array($rolesConfig)) {
            return;
        }

        foreach ($rolesConfig as $roleKey => $definition) {
            if (!\is_string($roleKey) || $roleKey === '' || !\is_array($definition)) {
                continue;
            }

            $capabilities = $definition['capabilities'] ?? [];
            if (!\is_array($capabilities)) {
                $capabilities = [];
            }

            $role = WPHelper::getRole($roleKey);

            if ($role === null) {
                // Le label du rôle doit être fourni par la config du thème.
                $label = $definition['label'] ?? $roleKey;

                WPHelper::addRole($roleKey, $label, $capabilities);
                continue;
            }

            foreach ($capabilities as $capability => $grant) {
                if (!\is_string($capability) || $capability === '' || !\is_bool($grant)) {
                    continue;
                }

                if ($grant) {
                    $role->add_cap($capability);
                } else {
                    $role->remove_cap($capability);
                }
            }
        }
    }
}
