<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Cli;

use CorbiDev\Theme\Config\ConfigSchema;

final class ExportConfigCommand
{
    public static function run(array $argv): int
    {
        if (PHP_SAPI !== 'cli') {
            fwrite(STDERR, "This command must be run from the CLI.\n");
            return 1;
        }

        $script = array_shift($argv); // script name

        $configFile = $argv[0] ?? null;
        if ($configFile === null) {
            fwrite(STDERR, "Usage: php {$script} path/to/config.php [environment]" . PHP_EOL);
            return 1;
        }

        if (!is_file($configFile)) {
            fwrite(STDERR, "Config file not found: {$configFile}\n");
            return 1;
        }

        $environment = $argv[1] ?? 'cli';

        /** @var array $config */
        $config = require $configFile;

        if (!\is_array($config)) {
            fwrite(STDERR, 'Config file must return an array.' . PHP_EOL);
            return 1;
        }

        $schema = new ConfigSchema();

        try {
            $validated = $schema->validate($config, '0.1.0', $environment);
        } catch (\Throwable $e) {
            fwrite(STDERR, 'Invalid configuration: ' . $e->getMessage() . PHP_EOL);
            return 1;
        }

        $payload = [
            'theme'          => $validated->getTheme(),
            'text_domain'    => $validated->getTextDomain(),
            'config_version' => $validated->getConfigVersion(),
            'validation_mode'=> $validated->getValidationMode(),
            'feature_flags'  => $validated->getFeatureFlags(),
            'paths'          => $validated->getPaths(),
            'options'        => $validated->getOptions(),
        ];

        fwrite(STDOUT, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

        return 0;
    }
}
