<?php

namespace ServiceSchema\Service;

use Prophecy\Exception\Doubler\ClassNotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ServiceSchema\Service\Exception\ServiceException;

class ServiceFactory
{
    /** @var ContainerInterface $container */
    protected $container;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(ContainerInterface $container = null, LoggerInterface $logger = null)
    {
        $this->container = $container;
        $this->logger = $logger ?? new NullLogger();
    }
    /**
     * @param string|null $serviceClass
     * @param string|null $schema
     * @return \ServiceSchema\Service\ServiceInterface|false
     * @throws \ServiceSchema\Service\Exception\ServiceException
     */
    public function createService(string $serviceClass = null, string $schema = null)
    {
        $this->logger->debug(__METHOD__ . ': Start creating service class', [
            'serviceClass' => $serviceClass,
            'schema' => $schema
        ]);

        try {
            $service = $this->container
            ? $this->getService($serviceClass)
            : (class_exists($serviceClass) ? new $serviceClass() : null);
            if ($service === null) {
                throw new ClassNotFoundException("not found", $serviceClass);
            }
        } catch (\Exception $exception) {
            $this->logger->error(__METHOD__ . ': ' . ServiceException::INVALID_SERVICE_CLASS, [
                'serviceName' => $serviceClass,
                'exception' => $exception->getMessage()
            ]);

            return false;
        }

        $this->logger->debug(__METHOD__ . 'Service Created', ['serviceName' => $serviceClass]);
        if ($service instanceof ServiceInterface) {
            $service->setName($serviceClass);
            $service->setJsonSchema($schema);

            return $service;
        }

        return false;
    }

    public function getService(string $serviceClass): ServiceInterface
    {
        try {
            return $this->container->get($serviceClass);
        } catch (NotFoundExceptionInterface $e) {
            return new $serviceClass();
        }
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    public function setContainer(?ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }
}
