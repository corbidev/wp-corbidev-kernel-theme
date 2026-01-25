<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Services;

use CorbiDev\Theme\Contracts\ServiceInterface;
use CorbiDev\Theme\Core\ServiceContainer;
use CorbiDev\Theme\Core\ThemeContext;
use CorbiDev\Theme\Core\HookRegistry;

final class MultisiteService implements ServiceInterface
{
    public function __construct(private readonly ServiceContainer $container)
    {
    }

    public function register(): void
    {
        if (!\function_exists('is_multisite') || !\is_multisite()) {
            return;
        }

        $hookRegistry = $this->container->getHookRegistry();
        $themeContext = $this->container->getThemeContext();

        $hookRegistry->registerAction('init', function () use ($themeContext): void {
            $this->registerMultisiteContext($themeContext);
        }, 1);
    }

    private function registerMultisiteContext(ThemeContext $themeContext): void
    {
        if (!\function_exists('get_main_network_id') || !\function_exists('get_main_site_id')) {
            return;
        }

        $options = $themeContext->getOptions();
        $multisite = $options['multisite'] ?? [];

        if (!\is_array($multisite)) {
            $multisite = [];
        }

        $mainNetworkId = \get_main_network_id();
        $mainSiteId = \get_main_site_id($mainNetworkId);

        $currentBlogId = \get_current_blog_id();

        $isMainSite = ($currentBlogId === $mainSiteId);

        if (!\has_filter('corbidev_theme_kernel_is_main_site')) {
            \add_filter('corbidev_theme_kernel_is_main_site', static function (bool $default) use ($isMainSite): bool {
                return $isMainSite;
            });
        }

        $sharedOptionKeys = $multisite['shared_options'] ?? [];
        if (\is_array($sharedOptionKeys) && $isMainSite && \function_exists('add_action')) {
            \add_action('update_option', static function (string $option, $oldValue, $value) use ($sharedOptionKeys, $mainSiteId): void {
                if (!\in_array($option, $sharedOptionKeys, true)) {
                    return;
                }

                if (!\function_exists('get_sites') || !\function_exists('switch_to_blog') || !\function_exists('restore_current_blog')) {
                    return;
                }

                $sites = \get_sites(['network_id' => \get_main_network_id()]);

                foreach ($sites as $site) {
                    if ((int) $site->blog_id === (int) $mainSiteId) {
                        continue;
                    }

                    \switch_to_blog((int) $site->blog_id);
                    \update_option($option, $value);
                    \restore_current_blog();
                }
            }, 10, 3);
        }
    }
}
