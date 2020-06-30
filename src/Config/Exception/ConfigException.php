<?php

namespace ServiceSchema\Config\Exception;

use ServiceSchema\Exception\ServiceSchemaException;

class ConfigException extends ServiceSchemaException
{
    public const MISSING_EVENT_CONFIGS = "Event configs are missing.";
    public const MISSING_SERVICE_CONFIGS = "Service configs are missing.";
}
