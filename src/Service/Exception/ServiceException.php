<?php

namespace ServiceSchema\Service\Exception;

use ServiceSchema\Exception\ServiceSchemaException;

class ServiceException extends ServiceSchemaException
{
    const INVALID_SERVICE_CLASS = "Invalid service class: ";
    const MISSING_SERVICE_SCHEMA = "Service schema is missing.";
    const MISSING_JSON_STRING = "Json string is missing.";
    const INVALIDATED_JSON_STRING = "Json string does not pass schema validation. Schema: %s. Errors: %s";
}
