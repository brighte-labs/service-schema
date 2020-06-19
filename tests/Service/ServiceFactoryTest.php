<?php

namespace ServiceSchema\Tests\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ServiceSchema\Service\Exception\ServiceException;
use ServiceSchema\Service\ServiceFactory;
use ServiceSchema\Service\ServiceInterface;
use ServiceSchema\Tests\Service\Samples\CreateContact;

class ServiceFactoryTest extends TestCase
{
    protected $testDir;
    /** @var ServiceFactory */
    protected $serviceFactory;
    /** @var MockObject */
    protected $logger;

    public function setUp()
    {
        parent::setUp();
        $this->testDir = dirname(dirname(__FILE__));
        $this->serviceFactory = new ServiceFactory();

    }

    public function testContainerSettersAndGetters()
    {
        $container = $this->createMock(ContainerInterface::class);
        $this->serviceFactory->setContainer($container);
        $this->assertEquals($container, $this->serviceFactory->getContainer());
    }

    /**
     * @covers \ServiceSchema\Service\ServiceFactory::createService
     * @throws \ServiceSchema\Service\Exception\ServiceException
     */
    public function testCreateService()
    {
        $serviceClass = "\ServiceSchema\Tests\Service\Samples\CreateContact";
        $schema = $this->testDir . "/jsons/schemas/CreateContact.json";
        $service = $this->serviceFactory->createService($serviceClass, $schema);
        $this->assertTrue($service instanceof ServiceInterface);
        $this->assertEquals($this->testDir . "/jsons/schemas/CreateContact.json", $service->getJsonSchema());
    }

    public function testCreateServiceNotExists()
    {
        $schema = $this->testDir . "/jsons/schemas/CreateContact.json";
        $service = $this->serviceFactory->createService("frog", $schema);
        $this->assertFalse($service);
    }

    public function testCreateNonStandardService()
    {
        $schema = $this->testDir . "/jsons/schemas/CreateContact.json";
        $service = $this->serviceFactory->createService("TestClass", $schema);
        $this->assertFalse($service instanceof ServiceInterface);
        $this->assertFalse($service);
    }

    public function testCreateServiceWithContainer()
    {
        $serviceClass = CreateContact::class;
        $schema = $this->testDir . "/jsons/schemas/CreateContact.json";
        $container = $this->createMock(ContainerInterface::class);
        $this->serviceFactory = new ServiceFactory($container);
        $exception = new class extends \Exception implements NotFoundExceptionInterface{};
        $container->expects(static::once())->method('get')->with($serviceClass)->willThrowException($exception);
        $newService = $this->serviceFactory->createService($serviceClass, $schema);
        $this->assertTrue($newService instanceof ServiceInterface);
        $this->assertEquals($this->testDir . "/jsons/schemas/CreateContact.json", $newService->getJsonSchema());
    }

    public function testCreateServiceFromContainer()
    {
        $serviceClass = CreateContact::class;
        $schema = $this->testDir . "/jsons/schemas/CreateContact.json";
        $container = $this->createMock(ContainerInterface::class);
        $service = new CreateContact(new NullLogger());
        $container->expects(static::once())->method('get')->with($serviceClass)->willReturn($service);
        $this->serviceFactory = new ServiceFactory($container);
        $newService = $this->serviceFactory->createService($serviceClass, $schema);
        $this->assertSame($service, $newService);
        $this->assertTrue($newService instanceof ServiceInterface);
        $this->assertEquals($this->testDir . "/jsons/schemas/CreateContact.json", $newService->getJsonSchema());
        $this->assertEquals($serviceClass, $newService->getName());
    }
}
