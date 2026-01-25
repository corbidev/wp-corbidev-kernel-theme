<?php

declare(strict_types=1);

namespace CorbiDev\Theme\Core;

use CorbiDev\Theme\Helpers\WPHelper;

final class HookRegistry
{
    public function registerAction(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        WPHelper::addAction($hookName, $callback, $priority, $acceptedArgs);
    }

    public function registerFilter(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        WPHelper::addFilter($hookName, $callback, $priority, $acceptedArgs);
    }
}
