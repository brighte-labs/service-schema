<?php

namespace ServiceSchema\Event\Exception;

use ServiceSchema\Exception\ServiceSchemaException;

class MessageValidatorException extends ServiceSchemaException
{
    public const INVALID_JSON_STRING = "Message->toJson is invalid Json string.";
    public const MISSING_EVENT_SCHEMA = "Event schema is missing.";
    public const INVALIDATED_EVENT_MESSAGE = "Event Message is not validated by event schema. Error: ";
}
