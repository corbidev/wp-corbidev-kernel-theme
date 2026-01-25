<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Services;

use CorbiDev\Theme\Contracts\ServiceInterface;
use CorbiDev\Theme\Core\ServiceContainer;
use CorbiDev\Theme\Core\ThemeContext;
use CorbiDev\Theme\Core\HookRegistry;
use CorbiDev\Theme\Helpers\WPHelper;

final class I18nService implements ServiceInterface
{
    public function __construct(private readonly ServiceContainer $container)
    {
    }

    public function register(): void
    {
        $hookRegistry = $this->container->getHookRegistry();
        $themeContext = $this->container->getThemeContext();

        $hookRegistry->registerAction('after_setup_theme', function () use ($themeContext): void {
            $this->loadTextDomain($themeContext);
        });
    }

    private function loadTextDomain(ThemeContext $themeContext): void
    {
        $textDomain = $themeContext->getTextDomain();
        $paths = $themeContext->getPaths();

        $languagesDir = null;
        if (isset($paths['languages_dir']) && \is_string($paths['languages_dir']) && $paths['languages_dir'] !== '') {
            $languagesDir = $paths['languages_dir'];
        }

        if ($languagesDir !== null) {
            WPHelper::loadThemeTextdomain($textDomain, $languagesDir);
        } else {
            WPHelper::loadThemeTextdomain($textDomain, null);
        }
    }
}
