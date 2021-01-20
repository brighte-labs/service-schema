<?php

namespace ServiceSchema\Tests\Event;

use PHPUnit\Framework\TestCase;
use ServiceSchema\Event\Message;

class MessageTest extends TestCase
{

    protected $testDir;

    public function setUp()
    {
        parent::setUp();
        $this->testDir = dirname(dirname(__FILE__));
    }

    /**
     * @covers \ServiceSchema\Event\Message::setPayload
     * @covers \ServiceSchema\Event\Message::setTime
     * @covers \ServiceSchema\Event\Message::setEvent
     * @covers \ServiceSchema\Event\Message::setId
     * @covers \ServiceSchema\Event\Message::getId
     * @covers \ServiceSchema\Event\Message::setStatus
     * @covers \ServiceSchema\Event\Message::getStatus
     * @covers \ServiceSchema\Event\Message::setDescription
     * @covers \ServiceSchema\Event\Message::getDescription
     * @covers \ServiceSchema\Event\Message::setSource
     * @covers \ServiceSchema\Event\Message::getSource
     * @covers \ServiceSchema\Event\Message::setSagaId
     * @covers \ServiceSchema\Event\Message::getSagaId
     * @covers \ServiceSchema\Event\Message::setAttribute
     * @covers \ServiceSchema\Event\Message::getAttribute
     * @covers \ServiceSchema\Event\Message::setAttributes
     * @covers \ServiceSchema\Event\Message::getAttributes
     * @covers \ServiceSchema\Event\Message::toJson
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testToJson()
    {
        $event = new Message();
        $event->setEvent("Users.afterSaveCommit.Create");
        $event->setTime("20190726032212");
        $event->setStatus('New');
        $event->setPayload(["user" => ["data" => ["name" => "Ken"]], "account" => ["data" => ["name" => "Brighte"]]]);
        $json = $event->toJson();
        $this->assertTrue(is_string($json));
        $this->assertEquals(
            json_encode(json_decode(file_get_contents(
                __DIR__ . '/../jsons/messages/Users.afterSaveCommit.Create.json'
            ))),
            $json
        );

        $event = new Message();
        $event->setId(111);
        $id = $event->getId();
        $this->assertSame($id, '111');

        $event->setStatus('status');
        $entity = $event->getStatus();
        $this->assertSame($entity, 'status');

        $event->setDescription('description');
        $entity = $event->getDescription();
        $this->assertSame($entity, 'description');

        $event->setSource('source');
        $entity = $event->getSource();
        $this->assertSame($entity, 'source');

        $event->setSagaId('sagaId');
        $event->setType('type1');
        $this->assertEquals('type1', $event->getType());
        $entity = $event->getSagaId();
        $this->assertSame($entity, 'sagaId');

        $event->setAttribute('attr', 'val');
        $entity = $event->getAttribute('attr');
        $this->assertSame($entity, 'val');

        $event->setAttributes(['attr', 'attr2']);
        $entity = $event->getAttributes();
        $this->assertSame($entity, ['attr', 'attr2']);
    }
}
