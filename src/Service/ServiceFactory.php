<?php

namespace ServiceSchema\Service;

use Prophecy\Exception\Doubler\ClassNotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ServiceSchema\Service\Exception\ServiceException;

class ServiceFactory
{

    /** @var ContainerInterface $container */
    protected $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    /**
     * @param string|null $serviceClass
     * @param string|null $schema
     * @return \ServiceSchema\Service\ServiceInterface|false
     * @throws \ServiceSchema\Service\Exception\ServiceException
     */
    public function createService(string $serviceClass = null, string $schema = null)
    {
        try {
            $service = $this->container 
            ? $this->getService($serviceClass)
            : (class_exists($serviceClass) ? new $serviceClass() : null);
            if ($service === null)
                throw new ClassNotFoundException("not found", $service);
        } catch (\Exception $exception) {
            throw new ServiceException(ServiceException::INVALID_SERVICE_CLASS . $serviceClass);
        }

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
            return new $serviceClass($this->container);
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
