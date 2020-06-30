<?php

namespace ServiceSchema\Tests\Config;

use PHPUnit\Framework\TestCase;
use ServiceSchema\Config\ServiceRegister;

class ServiceRegisterTest extends TestCase
{
    protected $testDir;

    /** @var ServiceRegister */
    protected $serviceRegister;

    /**
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function setUp()
    {
        parent::setUp();
        $this->testDir = dirname(dirname(__FILE__));
        $this->serviceRegister = new ServiceRegister([$this->testDir . "/jsons/configs//services.json"]);
    }

    /**
     * @covers \ServiceSchema\Config\ServiceRegister::loadServices
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testLoadServices()
    {
        $this->serviceRegister->loadServices();
        $services = $this->serviceRegister->getServices();
        $this->assertTrue(is_array($services));
        $this->assertTrue(isset($services["ServiceSchema\Tests\Service\Samples\CreateContact"]));
        $this->assertTrue(isset($services["ServiceSchema\Tests\Service\Samples\UpdateContact"]));
    }

    /**
     * @covers \ServiceSchema\Config\ServiceRegister::registerService
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testRegisterService()
    {
        $this->serviceRegister->loadServices();
        $this->serviceRegister->registerService("Service.Name", "SomeServiceSchema");
        $services = $this->serviceRegister->getServices();
        $this->assertTrue(is_array($services));
        $this->assertTrue(isset($services["Service.Name"]));
        $this->assertEquals("SomeServiceSchema", $services["Service.Name"]['schema']);
    }

    /**
     * @covers \ServiceSchema\Config\ServiceRegister::retrieveService
     * @throws \ServiceSchema\Json\Exception\JsonException
     */
    public function testRetrieveEvent()
    {
        $this->serviceRegister->loadServices();
        $this->serviceRegister->registerService("Service.Name", "SomeServiceSchema");
        $service = $this->serviceRegister->retrieveService("Service.Name");
        $this->assertTrue(is_array($service));
        $this->assertTrue(isset($service["Service.Name"]));
        $this->assertEquals("SomeServiceSchema", $service["Service.Name"]['schema']);
    }

    public function testGetSet()
    {
        $this->serviceRegister->setConfigs(['a', 'b']);
        $config = $this->serviceRegister->getConfigs();
        $this->assertCount(2, $config);

        $this->serviceRegister->setServices(['a', 'b']);
        $services = $this->serviceRegister->getServices();
        $this->assertCount(2, $services);
    }
}
