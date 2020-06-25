<?php

namespace ServiceSchema\Config;

use ServiceSchema\Json\JsonReader;

class ServiceRegister
{
    /** @var array $configs */
    protected $configs = [];

    /** @var array $services */
    protected $services = [];

    public const INDEX_SERVICE = "service";
    public const INDEX_SCHEMA = "schema";
    public const INDEX_CALLBACKS = "callbacks";

    /**
     * ServiceRegister constructor.
     *
     * @param array|null $configs
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function __construct(array $configs = null)
    {
        $this->configs = $configs;
    }

    /**
     * @return \ServiceSchema\Config\ServiceRegister
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function loadServices()
    {
        if (empty($this->configs)) {
            return $this;
        }
        foreach ($this->configs as $config) {
            $rows = JsonReader::decode(JsonReader::read($config), true);
            foreach ($rows as $row) {
                if (isset($row[self::INDEX_SERVICE]) && isset($row[self::INDEX_SCHEMA])) {
                    $this->registerService(
                        $row[self::INDEX_SERVICE],
                        $row[self::INDEX_SCHEMA],
                        isset($row[self::INDEX_CALLBACKS]) ? $row[self::INDEX_CALLBACKS] : null
                    );
                }
            }
        }

        return $this;
    }

    /**
     * @param string|null $serviceName
     * @param string|null $schema
     * @param array|null $callbacks
     * @return \ServiceSchema\Config\ServiceRegister
     */
    public function registerService(string $serviceName = null, string $schema = null, array $callbacks = null)
    {
        if (!isset($this->services[$serviceName])) {
            $this->services[$serviceName] = [self::INDEX_SCHEMA => $schema, self::INDEX_CALLBACKS => $callbacks];
        }

        return $this;
    }

    /**
     * @param string|null $serviceName
     * @return array|null
     */
    public function retrieveService(string $serviceName = null)
    {
        if (isset($this->services[$serviceName])) {
            return [$serviceName => $this->services[$serviceName]];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getConfigs(): array
    {
        return $this->configs;
    }

    /**
     * @param array $configs
     * @return \ServiceSchema\Config\ServiceRegister
     */
    public function setConfigs(array $configs = null)
    {
        $this->configs = $configs;

        return $this;
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param array $services
     * @return \ServiceSchema\Config\ServiceRegister
     */
    public function setServices(array $services = null)
    {
        $this->services = $services;

        return $this;
    }
}
