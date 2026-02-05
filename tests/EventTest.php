<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use CorbiDev\Kernel\Events\Event;

/**
 * Tests pour la classe Event
 */
class EventTest extends TestCase
{
    public function testCanCreateEvent(): void
    {
        $event = new Event('test.event', ['key' => 'value']);

        $this->assertEquals('test.event', $event->getName());
        $this->assertEquals(['key' => 'value'], $event->getData());
    }

    public function testCanGetData(): void
    {
        $event = new Event('test', ['foo' => 'bar', 'number' => 42]);

        $this->assertEquals('bar', $event->get('foo'));
        $this->assertEquals(42, $event->get('number'));
    }

    public function testGetWithDefault(): void
    {
        $event = new Event('test', []);

        $this->assertEquals('default', $event->get('missing', 'default'));
        $this->assertNull($event->get('missing'));
    }

    public function testCanSetData(): void
    {
        $event = new Event('test', []);

        $result = $event->set('key', 'value');

        $this->assertSame($event, $result); // Retour fluent
        $this->assertEquals('value', $event->get('key'));
    }

    public function testHasKey(): void
    {
        $event = new Event('test', ['existing' => null]);

        $this->assertTrue($event->has('existing'));
        $this->assertFalse($event->has('missing'));
    }

    public function testStopPropagation(): void
    {
        $event = new Event('test');

        $this->assertFalse($event->isPropagationStopped());

        $result = $event->stopPropagation();

        $this->assertSame($event, $result); // Retour fluent
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testMergeData(): void
    {
        $event = new Event('test', ['a' => 1, 'b' => 2]);

        $result = $event->merge(['b' => 20, 'c' => 3]);

        $this->assertSame($event, $result); // Retour fluent
        $this->assertEquals(1, $event->get('a'));
        $this->assertEquals(20, $event->get('b')); // Écrasé
        $this->assertEquals(3, $event->get('c')); // Ajouté
    }

    public function testRemoveKey(): void
    {
        $event = new Event('test', ['keep' => 1, 'remove' => 2]);

        $result = $event->remove('remove');

        $this->assertSame($event, $result); // Retour fluent
        $this->assertTrue($event->has('keep'));
        $this->assertFalse($event->has('remove'));
    }

    public function testChaining(): void
    {
        $event = new Event('test');

        $event
            ->set('a', 1)
            ->set('b', 2)
            ->merge(['c' => 3])
            ->remove('b')
            ->stopPropagation();

        $this->assertEquals(1, $event->get('a'));
        $this->assertFalse($event->has('b'));
        $this->assertEquals(3, $event->get('c'));
        $this->assertTrue($event->isPropagationStopped());
    }
}
