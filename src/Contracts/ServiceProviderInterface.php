<?php
namespace CorbiDev\Kernel\Contracts;

use CorbiDev\Kernel\Container\Container;

interface ServiceProviderInterface
{
    public function register(Container $container): void;
    public function boot(Container $container): void;
}
