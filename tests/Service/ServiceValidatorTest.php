<?php

namespace ServiceSchema\Tests\Service;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ServiceSchema\Json\JsonReader;
use ServiceSchema\Service\Exception\ServiceException;
use ServiceSchema\Service\ServiceValidator;
use ServiceSchema\Tests\Service\Samples\CreateContact;

class ServiceValidatorTest extends TestCase
{
    protected $testDir;
    /** @var ServiceValidator */
    protected $serviceValidator;

    public function setUp(): void
    {
        parent::setUp();
        $this->testDir = dirname(dirname(__FILE__));
        $this->serviceValidator = new ServiceValidator();
    }

    /**
     * @covers \ServiceSchema\Service\ServiceValidator::validate
     * @covers \ServiceSchema\Service\ServiceValidator::getValidator
     * @covers \ServiceSchema\Service\ServiceValidator::setValidator
     * @covers \ServiceSchema\Service\ServiceValidator::getSchemaDir
     * @covers \ServiceSchema\Service\ServiceValidator::setSchemaDir
     * @throws \ServiceSchema\Json\Exception\JsonException
     * @throws \ServiceSchema\Service\Exception\ServiceException
     */
    public function testValidate()
    {
        $file = $this->testDir . "/jsons/messages/Users.afterSaveCommit.Create.json";
        $jsonObject = JsonReader::decode(JsonReader::read($file));
        $service = new CreateContact(new NullLogger());
        $service->setJsonSchema($this->testDir . "/jsons/schemas/CreateContact.json");
        $validator = $this->serviceValidator->validate($jsonObject, $service);
        $this->assertTrue($validator->isValid());

        $validator = $this->serviceValidator->getValidator();
        $this->serviceValidator->setValidator($validator);
        $this->assertSame($validator, $this->serviceValidator->getValidator());

        $this->serviceValidator->setSchemaDir($this->testDir);
        $schemaDir = $this->serviceValidator->getSchemaDir();
        $this->assertSame($schemaDir, $this->serviceValidator->getSchemaDir());
    }

    public function testValidateNullJson()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage(ServiceException::MISSING_JSON_STRING);
        $this->serviceValidator->validate();
    }

    public function testValidateNullService()
    {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage(ServiceException::MISSING_SERVICE_SCHEMA);
        $json = json_decode('{}');
        $this->serviceValidator->validate($json);
    }
}
