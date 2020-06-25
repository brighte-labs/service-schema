<?php

namespace ServiceSchema\Event;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use ServiceSchema\Event\Exception\MessageValidatorException;
use ServiceSchema\Json\JsonReader;

class MessageValidator
{
    /**
     * @param \ServiceSchema\Event\MessageInterface|null $message
     * @param string|null $eventSchema
     * @return bool
     * @throws \ServiceSchema\Event\Exception\MessageValidatorException
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public static function validate(MessageInterface $message = null, ?string $eventSchema = null)
    {
        $jsonObject = JsonReader::decode($message->toJson());
        if (empty($jsonObject)) {
            throw new MessageValidatorException(MessageValidatorException::INVALID_JSON_STRING);
        }

        if (empty($eventSchema)) {
            throw new MessageValidatorException(MessageValidatorException::MISSING_EVENT_SCHEMA);
        }

        $schema = JsonReader::decode(JsonReader::read($eventSchema));
        $validator = new Validator();
        $validator->validate($jsonObject, $schema, Constraint::CHECK_MODE_APPLY_DEFAULTS);

        if (!$validator->isValid()) {
            throw new MessageValidatorException(
                MessageValidatorException::INVALIDATED_EVENT_MESSAGE . json_encode($validator->getErrors())
            );
        }

        return $validator->isValid();
    }
}
