<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Config;

/**
 * Politique explicite de compatibilité entre le Kernel et les versions de config.
 *
 * Cette classe doit rester alignée avec le RAG
 * DOCS/RAG/rag-corbidev-theme-kernel-compatibility.md.
 */
final class ConfigVersionPolicy
{
    /**
     * Matrice minimale Kernel × config_version.
     *
     * Clé : config_version
     * Valeurs :
     *  - state : active|migratable|deprecated|removed
     *  - allow_tolerant : bool
     *  - tolerant_min_kernel : string|null (version minimale du Kernel pour tolérant)
     *  - tolerant_max_kernel : string|null (version maximale du Kernel pour tolérant)
     */
    private const CONFIG_COMPAT = [
        '1.0' => [
            'state'               => 'active',
            'allow_tolerant'      => false,
            'tolerant_min_kernel' => null,
            'tolerant_max_kernel' => null,
        ],
        '0.9' => [
            'state'               => 'migratable',
            'allow_tolerant'      => true,
            'tolerant_min_kernel' => '0.1.0',
            'tolerant_max_kernel' => '0.1.0',
        ],
    ];

    public function assertCompatible(
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
            // Verrouillage strict du mode tolérant :
            // - environnement non-prod
            // - version de config marquée migratable
            // - fenêtre Kernel × config_version explicitement bornée
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
    }
}
