<?php

namespace ServiceSchema\Service\Exception;

use ServiceSchema\Exception\ServiceSchemaException;

class ServiceException extends ServiceSchemaException
{
    public const INVALID_SERVICE_CLASS = "Invalid service class: ";
    public const MISSING_SERVICE_SCHEMA = "Service schema is missing.";
    public const MISSING_JSON_STRING = "Json string is missing.";
    public const INVALIDATED_JSON_STRING = "Json string does not pass schema validation. Schema: %s. Errors: %s";
}
