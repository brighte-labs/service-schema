<?php

namespace ServiceSchema\Json;

interface JsonReaderInterface
{
    /**
     * @param string|null $file
     * @return string
     */
    public static function read(string $file = null);

    /**
     * @param string|null $json
     * @param bool $assoc
     * @return array|mixed
     */
    public static function decode(string $json = null, bool $assoc = false);

    /**
     * @param null $content
     * @return false|string
     */
    public static function encode($content = null);
}
