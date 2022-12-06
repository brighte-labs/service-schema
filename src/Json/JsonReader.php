<?php

namespace ServiceSchema\Json;

use ServiceSchema\Json\Exception\JsonException;

class JsonReader implements JsonReaderInterface
{
    /**
     * @param string|null $file
     * @return string
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public static function read(string $file = null)
    {
        if (empty($file)) {
            throw new JsonException(JsonException::MISSING_JSON_FILE);
        }

        if (!is_file($file)) {
            throw new JsonException(JsonException::INVALID_JSON_FILE . $file);
        }

        return file_get_contents($file);
    }

    /**
     * @param string|null $json
     * @param bool $assoc
     * @return array|mixed
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public static function decode(string $json = null, bool $assoc = false)
    {
        if (empty($json)) {
            throw new JsonException(JsonException::MISSING_JSON_CONTENT);
        }

        return json_decode($json, $assoc);
    }

    /**
     * @param null|mixed $content
     * @param int $flag
     * @return false|string
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public static function encode($content = null, $flag = JSON_UNESCAPED_SLASHES)
    {
        if (empty($content)) {
            throw new JsonException(JsonException::MISSING_JSON_CONTENT);
        }

        return json_encode($content, $flag);
    }

    /**
     * @param string|null $file
     * @param string|null $content
     * @return bool|int
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public static function save(string $file = null, string $content = null)
    {
        if (empty($file)) {
            throw new JsonException(JsonException::MISSING_JSON_FILE);
        }

        if (empty($content)) {
            throw new JsonException(JsonException::MISSING_JSON_CONTENT);
        }

        return file_put_contents($file, $content);
    }
}
