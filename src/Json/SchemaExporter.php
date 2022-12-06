<?php

namespace ServiceSchema\Json;

use ServiceSchema\Config\ServiceRegister;
use ServiceSchema\Main\Processor;

class SchemaExporter
{
    /** @var \ServiceSchema\Main\Processor */
    protected $processor;

    public const SCHEMA_EXTENSION = 'json';
    public const RETURN_JSON = 1;
    public const RETURN_ARRAY = 2;

    /**
     * SchemaReader constructor.
     *
     * @param \ServiceSchema\Main\Processor|null $processor
     */
    public function __construct(Processor $processor = null)
    {
        $this->processor = $processor;
    }

    /**
     * @param int $returnType
     * @return array|string
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function export(int $returnType = self::RETURN_ARRAY)
    {
        $files = [];
        $services = $this->processor->getServiceRegister()->getServices();
        foreach ($services as $service) {
            $files[$service[ServiceRegister::INDEX_SCHEMA]] = $this->processor->getServiceValidator()->getSchemaDir() .
                $service[ServiceRegister::INDEX_SCHEMA];
        }

        $schemas = [];
        foreach ($files as $file) {
            $schemas[basename($file, '.' . self::SCHEMA_EXTENSION)] = JsonReader::decode(JsonReader::read($file), true);
        }

        switch ($returnType) {
            case self::RETURN_JSON:
                return JsonReader::encode($schemas);
                break;
            case self::RETURN_ARRAY:
            default:
                return $schemas;
                break;
        }
    }

    /**
     * @param string $dir
     * @return array|false|string
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public static function exportEventSchema(string $dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        $schemas = [];
        foreach ($files as $file) {
            $schemas[basename($file, '.' . self::SCHEMA_EXTENSION)] = JsonReader::decode(
                JsonReader::read($dir . '/' . $file),
                true
            );
        }

        return $schemas;
    }
}
