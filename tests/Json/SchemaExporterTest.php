<?php

namespace ServiceSchema\Tests\Json;

use PHPUnit\Framework\TestCase;
use ServiceSchema\Json\JsonReader;
use ServiceSchema\Json\SchemaExporter;
use ServiceSchema\Main\Processor;

class SchemaExporterTest extends TestCase
{
    /** @var string */
    protected $testDir;

    /** @var string */
    protected $message;

    /**
     * @var $processor \ServiceSchema\Main\Processor
     */
    protected $processor;

    /**
     * @var $schemaExporter \ServiceSchema\Json\SchemaExporter
     */
    protected $schemaExporter;


    public function setUp(): void
    {
        parent::setUp();
        $this->testDir = dirname(dirname(__FILE__));
        $this->processor = new Processor(
            [$this->testDir . "/jsons/configs/events.json"],
            [$this->testDir . "/jsons/configs/services.json"],
            $this->testDir
        );
        $this->message = JsonReader::read($this->testDir . "/jsons/messages/Users.afterSaveCommit.Create.json");
        $this->schema = JsonReader::read($this->testDir . "/jsons/schemas/CreateContact.json");
    }

    /**
     * @covers ServiceSchema\Json\SchemaExporter::__construct
     * @covers ServiceSchema\Json\SchemaExporter::export
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testExport()
    {
        $this->schemaExporter = new SchemaExporter($this->processor);

        $result = $this->schemaExporter->export(schemaExporter::RETURN_JSON);
        $this->assertStringContainsString(
            '{"CreateContact":{"type":"object","properties":{"event":{"type":"string","minLength":0,"maxLength":256}',
            $result
        );
    }

    public function testExportArray()
    {
        $this->schemaExporter = new SchemaExporter($this->processor);

        $result = $this->schemaExporter->export(schemaExporter::RETURN_ARRAY);
        $expected = [
            'type' => 'string',
            'minLength' => 0,
            'maxLength' => 256,
        ];

        $this->assertEquals($expected, $result['CreateContact']['properties']['event']);
    }

    public function testExportEventSchema()
    {
        $schemas = SchemaExporter::exportEventSchema(__DIR__ . '/../jsons/schemas/');
        $expected = ['CreateContact', 'CreateTask', 'UpdateContact'];
        $found = array_intersect($expected, array_keys($schemas));
        $this->assertEquals($expected, $found);
    }
}
