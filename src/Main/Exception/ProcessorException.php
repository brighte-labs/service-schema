<?php

namespace ServiceSchema\Main\Exception;

use ServiceSchema\Exception\ServiceSchemaException;

class ProcessorException extends ServiceSchemaException
{
    public const FAILED_TO_CREATE_MESSAGE = "Failed to create message from json string: ";
    public const NO_REGISTER_EVENTS = "No registered events for: ";
    public const FILTERED_EVENT_ONLY = "Only filtered events are allowed to process: ";
}
