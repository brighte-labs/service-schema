<?php

namespace ServiceSchema\Event;

use ServiceSchema\Json\Exception\JsonException;
use ServiceSchema\Json\JsonReader;

class MessageFactory
{
    /**
     * @param string|null $json
     * @return false|\ServiceSchema\Event\Message
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function createMessage(string $json = null)
    {
        if (empty($json)) {
            throw new JsonException(JsonException::MISSING_JSON_CONTENT);
        }

        $object = JsonReader::decode($json);

        if (!$this->validate($object)) {
            throw new JsonException(JsonException::INVALID_JSON_CONTENT . $json);
        }

        return new Message([
            'id' => isset($object->id) ? $object->id : null,
            'event' => isset($object->event) ? $object->event : null,
            'time' => isset($object->time) ? $object->time : null,
            'payload' => isset($object->payload) ? $object->payload : null,
            'source' => isset($object->source) ? $object->source : null,
            'description' => isset($object->description) ? $object->description : null,
            'status' => isset($object->status) ? $object->status : null,
            'sagaId' => isset($object->sagaId) ? $object->sagaId : null,
            'attributes' => isset($object->attributes) ? (array) $object->attributes : null
        ]);
    }

    /**
     * @param null|\stdClass $object
     * @return bool
     */
    public function validate(\stdClass $object = null)
    {
        if (!is_object($object)) {
            return false;
        }

        return true;
    }
}
