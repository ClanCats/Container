<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    Container,
    ServiceProviderArray
};
use ClanCats\Container\Tests\TestServices\{
    Car, Engine, Producer
};

class ServiceProviderArrayTest extends \PHPUnit_Framework_TestCase
{
    private function resolveWithArray(string $serviceName, array $data)
    {
        $container = new Container();
        $provider = new ServiceProviderArray();

        $provider->setServices($data);

        return $provider->resolve($serviceName, $container);
    }

    public function testSetServices()
    {
        list($engine, $isShared) = $this->resolveWithArray('engine', [
            'engine' => ['class' => Engine::class]
        ]);

        $this->assertInstanceOf(Engine::class, $engine);
        $this->assertTrue($isShared);
    }

    public function testSharedAttribute()
    {
        list($engine, $isShared) = $this->resolveWithArray('engine', [
            'engine' => ['class' => Engine::class, 'shared' => false]
        ]);

        $this->assertInstanceOf(Engine::class, $engine);
        $this->assertFalse($isShared);
    }

    public function testProvides()
    {
        $provider = new ServiceProviderArray();

        $this->assertEquals([], $provider->provides());

        $provider->setServices(['foo' => [], 'bar' => []]);

        $this->assertEquals(['foo', 'bar'], $provider->provides());
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\InvalidServiceException
     */
    public function testResolveWithoutClass()
    {
        $this->resolveWithArray('foo', [
            'foo' => [],
        ]);
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\UnknownServiceException
     */
    public function testResolveUnknown()
    {
        $this->resolveWithArray('bar', [
            'foo' => [],
        ]);
    }

    public function testResolveCalls()
    {
        list($engine, $isShared) = $this->resolveWithArray('engine', [
            'engine' => ['class' => Engine::class, 'shared' => false, 'calls' => [
                ['method' => 'setPower', 'arguments' => [415]],
            ]]
        ]);

        $this->assertEquals(415, $engine->power);
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\InvalidServiceException
     */
    public function testResolveCallsMissingArguments()
    {
        $this->resolveWithArray('engine', [
            'engine' => ['class' => Engine::class, 'shared' => false, 'calls' => [
                ['method' => 'setPower'],
            ]]
        ]);
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\InvalidServiceException
     */
    public function testResolveCallsMissingMethod()
    {
        $this->resolveWithArray('engine', [
            'engine' => ['class' => Engine::class, 'shared' => false, 'calls' => [
                ['arguments' => [123]],
            ]]
        ]);
    }
}
