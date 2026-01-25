<?php

declare(strict_types=1);

namespace CorbiDev\Theme;

use CorbiDev\Theme\Config\ConfigSchema;
use CorbiDev\Theme\Core\ServiceContainer;
use CorbiDev\Theme\Core\ThemeContext;
use CorbiDev\Theme\Guards\EnvironmentGuard;
use CorbiDev\Theme\Guards\WordPressGuard;
use CorbiDev\Theme\Guards\RegressionGuard;
use CorbiDev\Theme\Bootstrap\Autoloader;
use CorbiDev\Theme\Bootstrap\Constants;
use CorbiDev\Theme\Bootstrap\Environment;
use CorbiDev\Theme\Core\HookRegistry;
use CorbiDev\Theme\Config\ValidatedConfig;

final class Kernel
{
    private const VERSION = '0.1.0';

    private static bool $booted = false;

    private static ?ServiceContainer $container = null;

    public static function boot(array $config): void
    {
        if (self::$booted) {
            return;
        }

        $environment = Environment::detect();

        $schema = new ConfigSchema();
        $validatedConfig = $schema->validate($config, self::VERSION, $environment);

        self::bootGuards($validatedConfig, $environment);
        self::bootBootstrap();
        self::bootCore($validatedConfig, $environment);
        self::bootServices();

        self::$booted = true;
    }

    public static function getContainer(): ServiceContainer
    {
        if (self::$container === null) {
            throw new \RuntimeException('Kernel has not been booted yet.');
        }

        return self::$container;
    }

    private static function bootGuards(ValidatedConfig $config, string $environment): void
    {
        EnvironmentGuard::assertCompatible();
        WordPressGuard::assertRunningInWordPress();
        RegressionGuard::assertNoRuntimeRegression();
    }

    private static function bootBootstrap(): void
    {
        Autoloader::register();

        Constants::defineKernelConstants(__DIR__ . '/..', self::VERSION);
    }

    private static function bootCore(ValidatedConfig $config, string $environment): void
    {
        $themeContext = new ThemeContext(
            $config->getTheme(),
            $config->getTextDomain(),
            $config->getConfigVersion(),
            $config->getFeatureFlags(),
            $config->getPaths(),
            $config->getOptions(),
            $environment,
        );

        $hookRegistry = new HookRegistry();

        self::$container = new ServiceContainer($themeContext, $hookRegistry);
    }

    private static function bootServices(): void
    {
        if (self::$container === null) {
            return;
        }

        self::$container->registerServices();
    }
}
