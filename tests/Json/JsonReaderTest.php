<?php

namespace ServiceSchema\Tests\Json;

use PHPUnit\Framework\TestCase;
use ServiceSchema\Json\Exception\JsonException;
use ServiceSchema\Json\JsonReader;

class JsonReaderTest extends TestCase
{
    /** @var string */
    protected $testDir;

    public function setUp()
    {
        parent::setUp();
        $this->testDir = dirname(dirname(__FILE__));
    }

    /**
     * @covers \ServiceSchema\Json\JsonReader::read
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testReadFailed()
    {
        $this->expectException(JsonException::class);
        JsonReader::read(null);

        $this->expectException(JsonException::class);
        JsonReader::read("someinvalidfile");
    }

    /**
     * @covers \ServiceSchema\Json\JsonReader::decode
     * @covers \ServiceSchema\Json\JsonReader::read
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testDecodeFailed()
    {
        $this->expectException(JsonException::class);
        JsonReader::decode(null);
    }

    /**
     * @covers \ServiceSchema\Json\JsonReader::read
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testRead()
    {
        $file = $this->testDir . "/jsons/messages/Users.afterSaveCommit.Create.json";
        $json = JsonReader::read($file);
        $this->assertTrue(is_string($json));
    }

    public function testReadInvalidFile()
    {
        $this->expectException(JsonException::class);
        JsonReader::read('invalid-file');
    }

    /**
     * @covers \ServiceSchema\Json\JsonReader::read
     * @covers \ServiceSchema\Json\JsonReader::decode
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testDecode()
    {
        $file = $this->testDir . "/jsons/messages/Users.afterSaveCommit.Create.json";
        $json = JsonReader::read($file);
        $object = JsonReader::decode($json);

        $this->assertTrue(is_object($object));
        $this->assertEquals("Users.afterSaveCommit.Create", $object->event);
        $this->assertEquals("20190726032212", $object->time);
        $this->assertTrue(isset($object->payload));
        $this->assertEquals("Ken", $object->payload->user->data->name);
        $this->assertTrue(isset($object->payload->account->data->name));
        $this->assertEquals("Brighte", $object->payload->account->data->name);
    }

    /**
     * @covers \ServiceSchema\Json\JsonReader::encode
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testEncodeFailed()
    {
        $this->expectException(JsonException::class);
        JsonReader::encode(null);
    }

    /**
     * @covers \ServiceSchema\Json\JsonReader::encode
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testEncode()
    {
        $array = ["name" => "Ken"];
        $json = JsonReader::encode($array);
        $this->assertTrue(is_string($json));
    }

    /**
     * @covers \ServiceSchema\Json\JsonReader::save
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testSave()
    {
        $file = $this->testDir . "/jsons/JsonReader/testSave.json";
        $array = ["name" => "Ken"];
        $json = JsonReader::encode($array);
        JsonReader::save($file, $json);
        $contents = file_get_contents($file);
        $this->assertSame('{"name":"Ken"}', $contents);
    }

    public function testSaveEmptyFile()
    {
        $this->expectException(JsonException::class);
        JsonReader::save();
    }

    public function testSaveEmptyContent()
    {
        $this->expectException(JsonException::class);
        JsonReader::save('file-name');
    }
}
