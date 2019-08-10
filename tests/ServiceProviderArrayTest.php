<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    Container,
    ServiceProviderArray
};
use ClanCats\Container\Tests\TestServices\{
    Car, Engine, Producer
};

class ServiceProviderArrayTest extends \PHPUnit\Framework\TestCase
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

    public function testResolveWithoutClass() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\InvalidServiceException::class);
        $this->resolveWithArray('foo', [
            'foo' => [],
        ]);
    }

    public function testResolveUnknown() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\UnknownServiceException::class);
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

    public function testResolveCallsMissingArguments() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\InvalidServiceException::class);
        $this->resolveWithArray('engine', [
            'engine' => ['class' => Engine::class, 'shared' => false, 'calls' => [
                ['method' => 'setPower'],
            ]]
        ]);
    }

    public function testResolveCallsMissingMethod() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\InvalidServiceException::class);
        $this->resolveWithArray('engine', [
            'engine' => ['class' => Engine::class, 'shared' => false, 'calls' => [
                ['arguments' => [123]],
            ]]
        ]);
    }
}
