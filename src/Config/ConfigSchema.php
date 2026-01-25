<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Config;

final class ConfigSchema
{
    public function validate(array $config, string $kernelVersion, string $environment): ValidatedConfig
    {
        $theme = $this->extractString($config, 'theme');
        $textDomain = $this->extractString($config, 'text_domain');
        $configVersion = $this->extractString($config, 'config_version');

        $validationMode = $config['validation_mode'] ?? null;
        if ($validationMode !== null && !\in_array($validationMode, ['strict', 'tolerant'], true)) {
            throw new \InvalidArgumentException('Invalid validation_mode value.');
        }

        $featureFlags = $config['feature_flags'] ?? [];
        if (!\is_array($featureFlags)) {
            throw new \InvalidArgumentException('feature_flags must be an array.');
        }

        $paths = $config['paths'] ?? [];
        if (!\is_array($paths)) {
            throw new \InvalidArgumentException('paths must be an array.');
        }

        $options = $config['options'] ?? [];
        if (!\is_array($options)) {
            throw new \InvalidArgumentException('options must be an array.');
        }

        $resolvedValidationMode = $validationMode ?? 'strict';

        $this->assertRootKeys($config, $resolvedValidationMode);
        $this->assertFeatureFlags($featureFlags);
        $this->assertPaths($paths);

        (new ConfigVersionPolicy())->assertCompatible(
            $kernelVersion,
            $configVersion,
            $resolvedValidationMode,
            $environment,
        );

        return new ValidatedConfig(
            $theme,
            $textDomain,
            $configVersion,
            $resolvedValidationMode,
            $featureFlags,
            $paths,
            $options,
        );
    }

    private function extractString(array $config, string $key): string
    {
        if (!\array_key_exists($key, $config) || !\is_string($config[$key]) || $config[$key] === '') {
            throw new \InvalidArgumentException(sprintf('Missing or invalid "%s" in Kernel configuration.', $key));
        }

        return $config[$key];
    }

    private function assertRootKeys(array $config, string $validationMode): void
    {
        $allowedKeys = [
            'theme',
            'text_domain',
            'config_version',
            'validation_mode',
            'feature_flags',
            'paths',
            'options',
        ];

        $unknownKeys = array_diff(array_keys($config), $allowedKeys);

        if ($unknownKeys === []) {
            return;
        }

        if ($validationMode === 'strict') {
            throw new \InvalidArgumentException('Unknown configuration keys are not allowed in strict mode.');
        }

        // En mode tolérant, les clés inconnues sont ignorées (violation mineure).
    }

    private function assertFeatureFlags(array $featureFlags): void
    {
        foreach ($featureFlags as $key => $value) {
            if (!\is_string($key) || $key === '') {
                throw new \InvalidArgumentException('feature_flags keys must be non-empty strings.');
            }

            if (!\is_bool($value) && !\is_int($value) && !\is_string($value)) {
                throw new \InvalidArgumentException('feature_flags values must be bool, int or string.');
            }
        }
    }

    private function assertPaths(array $paths): void
    {
        foreach ($paths as $key => $value) {
            if (!\is_string($key) || $key === '') {
                throw new \InvalidArgumentException('paths keys must be non-empty strings.');
            }

            if (!\is_string($value) || $value === '') {
                throw new \InvalidArgumentException('paths values must be non-empty strings.');
            }
        }
    }
}
