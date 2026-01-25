<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Services;

use CorbiDev\Theme\Contracts\ServiceInterface;
use CorbiDev\Theme\Core\ServiceContainer;
use CorbiDev\Theme\Core\ThemeContext;
use CorbiDev\Theme\Core\HookRegistry;
use CorbiDev\Theme\Helpers\WPHelper;

final class AssetsService implements ServiceInterface
{
    public function __construct(private readonly ServiceContainer $container)
    {
    }

    public function register(): void
    {
        $hookRegistry = $this->container->getHookRegistry();
        $themeContext = $this->container->getThemeContext();

        $featureFlags = $themeContext->getFeatureFlags();

        // La gestion des assets est strictement opt‑in :
        // elle n'est active que si le thème déclare explicitement
        // feature_flags['assets'] = true dans la configuration du Kernel.
        if (!($featureFlags['assets'] ?? false)) {
            return;
        }

        $hookRegistry->registerAction('wp_enqueue_scripts', function () use ($themeContext): void {
            $this->enqueueAssets($themeContext);
        });
    }

    private function enqueueAssets(ThemeContext $themeContext): void
    {
        $options = $themeContext->getOptions();
        $environment = $themeContext->getEnvironment();

        $assets = $options['assets'] ?? [];
        if (!\is_array($assets)) {
            return;
        }

        $styles = $assets['styles'] ?? [];
        if (\is_array($styles)) {
            foreach ($styles as $style) {
                $this->enqueueStyle($style, $environment);
            }
        }

        $scripts = $assets['scripts'] ?? [];
        if (\is_array($scripts)) {
            foreach ($scripts as $script) {
                $this->enqueueScript($script, $environment);
            }
        }
    }

    private function enqueueStyle(array $definition, string $environment): void
    {
        if (!$this->isAssetEnabledForEnvironment($definition, $environment)) {
            return;
        }

        $handle = $definition['handle'] ?? null;
        $src = $definition['src'] ?? null;

        if (!\is_string($handle) || $handle === '' || !\is_string($src) || $src === '') {
            return;
        }

        $deps = \is_array($definition['deps'] ?? null) ? $definition['deps'] : [];
        $ver = \is_string($definition['ver'] ?? null) || \is_bool($definition['ver'] ?? null) || \is_null($definition['ver'] ?? null)
            ? $definition['ver']
            : null;
        $media = \is_string($definition['media'] ?? null) && $definition['media'] !== ''
            ? $definition['media']
            : 'all';

        WPHelper::enqueueStyle($handle, $src, $deps, $ver, $media);
    }

    private function enqueueScript(array $definition, string $environment): void
    {
        if (!$this->isAssetEnabledForEnvironment($definition, $environment)) {
            return;
        }

        $handle = $definition['handle'] ?? null;
        $src = $definition['src'] ?? null;

        if (!\is_string($handle) || $handle === '' || !\is_string($src) || $src === '') {
            return;
        }

        $deps = \is_array($definition['deps'] ?? null) ? $definition['deps'] : [];
        $ver = \is_string($definition['ver'] ?? null) || \is_bool($definition['ver'] ?? null) || \is_null($definition['ver'] ?? null)
            ? $definition['ver']
            : null;
        $inFooter = (bool) ($definition['in_footer'] ?? true);

        WPHelper::enqueueScript($handle, $src, $deps, $ver, $inFooter);
    }

    private function isAssetEnabledForEnvironment(array $definition, string $environment): bool
    {
        $allowedEnvs = $definition['env'] ?? null;
        if ($allowedEnvs === null) {
            return true;
        }

        if (!\is_array($allowedEnvs)) {
            return true;
        }

        return \in_array($environment, $allowedEnvs, true);
    }
}
