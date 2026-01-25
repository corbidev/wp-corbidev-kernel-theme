<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Config;

final class ConfigExporter
{
    public static function toJson(ValidatedConfig $validatedConfig): string
    {
        $data = [
            'theme'          => $validatedConfig->getTheme(),
            'text_domain'    => $validatedConfig->getTextDomain(),
            'config_version' => $validatedConfig->getConfigVersion(),
            'validation_mode'=> $validatedConfig->getValidationMode(),
            'feature_flags'  => $validatedConfig->getFeatureFlags(),
            'paths'          => $validatedConfig->getPaths(),
            'options'        => $validatedConfig->getOptions(),
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }
}
