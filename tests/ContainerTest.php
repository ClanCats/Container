<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    Container
};
use ClanCats\Container\Tests\TestServices\{
    Car, Engine, Producer,

    CustomContainer
};

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testParameterBasics()
    {
        $container = new Container(['foo' => 'bar', 'pass' => 1234]);

        $this->assertTrue($container->hasParameter('foo'));
        $this->assertEquals('bar', $container->getParameter('foo'));

        $this->assertFalse($container->hasParameter('bar'));
        $this->assertEquals('someDefault', $container->getParameter('bar', 'someDefault'));

        $this->assertEquals(1234, $container->getParameter('pass'));
        $container->setParameter('pass', '12345');
        $this->assertEquals(12345, $container->getParameter('pass'));
    }

    public function testServiceTypeFactory()
    {
        $container = new Container();
        $container->bindFactory('test', function($c) {});
        $this->assertEquals(Container::RESOLVE_FACTORY, $container->getServiceResolverType('test'));

        $container->bind('test2', function($c) {}, false);
        $this->assertEquals(Container::RESOLVE_FACTORY, $container->getServiceResolverType('test2'));
    }

    public function testServiceTypeFactoryShared()
    {
        $container = new Container();
        $container->bindSharedFactory('test', function($c) {});
        $this->assertEquals(Container::RESOLVE_SHARED, $container->getServiceResolverType('test'));

        $container->bind('test2', function($c) {});
        $this->assertEquals(Container::RESOLVE_SHARED, $container->getServiceResolverType('test2'));
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\InvalidServiceException
     */
    public function testInvalidServiceFactoryBinding()
    {
        $container = new Container();
        $container->bind('test', 42);
        $container->get('test');
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\UnknownServiceException
     */
    public function testUnknownService()
    {
        (new Container())->get('unknown');
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\UnknownServiceException
     */
    public function testServiceTypeUnknown()
    {
        (new Container())->getServiceResolverType('unknown');
    }

    public function testBindFactory()
    {
        $container = new Container();
        $container->bindFactory('engine', function($c) 
        {
            return new Engine();   
        });

        $this->assertInstanceOf(Engine::class, $container->get('engine'));

        // check if they are not the same
        $engine = $container->get('engine'); 
        $engine->power = 120;
        $this->assertNotEquals($engine, $container->get('engine'));
        $this->assertNotEquals(120, $container->get('engine'));

        // add the car
        $container->bindFactory('car', function($c) 
        {
            return new Car($c->get('engine'));
        });

        $this->assertInstanceOf(Car::class, $container->get('car'));
        $this->assertInstanceOf(Engine::class, $container->get('car')->engine);

        // check if the engine is not the same
        $car1 = $container->get('car');
        $car2 = $container->get('car');

        $this->assertNotSame($car1, $car2);
        $this->assertNotSame($car1->engine, $car2->engine);
    } 

    public function testBindSharedFactory()
    {
        $container = new Container();
        $container->bindFactory('engine.custom', function($c) 
        {
            return new Engine();   
        });

        $container->bindSharedFactory('engine.d8', function($c) 
        {
            $engine = new Engine(); $engine->power = 300; return $engine;
        });

        $container->bindSharedFactory('engine.t8', function($c) 
        {
            $engine = new Engine(); $engine->power = 325; return $engine; 
        });

        $this->assertInstanceOf(Engine::class, $container->get('engine.custom'));
        $this->assertInstanceOf(Engine::class, $container->get('engine.d8'));
        $this->assertInstanceOf(Engine::class, $container->get('engine.t8'));

        $this->assertSame($container->get('engine.d8'), $container->get('engine.d8'));
        $this->assertSame($container->get('engine.t8'), $container->get('engine.t8'));
        $this->assertNotSame($container->get('engine.custom'), $container->get('engine.custom'));

        $container->bindSharedFactory('volvo.s90', function($c) 
        {
            return new Car($c->get('engine.d8')); 
        });

        $this->assertSame($container->get('engine.d8'), $container->get('volvo.s90')->engine);
        $this->assertEquals(300, $container->get('volvo.s90')->engine->power);
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\UnknownServiceException
     */
    public function testBrokenCustomContainerFactoryType()
    {
        (new CustomContainer())->get('broken');
    }

    public function testResolveFromMethod()
    {
        $container = new CustomContainer();

        var_dump($container->get('car'));
    }
}
