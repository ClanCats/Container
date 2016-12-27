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

        $this->assertInstanceOf(Engine::class, $container->get('engine'));
        $this->assertInstanceOf(Engine::class, $container->get('car')->engine);
        $this->assertInstanceOf(Car::class, $container->get('car'));

        $this->assertSame($container->get('engine'), $container->get('engine'));
        $this->assertNotSame($container->get('car'), $container->get('car'));

        $this->assertEquals(Container::RESOLVE_METHOD, $container->getServiceResolverType('engine'));
        $this->assertEquals(Container::RESOLVE_METHOD, $container->getServiceResolverType('car'));
    }

    public function testContainerResolveSelf()
    {
        $containerA = new Container();
        $containerB = new Container();

        $this->assertInstanceOf(Container::class, $containerA);
        $this->assertInstanceOf(Container::class, $containerB);

        $this->assertSame($containerA, $containerA->get('container'));
        $this->assertNotSame($containerA, $containerB->get('container'));
    }

    public function testContainerHas()
    {
        $container = new Container();

        // the container always has itself
        $this->assertTrue($container->has('container'));
        $this->assertFalse($container->has('foo'));

        $container->bind('foo', function($c) {});

        $this->assertTrue($container->has('foo'));
    }

    public function testContainerSet()
    {
        $container = new Container();

        $this->assertFalse($container->has('foo'));

        $container->set('foo', 'bar');

        $this->assertTrue($container->has('foo'));
        $this->assertEquals('bar', $container->get('foo'));
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\ContainerException
     */
    public function testContainerSetSelfError()
    {
        (new Container())->set('container', 'shouldNotWork');
    }

    public function testServiceIsResolved()
    {
        $container = new Container();

        // check if self is resolved
        $this->assertTrue($container->isResolved('container'));

        // check unknown
        $this->assertFalse($container->isResolved('car'));

        // check factory binding
        $container->bind('car', function($c) 
        {
            return new Car(new Engine());
        });

        $this->assertInstanceOf(Car::class, $container->get('car'));
        $this->assertSame($container->get('car'), $container->get('car'));
        $this->assertTrue($container->isResolved('car'));

        // check that factory method can never be resolved
        // even if it already has been resolved
        $container->bind('car', function($c) 
        {
            return new Car(new Engine());
        }, false);

        $this->assertNotSame($container->get('car'), $container->get('car'));
        $this->assertFalse($container->isResolved('car'));
    }

    public function testReleaseService()
    {
        $container = new Container();

        $this->assertFalse($container->release('car'));

        $container->bind('car', function($c) 
        {
            return new Car(new Engine());
        });

        $referenceCar = $container->get('car');
        $this->assertSame($referenceCar, $container->get('car'));

        $this->assertTrue($container->release('car'));
        $this->assertNotSame($referenceCar, $container->get('car'));
    }
}
