<?php
use PHPUnit\Framework\TestCase;
use CorbiDev\Kernel\Core\Application;

class ApplicationTest extends TestCase
{
    public function testApplicationCreation()
    {
        $app = Application::create(['env' => 'dev']);
        $this->assertInstanceOf(Application::class, $app);
    }
}
