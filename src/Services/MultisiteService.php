<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Services;

use CorbiDev\Theme\Contracts\ServiceInterface;
use CorbiDev\Theme\Core\ServiceContainer;
use CorbiDev\Theme\Core\ThemeContext;
use CorbiDev\Theme\Core\HookRegistry;
use CorbiDev\Theme\Helpers\WPHelper;

final class MultisiteService implements ServiceInterface
{
    public function __construct(private readonly ServiceContainer $container)
    {
    }

    public function register(): void
    {
        if (!WPHelper::isMultisite()) {
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
        $mainNetworkId = WPHelper::getMainNetworkId();
        if ($mainNetworkId === null) {
            return;
        }

        $mainSiteId = WPHelper::getMainSiteId($mainNetworkId);
        if ($mainSiteId === null) {
            return;
        }

        $options = $themeContext->getOptions();
        $multisite = $options['multisite'] ?? [];

        if (!\is_array($multisite)) {
            $multisite = [];
        }

        $currentBlogId = WPHelper::getCurrentBlogId();
        if ($currentBlogId === null) {
            return;
        }

        $isMainSite = ($currentBlogId === $mainSiteId);

        if (!WPHelper::hasFilter('corbidev_theme_kernel_is_main_site')) {
            WPHelper::addFilter('corbidev_theme_kernel_is_main_site', static function (bool $default) use ($isMainSite): bool {
                return $isMainSite;
            });
        }

        $sharedOptionKeys = $multisite['shared_options'] ?? [];
        if (\is_array($sharedOptionKeys) && $isMainSite) {
            WPHelper::addAction('update_option', static function (string $option, $oldValue, $value) use ($sharedOptionKeys, $mainSiteId): void {
                if (!\in_array($option, $sharedOptionKeys, true)) {
                    return;
                }

                $sites = WPHelper::getSites(['network_id' => WPHelper::getMainNetworkId() ?? 0]);

                foreach ($sites as $site) {
                    if ((int) $site->blog_id === (int) $mainSiteId) {
                        continue;
                    }

                    WPHelper::switchToBlog((int) $site->blog_id);
                    WPHelper::updateOption($option, $value);
                    WPHelper::restoreCurrentBlog();
                }
            }, 10, 3);
        }
    }
}
