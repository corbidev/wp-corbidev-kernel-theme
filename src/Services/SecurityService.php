<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Services;

use CorbiDev\Theme\Contracts\ServiceInterface;
use CorbiDev\Theme\Core\ServiceContainer;
use CorbiDev\Theme\Core\ThemeContext;
use CorbiDev\Theme\Core\HookRegistry;
use CorbiDev\Theme\Helpers\WPHelper;

final class SecurityService implements ServiceInterface
{
    public function __construct(private readonly ServiceContainer $container)
    {
    }

    public function register(): void
    {
        $hookRegistry = $this->container->getHookRegistry();
        $themeContext = $this->container->getThemeContext();

        $hookRegistry->registerAction('init', function () use ($themeContext): void {
            $this->applyHardening($themeContext);
        }, 0);
    }

    private function applyHardening(ThemeContext $themeContext): void
    {
        $options = $themeContext->getOptions();
        $security = $options['security'] ?? [];

        if (!\is_array($security)) {
            $security = [];
        }

        $disableXmlRpc = (bool) ($security['disable_xmlrpc'] ?? true);
        $disableUserEnumeration = (bool) ($security['disable_user_enumeration'] ?? true);

        if ($disableXmlRpc) {
            WPHelper::addFilter('xmlrpc_enabled', '__return_false');

            WPHelper::addFilter('wp_headers', static function (array $headers): array {
                unset($headers['X-Pingback']);

                return $headers;
            });
        }

        if ($disableUserEnumeration) {
            WPHelper::addFilter('rest_endpoints', static function (array $endpoints): array {
                unset($endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\\d]+)']);

                return $endpoints;
            });

            WPHelper::addAction('pre_get_posts', static function ($query): void {
                if (!WPHelper::isAdmin() && $query->is_main_query() && $query->is_author()) {
                    $query->set('author', 0);
                    $query->set('post__in', [0]);
                }
            });
        }
    }
}