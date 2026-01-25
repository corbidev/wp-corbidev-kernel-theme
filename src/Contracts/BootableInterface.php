<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Contracts;

interface BootableInterface
{
    public function boot(): void;
}
