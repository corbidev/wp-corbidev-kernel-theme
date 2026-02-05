<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use CorbiDev\Kernel\Events\EventDispatcher;
use CorbiDev\Kernel\Events\Event;

/**
 * Tests pour EventDispatcher
 */
class EventDispatcherTest extends TestCase
{
    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
    }

    public function testCanRegisterListener(): void
    {
        $called = false;
        $this->dispatcher->on('test.event', function () use (&$called) {
            $called = true;
        });

        $this->assertTrue($this->dispatcher->hasListeners('test.event'));
        $this->dispatcher->dispatch('test.event');
        $this->assertTrue($called);
    }

    public function testListenerReceivesEventObject(): void
    {
        $receivedEvent = null;
        $this->dispatcher->on('test.event', function (Event $event) use (&$receivedEvent) {
            $receivedEvent = $event;
        });

        $this->dispatcher->dispatch('test.event', ['key' => 'value']);

        $this->assertInstanceOf(Event::class, $receivedEvent);
        $this->assertEquals('test.event', $receivedEvent->getName());
        $this->assertEquals('value', $receivedEvent->get('key'));
    }

    public function testPriorityOrdering(): void
    {
        $order = [];

        $this->dispatcher->on('test.priority', function () use (&$order) {
            $order[] = 'low';
        }, 1);

        $this->dispatcher->on('test.priority', function () use (&$order) {
            $order[] = 'high';
        }, 100);

        $this->dispatcher->on('test.priority', function () use (&$order) {
            $order[] = 'medium';
        }, 10);

        $this->dispatcher->dispatch('test.priority');

        $this->assertEquals(['high', 'medium', 'low'], $order);
    }

    public function testStopPropagation(): void
    {
        $firstCalled = false;
        $secondCalled = false;

        $this->dispatcher->on('test.stop', function (Event $event) use (&$firstCalled) {
            $firstCalled = true;
            $event->stopPropagation();
        }, 10);

        $this->dispatcher->on('test.stop', function () use (&$secondCalled) {
            $secondCalled = true;
        }, 5);

        $this->dispatcher->dispatch('test.stop');

        $this->assertTrue($firstCalled);
        $this->assertFalse($secondCalled);
    }

    public function testOnceListener(): void
    {
        $callCount = 0;
        $this->dispatcher->once('test.once', function () use (&$callCount) {
            $callCount++;
        });

        $this->dispatcher->dispatch('test.once');
        $this->dispatcher->dispatch('test.once');
        $this->dispatcher->dispatch('test.once');

        $this->assertEquals(1, $callCount);
    }

    public function testRemoveListener(): void
    {
        $callback = function () {};
        $this->dispatcher->on('test.remove', $callback);

        $this->assertTrue($this->dispatcher->hasListeners('test.remove'));

        $removed = $this->dispatcher->off('test.remove', $callback);

        $this->assertTrue($removed);
        $this->assertFalse($this->dispatcher->hasListeners('test.remove'));
    }

    public function testRemoveAllListeners(): void
    {
        $this->dispatcher->on('event1', function () {});
        $this->dispatcher->on('event2', function () {});

        $this->dispatcher->removeAllListeners();

        $this->assertFalse($this->dispatcher->hasListeners('event1'));
        $this->assertFalse($this->dispatcher->hasListeners('event2'));
    }

    public function testRemoveListenersForSpecificEvent(): void
    {
        $this->dispatcher->on('event1', function () {});
        $this->dispatcher->on('event2', function () {});

        $this->dispatcher->removeAllListeners('event1');

        $this->assertFalse($this->dispatcher->hasListeners('event1'));
        $this->assertTrue($this->dispatcher->hasListeners('event2'));
    }

    public function testGetListeners(): void
    {
        $callback1 = function () {};
        $callback2 = function () {};

        $this->dispatcher->on('test.get', $callback1, 10);
        $this->dispatcher->on('test.get', $callback2, 20);

        $listeners = $this->dispatcher->getListeners('test.get');

        $this->assertCount(2, $listeners);
        $this->assertSame($callback2, $listeners[0]); // Plus haute priorité en premier
        $this->assertSame($callback1, $listeners[1]);
    }

    public function testCountListeners(): void
    {
        $this->dispatcher->on('event1', function () {});
        $this->dispatcher->on('event1', function () {});
        $this->dispatcher->on('event2', function () {});

        $this->assertEquals(2, $this->dispatcher->countListeners('event1'));
        $this->assertEquals(1, $this->dispatcher->countListeners('event2'));
        $this->assertEquals(3, $this->dispatcher->countListeners());
    }

    public function testEventDataManipulation(): void
    {
        $this->dispatcher->on('test.data', function (Event $event) {
            $event->set('modified', true);
            $event->set('count', $event->get('count', 0) + 1);
        });

        $result = $this->dispatcher->dispatch('test.data', ['count' => 5]);

        $this->assertTrue($result->get('modified'));
        $this->assertEquals(6, $result->get('count'));
    }

    public function testMultipleListenersSameEvent(): void
    {
        $executions = [];

        $this->dispatcher->on('multi', function () use (&$executions) {
            $executions[] = 1;
        });

        $this->dispatcher->on('multi', function () use (&$executions) {
            $executions[] = 2;
        });

        $this->dispatcher->on('multi', function () use (&$executions) {
            $executions[] = 3;
        });

        $this->dispatcher->dispatch('multi');

        $this->assertCount(3, $executions);
    }

    public function testNoListenersDoesNotThrow(): void
    {
        $event = $this->dispatcher->dispatch('nonexistent.event');

        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals('nonexistent.event', $event->getName());
    }
}
