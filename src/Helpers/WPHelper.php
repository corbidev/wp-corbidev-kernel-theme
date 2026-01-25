<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Helpers;

final class WPHelper
{
    public static function addAction(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        if (\function_exists('add_action')) {
            \add_action($hook, $callback, $priority, $acceptedArgs);
        }
    }

    public static function addFilter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        if (\function_exists('add_filter')) {
            \add_filter($hook, $callback, $priority, $acceptedArgs);
        }
    }

    public static function hasFilter(string $hook, ?callable $callback = null): bool
    {
        if (!\function_exists('has_filter')) {
            return false;
        }

        if ($callback === null) {
            return (bool) \has_filter($hook);
        }

        return (bool) \has_filter($hook, $callback);
    }

    public static function enqueueStyle(string $handle, string $src, array $deps = [], $ver = null, string $media = 'all'): void
    {
        if (!\function_exists('wp_enqueue_style')) {
            return;
        }

        \wp_enqueue_style($handle, $src, $deps, $ver, $media);
    }

    public static function enqueueScript(string $handle, string $src, array $deps = [], $ver = null, bool $inFooter = true): void
    {
        if (!\function_exists('wp_enqueue_script')) {
            return;
        }

        \wp_enqueue_script($handle, $src, $deps, $ver, $inFooter);
    }

    public static function loadThemeTextdomain(string $textDomain, ?string $path = null): void
    {
        if (!\function_exists('load_theme_textdomain')) {
            return;
        }

        if ($path !== null) {
            \load_theme_textdomain($textDomain, $path);
        } else {
            \load_theme_textdomain($textDomain);
        }
    }

    public static function isMultisite(): bool
    {
        return \function_exists('is_multisite') && \is_multisite();
    }

    public static function getMainNetworkId(): ?int
    {
        if (!\function_exists('get_main_network_id')) {
            return null;
        }

        return (int) \get_main_network_id();
    }

    public static function getMainSiteId(int $networkId): ?int
    {
        if (!\function_exists('get_main_site_id')) {
            return null;
        }

        return (int) \get_main_site_id($networkId);
    }

    public static function getCurrentBlogId(): ?int
    {
        if (!\function_exists('get_current_blog_id')) {
            return null;
        }

        return (int) \get_current_blog_id();
    }

    public static function getSites(array $args = []): array
    {
        if (!\function_exists('get_sites')) {
            return [];
        }

        return \get_sites($args);
    }

    public static function switchToBlog(int $blogId): void
    {
        if (!\function_exists('switch_to_blog')) {
            return;
        }

        \switch_to_blog($blogId);
    }

    public static function restoreCurrentBlog(): void
    {
        if (!\function_exists('restore_current_blog')) {
            return;
        }

        \restore_current_blog();
    }

    public static function updateOption(string $option, $value): void
    {
        if (!\function_exists('update_option')) {
            return;
        }

        \update_option($option, $value);
    }

    public static function getRole(string $role)
    {
        if (!\function_exists('get_role')) {
            return null;
        }

        return \get_role($role);
    }

    public static function addRole(string $role, string $label, array $capabilities = []): void
    {
        if (!\function_exists('add_role')) {
            return;
        }

        \add_role($role, $label, $capabilities);
    }

    public static function isAdmin(): bool
    {
        return \function_exists('is_admin') && \is_admin();
    }
}
