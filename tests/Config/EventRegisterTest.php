<?php

namespace ServiceSchema\Tests\Config;

use PHPUnit\Framework\TestCase;
use ServiceSchema\Config\EventRegister;

class EventRegisterTest extends TestCase
{
    protected $testDir;

    /** @var EventRegister $eventRegister */
    protected $eventRegister;

    /**
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->testDir = dirname(dirname(__FILE__));
        $this->eventRegister = new EventRegister([$this->testDir . "/jsons/configs/events.json"]);
    }

    /**
     * @covers \ServiceSchema\Config\EventRegister::loadEvents
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testLoadEvents()
    {
        $this->eventRegister->loadEvents();
        $events = $this->eventRegister->getEvents();
        $this->assertTrue(is_array($events));
        $this->assertTrue(isset($events["Users.afterSaveCommit.Create"]));
        $this->assertTrue(isset($events["Users.afterSaveCommit.Update"]));
    }

    /**
     * @covers \ServiceSchema\Config\EventRegister::registerEvent
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testRegisterEvent()
    {
        $this->eventRegister->loadEvents();
        $this->eventRegister->registerEvent("Event.Name", ["SomeServiceClass"]);
        $events = $this->eventRegister->getEvents();
        $this->assertTrue(is_array($events));
        $this->assertTrue(isset($events["Event.Name"]));
        $this->assertEquals(["SomeServiceClass"], $events["Event.Name"]);
    }

    /**
     * @covers \ServiceSchema\Config\EventRegister::retrieveEvent
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testRetrieveEvent()
    {
        $this->eventRegister->loadEvents();
        $this->eventRegister->registerEvent("Event.Name", ["SomeServiceClass"]);
        $event = $this->eventRegister->retrieveEvent("Event.Name");
        $this->assertTrue(is_array($event));
        $this->assertTrue(isset($event["Event.Name"]));
        $this->assertEquals(["SomeServiceClass"], $event["Event.Name"]);
    }

    /**
     * @covers \ServiceSchema\Config\EventRegister::getConfigs
     * @covers \ServiceSchema\Config\EventRegister::setConfigs
     * @covers \ServiceSchema\Config\EventRegister::getEvents
     * @covers \ServiceSchema\Config\EventRegister::setEvents
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testGetterAndSetters()
    {
        $this->eventRegister->loadEvents();
        $this->eventRegister->registerEvent("Event.Name", ["SomeServiceClass"]);

        $entity = $this->eventRegister->getConfigs();
        $this->eventRegister->setConfigs($entity);
        $this->assertSame($entity, $this->eventRegister->getConfigs());

        $entity = $this->eventRegister->getEvents();
        $this->eventRegister->setEvents($entity);
        $this->assertSame($entity, $this->eventRegister->getEvents());
    }
}
