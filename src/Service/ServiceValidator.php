<?php

namespace ServiceSchema\Service;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use ServiceSchema\Json\JsonReader;
use ServiceSchema\Service\Exception\ServiceException;

class ServiceValidator
{
    /** @var \JsonSchema\Validator */
    protected $validator;

    /** @var string */
    protected $schemaDir;

    /**
     * EventValidator constructor.
     *
     * @param \JsonSchema\Validator|null $validator
     */
    public function __construct(Validator $validator = null, string $schemaDir = null)
    {
        $this->validator = $validator ?? new Validator();
        $this->schemaDir = $schemaDir;
    }

    /**
     * @param \stdClass $jsonObject
     * @param \ServiceSchema\Service\ServiceInterface $service
     * @return \JsonSchema\Validator
     * @throws \ServiceSchema\Service\Exception\ServiceException
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function validate(\stdClass &$jsonObject = null, ServiceInterface $service = null)
    {
        if (empty($jsonObject)) {
            throw new ServiceException(ServiceException::MISSING_JSON_STRING);
        }

        if (empty($service) || empty($service->getJsonSchema())) {
            throw new ServiceException(ServiceException::MISSING_SERVICE_SCHEMA);
        }

        $schema = JsonReader::decode(JsonReader::read($this->schemaDir . $service->getJsonSchema()));

        $this->validator->validate($jsonObject, $schema, Constraint::CHECK_MODE_APPLY_DEFAULTS);

        return $this->validator;
    }

    /**
     * @return \JsonSchema\Validator
     */
    public function getValidator(): Validator
    {
        return $this->validator;
    }

    /**
     * @param \JsonSchema\Validator $validator
     * @return \ServiceSchema\Service\ServiceValidator
     */
    public function setValidator(Validator $validator = null): ServiceValidator
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * @return string
     */
    public function getSchemaDir(): string
    {
        return $this->schemaDir;
    }

    /**
     * @param string $schemaDir
     * @return \ServiceSchema\Service\ServiceValidator
     */
    public function setSchemaDir(?string $schemaDir = null): ServiceValidator
    {
        $this->schemaDir = $schemaDir;

        return $this;
    }
}
