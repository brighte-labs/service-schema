<?php

namespace ServiceSchema\Config;

use ServiceSchema\Json\JsonReader;

class EventRegister
{
    /** @var array $configs */
    protected $configs = [];

    /** @var array $events */
    protected $events = [];

    public const INDEX_EVENT = "event";
    public const INDEX_SERVICES = "services";

    /**
     * EventRegister constructor.
     *
     * @param array|null $configs
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function __construct(array $configs = null)
    {
        $this->configs = $configs;
    }

    /**
     * @return \ServiceSchema\Config\EventRegister
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function loadEvents()
    {
        if (empty($this->configs)) {
            return $this;
        }

        foreach ($this->configs as $config) {
            $rows = JsonReader::decode(JsonReader::read($config), true);
            foreach ($rows as $row) {
                $eventName = $row[self::INDEX_EVENT];
                $services = $row[self::INDEX_SERVICES];
                $this->registerEvent($eventName, $services);
            }
        }

        return $this;
    }

    /**
     * @param string|null $eventName
     * @param array|null $services
     * @return \ServiceSchema\Config\EventRegister
     */
    public function registerEvent(string $eventName = null, array $services = null)
    {
        if (!isset($this->events[$eventName])) {
            $this->events[$eventName] = $services;
        } else {
            $this->events[$eventName] = array_unique(array_merge($this->events[$eventName], $services));
        }

        return $this;
    }

    /**
     * @param string|null $eventName
     * @return array|null
     */
    public function retrieveEvent(string $eventName = null)
    {
        if (isset($this->events[$eventName])) {
            return [$eventName => $this->events[$eventName]];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * @param array $configs
     * @return \ServiceSchema\Config\EventRegister
     */
    public function setConfigs(array $configs = null)
    {
        $this->configs = $configs;

        return $this;
    }

    /**
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param array $events
     * @return \ServiceSchema\Config\EventRegister
     */
    public function setEvents(array $events = null)
    {
        $this->events = $events;

        return $this;
    }
}
