<?php
namespace tests\Zeedhi\Framework\Events;

use Zeedhi\Framework\Events;

class AbstractEventTest extends \PHPUnit\Framework\TestCase {

    /** @var EventImpl */
    protected $event;

    protected function setUp() {
        $this->event = new EventImpl();
    }

    public function testAddListener() {
        $listener = new ListenerImpl();
        $this->event->addListener($listener);
        $this->assertContains($listener, $this->event->getListeners());
    }

    public function testRemoveListener() {
        $listener = new ListenerImpl();
        $this->event->addListener($listener);
        $key = $this->event->removeListener($listener);
        $this->assertTrue(is_numeric($key));
        $this->assertNotContains($listener, $this->event->getListeners());
    }

    public function testTrigger() {
        $listener = new ListenerImpl();
        $this->event->addListener($listener);
        $this->event->trigger(array("fooBarBazQux"));
        $this->assertTrue($listener->isNotified());
    }

    public function testInvalidArguments() {
        $listener = new ListenerImpl();
        $this->event->addListener($listener);

        $eventClassName = get_class($this->event);
        $expectedMessage = "Can't trigger {$eventClassName}, because was given wrong parameters.";
        $this->expectException(Events\Exception::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->event->trigger(array(123));
        $this->assertFalse($listener->isNotified());
    }
}
