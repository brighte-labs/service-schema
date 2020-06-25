<?php

namespace ServiceSchema\Json\Exception;

use ServiceSchema\Exception\ServiceSchemaException;

class JsonException extends ServiceSchemaException
{
    public const INVALID_JSON_FILE = "Provided file is not a valid json file: ";
    public const MISSING_JSON_FILE = "Missing json file";
    public const MISSING_JSON_CONTENT = "Content is empty, please provide json content";
    public const INVALID_JSON_CONTENT = "Provided string is not valid json: ";
}
