<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Bootstrap;

final class Autoloader
{
    public static function register(): void
    {
        // Autoload géré principalement par Composer.
        // Ce hook existe pour des extensions éventuelles spécifiques au Kernel.
    }
}
