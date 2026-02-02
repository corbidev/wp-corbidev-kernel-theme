<?php
namespace CorbiDev\Kernel\Container;

class Container
{
    private array $services = [];

    public function set(string $id, mixed $service): void
    {
        $this->services[$id] = $service;
    }

    public function get(string $id): mixed
    {
        if (!isset($this->services[$id])) {
            throw new \RuntimeException("Service {$id} not found.");
        }
        return $this->services[$id];
    }
}
