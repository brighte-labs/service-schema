<?php

namespace ServiceSchema\Main;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ServiceSchema\Config\EventRegister;
use ServiceSchema\Config\ServiceRegister;
use ServiceSchema\Event\Message;
use ServiceSchema\Event\MessageFactory;
use ServiceSchema\Event\MessageInterface;
use ServiceSchema\Json\JsonReader;
use ServiceSchema\Main\Exception\ProcessorException;
use ServiceSchema\Service\Exception\ServiceException;
use ServiceSchema\Service\SagaInterface;
use ServiceSchema\Service\ServiceFactory;
use ServiceSchema\Service\ServiceInterface;
use ServiceSchema\Service\ServiceValidator;

class Processor implements ProcessorInterface
{

    /** @var \ServiceSchema\Config\EventRegister */
    protected $eventRegister;

    /** @var \ServiceSchema\Config\ServiceRegister */
    protected $serviceRegister;

    /** @var \ServiceSchema\Event\MessageFactory */
    protected $messageFactory;

    /** @var \ServiceSchema\Service\ServiceFactory */
    protected $serviceFactory;

    /** @var \ServiceSchema\Service\ServiceValidator */
    protected $serviceValidator;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * ServiceProvider constructor.
     *
     * @param array|null $eventConfigs
     * @param array|null $serviceConfigs
     * @param string|null $schemaDir
     * @param ContainerInterface|null $container
     * @param LoggerInterface|null $logger
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function __construct(
        array $eventConfigs = null,
        array $serviceConfigs = null,
        string $schemaDir = null,
        ContainerInterface $container = null,
        LoggerInterface $logger = null
    ) {
        $this->eventRegister = new EventRegister($eventConfigs);
        $this->serviceRegister = new ServiceRegister($serviceConfigs);
        $this->serviceFactory = new ServiceFactory($container);
        $this->messageFactory = new MessageFactory();
        $this->serviceValidator = new ServiceValidator(null, $schemaDir);
        $this->loadConfigs();
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    protected function loadConfigs()
    {
        $this->eventRegister->loadEvents();
        $this->serviceRegister->loadServices();
    }

    /**
     * @param string|\ServiceSchema\Event\Message $message
     * @param bool $return return first service result
     * @param array|null $filteredEvents
     * @return bool
     * @throws \ServiceSchema\Json\Exception\JsonException
     * @throws \ServiceSchema\Service\Exception\ServiceException
     * @throws \ServiceSchema\Main\Exception\ProcessorException
     */
    public function process($message = null, array $filteredEvents = null, bool $return = false)
    {
        $message = $this->createMessage($message);

        if (!empty($filteredEvents) && !in_array($message->getEvent(), $filteredEvents)) {
            throw new ProcessorException(ProcessorException::FILTERED_EVENT_ONLY . json_encode($filteredEvents));
        }

        $registeredEvents = $this->eventRegister->retrieveEvent($message->getEvent());
        $this->logger->debug(__METHOD__ . ': Registered events.', [
            'registeredEvents' => $registeredEvents
        ]);

        if (empty($registeredEvents)) {
            throw new ProcessorException(ProcessorException::NO_REGISTER_EVENTS . $message->getEvent());
        }

        foreach ($registeredEvents as $eventName => $services) {
            if (empty($services)) {
                continue;
            }

            foreach ($services as $serviceName) {
                $registerService = $this->serviceRegister->retrieveService($serviceName);
                $this->logger->debug(__METHOD__ . ': Registered service.', [
                    'registeredServices' => $registerService,
                    'serviceName' => $serviceName
                ]);

                if (empty($registerService)) {
                    continue;
                }

                $jsonSchema = $registerService[$serviceName][ServiceRegister::INDEX_SCHEMA];
                $callbacks = $registerService[$serviceName][ServiceRegister::INDEX_CALLBACKS];
                $service = $this->serviceFactory->createService($serviceName, $jsonSchema);
                if (empty($service)) {
                    continue;
                }

                if ($return === true) {
                    return $this->runService($message, $service, $callbacks, $return);
                }

                $this->runService($message, $service, $callbacks);
                $this->logger->debug(__METHOD__ . ': End running service', [
                    'messageId' => $message->getId(),
                    'service' => $service->getName(),

                ]);
            }
        }

        return true;
    }

    /**
     * @param null $message
     * @return bool
     * @throws \ServiceSchema\Json\Exception\JsonException
     * @throws \ServiceSchema\Main\Exception\ProcessorException
     * @throws \ServiceSchema\Service\Exception\ServiceException
     */
    public function rollback($message = null)
    {
        $message = $this->createMessage($message);

        $registeredEvents = $this->eventRegister->retrieveEvent($message->getEvent());
        $this->logger->debug(__METHOD__ . ': Registered events.', [
            'registeredEvents' => $registeredEvents
        ]);

        if (empty($registeredEvents)) {
            throw new ProcessorException(ProcessorException::NO_REGISTER_EVENTS . $message->getEvent());
        }

        foreach ($registeredEvents as $eventName => $services) {
            if (empty($services)) {
                continue;
            }

            foreach ($services as $serviceName) {
                $registerService = $this->serviceRegister->retrieveService($serviceName);
                $this->logger->debug(__METHOD__ . ': Registered service.', [
                    'registeredServices' => $registerService,
                    'serviceName' => $serviceName
                ]);

                if (empty($registerService)) {
                    continue;
                }

                $jsonSchema = $registerService[$serviceName][ServiceRegister::INDEX_SCHEMA];
                $service = $this->serviceFactory->createService($serviceName, $jsonSchema);

                if (empty($service)) {
                    continue;
                }

                if ($service instanceof SagaInterface) {
                    $this->rollbackService($message, $service);
                    $this->logger->debug(__METHOD__ . ': End rolling back service', [
                        'messageId' => $message->getId(),
                        'service' => $service->getName(),

                    ]);
                }
            }
        }

        return true;
    }

    /**
     * @param $json
     * @return false|\ServiceSchema\Event\Message
     * @throws \ServiceSchema\Json\Exception\JsonException
     * @throws \ServiceSchema\Main\Exception\ProcessorException
     */
    public function createMessage($json = null)
    {
        if ($json instanceof Message) {
            return $json;
        }

        $message = $this->messageFactory->createMessage($json);
        if (empty($message)) {
            throw new ProcessorException(ProcessorException::FAILED_TO_CREATE_MESSAGE . $json);
        }

        $this->logger->debug(__METHOD__ . ': Message Created', [
            'messageId' => $message->getId(),
            'event' => $message->getEvent()
        ]);

        return $message;
    }

    /**
     * @param \ServiceSchema\Event\MessageInterface|null $message
     * @param \ServiceSchema\Service\SagaInterface|null $service
     * @return bool|\ServiceSchema\Event\MessageInterface
     * @throws \ServiceSchema\Json\Exception\JsonException
     * @throws \ServiceSchema\Service\Exception\ServiceException
     */
    public function rollbackService(MessageInterface $message = null, SagaInterface $service = null)
    {
        $this->logger->debug(__METHOD__ . ': Start rolling back service', [
            'messageId' => $message->getId(),
        ]);

        $json = JsonReader::decode($message->toJson());
        $validator = $this->serviceValidator->validate($json, $service);
        if (!$validator->isValid()) {
            throw  new ServiceException(sprintf(ServiceException::INVALIDATED_JSON_STRING, $service->getJsonSchema(), json_encode($validator->getErrors())));
        }

        if (isset($json->payload)) {
            $message->setPayload($json->payload);
        }

        return $service->rollback($message);
    }

    /**
     * @param \ServiceSchema\Event\MessageInterface|null $message
     * @param \ServiceSchema\Service\ServiceInterface|null $service
     * @param array $callbacks
     * @param bool $return
     * @return bool
     * @throws \ServiceSchema\Json\Exception\JsonException
     * @throws \ServiceSchema\Service\Exception\ServiceException
     */
    public function runService(MessageInterface $message = null, ServiceInterface $service = null, array $callbacks = null, bool $return = false)
    {
        $this->logger->debug(__METHOD__ . ': Start running service', [
            'messageId' => $message->getId(),
            'service' => $service->getName(),

        ]);
        $json = JsonReader::decode($message->toJson());
        $validator = $this->serviceValidator->validate($json, $service);
        if (!$validator->isValid()) {
            throw  new ServiceException(sprintf(ServiceException::INVALIDATED_JSON_STRING, $service->getJsonSchema(), json_encode($validator->getErrors())));
        }

        if (isset($json->payload)) {
            $message->setPayload($json->payload);
        }

        $result = $service->consume($message);
        if ($return === true) {
            return $result;
        }

        if (($result instanceof MessageInterface) && !empty($callbacks)) {
            return $this->runCallbacks($result, $callbacks);
        }

        return $result;
    }

    /**
     * @param \ServiceSchema\Event\MessageInterface|null $event
     * @param array|null $callbacks
     * @return bool
     * @throws \ServiceSchema\Service\Exception\ServiceException
     */
    public function runCallbacks(MessageInterface $event, array $callbacks = null)
    {
        if (empty($callbacks)) {
            return true;
        }

        foreach ($callbacks as $callback) {
            $service = $this->serviceFactory->createService($callback);
            if (empty($service)) {
                continue;
            }

            $service->consume($event);
        }

        return true;
    }

    /**
     * @return \ServiceSchema\Config\EventRegister
     */
    public function getEventRegister()
    {
        return $this->eventRegister;
    }

    /**
     * @param \ServiceSchema\Config\EventRegister $eventRegister
     * @return \ServiceSchema\Main\Processor
     */
    public function setEventRegister(EventRegister $eventRegister = null)
    {
        $this->eventRegister = $eventRegister;

        return $this;
    }

    /**
     * @return \ServiceSchema\Config\ServiceRegister
     */
    public function getServiceRegister()
    {
        return $this->serviceRegister;
    }

    /**
     * @param \ServiceSchema\Config\ServiceRegister $serviceRegister
     * @return \ServiceSchema\MainProcessor
     */
    public function setServiceRegister(ServiceRegister $serviceRegister = null)
    {
        $this->serviceRegister = $serviceRegister;

        return $this;
    }

    /**
     * @return \ServiceSchema\Event\MessageFactory
     */
    public function getMessageFactory()
    {
        return $this->messageFactory;
    }

    /**
     * @param \ServiceSchema\Event\MessageFactory $messageFactory
     * @return \ServiceSchema\Main\Processor
     */
    public function setMessageFactory(MessageFactory $messageFactory = null)
    {
        $this->messageFactory = $messageFactory;

        return $this;
    }

    /**
     * @return \ServiceSchema\Service\ServiceFactory
     */
    public function getServiceFactory()
    {
        return $this->serviceFactory;
    }

    /**
     * @param \ServiceSchema\Service\ServiceFactory $serviceFactory
     * @return \ServiceSchema\Main\Processor
     */
    public function setServiceFactory(ServiceFactory $serviceFactory = null)
    {
        $this->serviceFactory = $serviceFactory;

        return $this;
    }

    /**
     * @return \ServiceSchema\Service\ServiceValidator
     */
    public function getServiceValidator()
    {
        return $this->serviceValidator;
    }

    /**
     * @param \ServiceSchema\Service\ServiceValidator $serviceValidator
     * @return \ServiceSchema\Main\Processor
     */
    public function setServiceValidator(ServiceValidator $serviceValidator = null)
    {
        $this->serviceValidator = $serviceValidator;

        return $this;
    }
}
