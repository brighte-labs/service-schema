<?php

namespace ServiceSchema\Tests\Event;

use PHPUnit\Framework\TestCase;
use ServiceSchema\Event\Message;
use ServiceSchema\Event\MessageFactory;
use ServiceSchema\Json\Exception\JsonException;

class MessageFactoryTest extends TestCase
{
    protected $testDir;

    public function setUp()
    {
        parent::setUp();
        $this->testDir = dirname(dirname(__FILE__));
    }

    /**
     * @covers \ServiceSchema\Event\MessageFactory::createMessage
     * @covers \ServiceSchema\Event\MessageFactory::validate
     * @covers \ServiceSchema\Event\Message::getEvent
     * @covers \ServiceSchema\Event\Message::getTime
     * @covers \ServiceSchema\Event\Message::getPayload
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testCreateEvent()
    {
        $messageFactory = new MessageFactory();
        $json = '{"event":"Test.Event.Name","time":"SomeTimeString","payload":{"name":"Ken"}}';
        $message = $messageFactory->createMessage($json);
        $this->assertTrue($message instanceof Message);
        $this->assertEquals("Test.Event.Name", $message->getEvent());
        $this->assertEquals("SomeTimeString", $message->getTime());
        $this->assertEquals((object)["name" => "Ken"], $message->getPayload());
    }

    public function testCreateEventEmpty()
    {
        $this->expectException(JsonException::class);
        $this->expectExceptionMessage(JsonException::MISSING_JSON_CONTENT);
        (new MessageFactory)->createMessage('');
    }

    public function testCreateEventInvalid()
    {
        $this->expectException(JsonException::class);
        (new MessageFactory)->createMessage('invalid-json');
    }
}
