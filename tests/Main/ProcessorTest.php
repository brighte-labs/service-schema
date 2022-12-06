<?php

namespace ServiceSchema\Tests\Main;

use PHPUnit\Framework\TestCase;
use ServiceSchema\Event\Message;
use ServiceSchema\Event\MessageFactory;
use ServiceSchema\Event\MessageInterface;
use ServiceSchema\Json\JsonReader;
use ServiceSchema\Main\Exception\ProcessorException;
use ServiceSchema\Main\Processor;
use ServiceSchema\Service\Exception\ServiceException;
use ServiceSchema\Service\SagaInterface;

class ProcessorTest extends TestCase
{
    protected $testDir;

    /** @var Processor */
    protected $processor;

    public function setUp(): void
    {
        parent::setUp();
        $this->testDir = dirname(dirname(__FILE__));
        $this->processor = new Processor(
            [$this->testDir . "/jsons/configs/events.json"],
            [$this->testDir . "/jsons/configs/services.json"],
            $this->testDir
        );
    }

    /**
     * @throws \ServiceSchema\Json\Exception\JsonException
     * @throws \ServiceSchema\Main\Exception\ProcessorException
     * @throws \ServiceSchema\Service\Exception\ServiceException
     */
    public function testProcess()
    {
        $message = JsonReader::read($this->testDir . "/jsons/messages/Users.afterSaveCommit.Create.json");
        $result = $this->processor->process($message);
        $this->assertTrue(is_bool($result));

        $result = $this->processor->process($message, null, true);
        $this->assertInstanceOf(MessageInterface::class, $result);
    }

    /**
     * @throws \ServiceSchema\Json\Exception\JsonException
     * @throws \ServiceSchema\Main\Exception\ProcessorException
     * @throws \ServiceSchema\Service\Exception\ServiceException
     */
    public function testProcessFailed()
    {
        $message = JsonReader::read($this->testDir . "/jsons/messages/Users.afterSaveCommit.Create.Failed.json");
        $this->expectException(ServiceException::class);
        $this->processor->process($message);
    }

    public function testProcessFailedWithFilteredEvent()
    {
        $message = JsonReader::read($this->testDir . "/jsons/messages/Users.afterSaveCommit.Create.json");
        $this->expectException(ProcessorException::class);
        $this->processor->process($message, ['expected-events']);
    }

    public function testProcessFailedWithNoRegisteredEvent()
    {
        $message = JsonReader::read($this->testDir . "/jsons/messages/Unknown.json");
        $this->expectException(ProcessorException::class);
        $this->expectExceptionMessage(ProcessorException::NO_REGISTER_EVENTS . 'Unknown.Event');
        $this->processor->process($message);
    }

    public function testProcessFailedWithEmptyService()
    {
        $message = JsonReader::read($this->testDir . "/jsons/messages/Unknown.json");
        $this->processor->getEventRegister()->registerEvent('Unknown.Event', []);
        $this->assertTrue($this->processor->process($message));

        $this->processor->getEventRegister()->registerEvent('Unknown.Event', ['UnknownService']);
        $this->assertTrue($this->processor->process($message));

        $this->processor->getServiceRegister()->registerService('stdClass');
        $this->processor->getEventRegister()->registerEvent('Unknown.Event', ['stdClass']);
        $this->assertTrue($this->processor->process($message));
    }

    /**
     * @throws \ServiceSchema\Json\Exception\JsonException
     * @throws \ServiceSchema\Main\Exception\ProcessorException
     * @throws \ServiceSchema\Service\Exception\ServiceException
     */
    public function testRollback()
    {
        $message = JsonReader::read($this->testDir . "/jsons/messages/Users.afterSaveCommit.Create.json");
        $result = $this->processor->rollback($message);
        $this->assertTrue(is_bool($result));
    }

    public function testRollbackNoRegisteredEvent()
    {
        $message = JsonReader::read($this->testDir . "/jsons/messages/Unknown.json");
        $this->expectException(ProcessorException::class);
        $this->expectExceptionMessage(ProcessorException::NO_REGISTER_EVENTS);
        $this->processor->rollback($message);
    }

    public function testSettersAndGetters()
    {
        $eventRegister = $this->processor->getEventRegister();
        $this->processor->setEventRegister($eventRegister);
        $this->assertSame($eventRegister, $this->processor->getEventRegister());

        $serviceRegister = $this->processor->getServiceRegister();
        $this->processor->setServiceRegister($serviceRegister);
        $this->assertSame($serviceRegister, $this->processor->getServiceRegister());

        $messageFactory = $this->processor->getMessageFactory();
        $this->processor->setMessageFactory($messageFactory);
        $this->assertSame($messageFactory, $this->processor->getMessageFactory());

        $serviceFactory = $this->processor->getServiceFactory();
        $this->processor->setServiceFactory($serviceFactory);
        $this->assertSame($serviceFactory, $this->processor->getServiceFactory());

        $serviceValidator = $this->processor->getServiceValidator();
        $this->processor->setServiceValidator($serviceValidator);
        $this->assertSame($serviceValidator, $this->processor->getServiceValidator());
    }

    public function testRollbackService()
    {
        $json = JsonReader::read($this->testDir . "/jsons/messages/Users.afterSaveCommit.Create.json");
        $message = (new MessageFactory())->createMessage($json);
        $service = $this->createMock(SagaInterface::class);
        $service->expects($this->once())->method('rollback')->with($message)->willReturn(true);
        $service->expects($this->any())->method('getJsonSchema')->willReturn('/jsons/schemas/CreateContact.json');
        $result = $this->processor->rollbackService($message, $service);
        $this->assertTrue($result);
    }

    public function testRollbackServiceInvalid()
    {
        $json = JsonReader::read($this->testDir . "/jsons/messages/Users.afterSaveCommit.Create.Failed.json");
        $message = (new MessageFactory())->createMessage($json);
        $service = $this->createMock(SagaInterface::class);
        $service->expects($this->never())->method('rollback')->with($message);
        $service->expects($this->any())->method('getJsonSchema')->willReturn('/jsons/schemas/CreateContact.json');
        $this->expectException(ServiceException::class);
        $this->processor->rollbackService($message, $service);
    }

    public function testCreateMessage()
    {
        $json = JsonReader::read($this->testDir . "/jsons/messages/Users.afterSaveCommit.Create.json");
        $message = $this->processor->createMessage($json);
        $this->assertInstanceOf(Message::class, $message);
    }

    public function testCreateMessageInvalid()
    {
        $message = $this->createMock(Message::class);
        $this->assertSame($message, $this->processor->createMessage($message));

        $messageFactory = $this->createMock(MessageFactory::class);
        $messageFactory->expects($this->once())->method('createMessage')->willReturn(null);
        $this->processor->setMessageFactory($messageFactory);
        $this->expectException(ProcessorException::class);
        $this->processor->createMessage('{"event":"CreateContact"}');
    }
}
