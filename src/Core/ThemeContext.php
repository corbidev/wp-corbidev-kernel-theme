<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Core;

final class ThemeContext
{
    public function __construct(
        private readonly string $theme,
        private readonly string $textDomain,
        private readonly string $configVersion,
        private readonly array $featureFlags,
        private readonly array $paths,
        private readonly array $options,
        private readonly string $environment,
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

    public function getEnvironment(): string
    {
        return $this->environment;
    }
}
