<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Config;

final class ValidatedConfig
{
    public function __construct(
        private readonly string $theme,
        private readonly string $textDomain,
        private readonly string $configVersion,
        private readonly string $validationMode,
        private readonly array $featureFlags,
        private readonly array $paths,
        private readonly array $options,
    ) {
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function getTextDomain(): string
    {
        return $this->textDomain;
    }

    public function getConfigVersion(): string
    {
        return $this->configVersion;
    }

    public function getValidationMode(): string
    {
        return $this->validationMode;
    }

    public function getFeatureFlags(): array
    {
        return $this->featureFlags;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
