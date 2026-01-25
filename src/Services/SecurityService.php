<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Services;

use CorbiDev\Theme\Contracts\ServiceInterface;
use CorbiDev\Theme\Core\ServiceContainer;
use CorbiDev\Theme\Core\ThemeContext;
use CorbiDev\Theme\Core\HookRegistry;

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

        if ($disableXmlRpc && \function_exists('add_filter')) {
            \add_filter('xmlrpc_enabled', '__return_false');

            \add_filter('wp_headers', static function (array $headers): array {
                unset($headers['X-Pingback']);

                return $headers;
            });
        }

        if ($disableUserEnumeration && \function_exists('add_action') && \function_exists('add_filter')) {
            \add_filter('rest_endpoints', static function (array $endpoints): array {
                unset($endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\\d]+)']);

                return $endpoints;
            });

            \add_action('pre_get_posts', static function ($query): void {
                if (!\is_admin() && $query->is_main_query() && $query->is_author()) {
                    $query->set('author', 0);
                    $query->set('post__in', [0]);
                }
            });
        }
    }
}
