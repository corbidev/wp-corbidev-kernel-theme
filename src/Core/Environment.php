<?php
namespace CorbiDev\Kernel\Core;

class Environment
{
    private string $env;

    public function __construct(string $env = 'prod')
    {
        $this->env = strtolower($env);
    }

    public static function detect(array $config = []): self
    {
        if (isset($config['env'])) {
            return new self($config['env']);
        }

        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            return new self('dev');
        }

        return new self('prod');
    }

    public function get(): string
    {
        return $this->env;
    }
}
