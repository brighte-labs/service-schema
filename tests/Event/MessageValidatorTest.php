<?php

namespace ServiceSchema\Tests\Event;

use PHPUnit\Framework\TestCase;
use ServiceSchema\Event\Exception\MessageValidatorException;
use ServiceSchema\Event\MessageFactory;
use ServiceSchema\Event\MessageInterface;
use ServiceSchema\Event\MessageValidator;
use ServiceSchema\Json\JsonReader;

class MessageValidatorTest extends TestCase
{
    public function testValidate()
    {
        $json = JsonReader::read(__DIR__ . '/../jsons/messages/Users.afterSaveCommit.Create.json');
        $message = (new MessageFactory())->createMessage($json);
        $schema = __DIR__ . '/../jsons/schemas/CreateContact.json';
        $this->assertTrue(MessageValidator::validate($message, $schema));
    }

    public function testValidateInvalid()
    {
        $json = JsonReader::read(__DIR__ . '/../jsons/messages/Users.afterSaveCommit.Create.Failed.json');
        $message = (new MessageFactory())->createMessage($json);
        $schema = __DIR__ . '/../jsons/schemas/CreateContact.json';
        $this->expectException(MessageValidatorException::class);
        $this->expectExceptionMessage(MessageValidatorException::INVALIDATED_EVENT_MESSAGE);
        MessageValidator::validate($message, $schema);
    }

    public function testValidateInvalidJson()
    {
        $message = $this->createMock(MessageInterface::class);
        $message->expects($this->once())->method('toJson')->willReturn('invalid-json');
        $this->expectException(MessageValidatorException::class);
        $this->expectExceptionMessage(MessageValidatorException::INVALID_JSON_STRING);
        MessageValidator::validate($message);
    }

    public function testValidateMissingSchema()
    {
        $json = JsonReader::read(__DIR__ . '/../jsons/messages/Users.afterSaveCommit.Create.json');
        $message = (new MessageFactory())->createMessage($json);
        $this->expectException(MessageValidatorException::class);
        $this->expectExceptionMessage(MessageValidatorException::MISSING_EVENT_SCHEMA);
        $this->assertTrue(MessageValidator::validate($message));
    }
}
