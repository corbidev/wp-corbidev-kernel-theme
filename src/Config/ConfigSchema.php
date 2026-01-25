<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Config;

final class ConfigSchema
{
    /**
     * Matrice minimale Kernel × config_version.
     *
     * Cette structure doit être maintenue en cohérence avec le RAG
     * rag-corbidev-theme-kernel-compatibility.md.
     *
     * Clé : config_version
     * Valeurs :
     *  - state : active|migratable|deprecated|removed
     *  - allow_tolerant : bool
     *  - tolerant_min_kernel : string|null (version minimale du Kernel pour tolérant)
     *  - tolerant_max_kernel : string|null (version maximale du Kernel pour tolérant)
     */
    private const CONFIG_COMPAT = [
        // Exemple : contrat courant, strict uniquement.
        '1.0' => [
            'state'               => 'active',
            'allow_tolerant'      => false,
            'tolerant_min_kernel' => null,
            'tolerant_max_kernel' => null,
        ],
        // Exemple : ancienne version migratable avec fenêtre tolérante bornée.
        '0.9' => [
            'state'               => 'migratable',
            'allow_tolerant'      => true,
            'tolerant_min_kernel' => '0.1.0',
            'tolerant_max_kernel' => '0.1.0',
        ],
    ];

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

        $this->assertCompatibility(
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

    private function assertCompatibility(
        string $kernelVersion,
        string $configVersion,
        string $validationMode,
        string $environment,
    ): void {
        if (!isset(self::CONFIG_COMPAT[$configVersion])) {
            throw new \InvalidArgumentException('Unknown or unsupported config_version for Kernel configuration.');
        }

        $definition = self::CONFIG_COMPAT[$configVersion];
        $state = $definition['state'] ?? 'removed';
        $allowTolerant = (bool) ($definition['allow_tolerant'] ?? false);
        $minKernel = $definition['tolerant_min_kernel'] ?? null;
        $maxKernel = $definition['tolerant_max_kernel'] ?? null;

        if ($state === 'removed') {
            throw new \InvalidArgumentException('config_version is removed and cannot be used.');
        }

        if ($state === 'deprecated' && $validationMode === 'tolerant') {
            throw new \InvalidArgumentException('Tolerant mode is not allowed for deprecated config_version.');
        }

        $isProductionEnv = \in_array($environment, ['production', 'prod'], true);

        if ($validationMode === 'tolerant') {
            if ($isProductionEnv) {
                throw new \InvalidArgumentException('Tolerant validation_mode is forbidden in production environment.');
            }

            if ($state !== 'migratable') {
                throw new \InvalidArgumentException('Tolerant validation_mode is only allowed for migratable config_version.');
            }

            if (!$allowTolerant) {
                throw new \InvalidArgumentException('Tolerant validation_mode is not allowed for this config_version.');
            }

            if ($minKernel === null || $maxKernel === null) {
                throw new \InvalidArgumentException('Tolerant validation_mode requires a bounded Kernel window for this config_version.');
            }

            if (\version_compare($kernelVersion, $minKernel, '<') || \version_compare($kernelVersion, $maxKernel, '>')) {
                throw new \InvalidArgumentException('Tolerant validation_mode is not allowed for this Kernel version with the given config_version.');
            }
        }

        // Le paramètre $kernelVersion est prévu pour une gestion future
        // des fenêtres Kernel × config_version, conformément au RAG.
        // La logique fine pourra être étendue ici sans changer la signature.
    }
}
