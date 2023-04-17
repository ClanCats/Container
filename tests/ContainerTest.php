<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    Container
};
use ClanCats\Container\Tests\TestServices\{
    Car, Engine, Producer,

    CustomContainer,
    CustomServiceProviderArray
};

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testParameterBasics() : void
    {
        $container = new Container(['foo' => 'bar', 'pass' => 1234]);

        $this->assertTrue($container->hasParameter('foo'));
        $this->assertEquals('bar', $container->getParameter('foo'));

        $this->assertFalse($container->hasParameter('bar'));
        $this->assertEquals('someDefault', $container->getParameter('bar', 'someDefault'));

        $this->assertEquals(1234, $container->getParameter('pass'));
        $container->setParameter('pass', '12345');
        $this->assertEquals(12345, $container->getParameter('pass'));

        $this->assertEquals([
            'foo' => 'bar',
            'pass' => 12345
        ], $container->allParameters());
    }

    public function testServiceTypeFactory() : void
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
        $container->bindFactoryShared('test', function($c) {});
        $this->assertEquals(Container::RESOLVE_SHARED, $container->getServiceResolverType('test'));

        $container->bind('test2', function($c) {});
        $this->assertEquals(Container::RESOLVE_SHARED, $container->getServiceResolverType('test2'));
    }

    public function testInvalidServiceFactoryBinding() : void
    {
        $this->expectException(\ClanCats\Container\Exceptions\InvalidServiceException::class);
        $container = new Container();
        $container->bind('test', 42);
        $container->get('test');
    }

    public function testUnknownService() : void
    {
        $this->expectException(\ClanCats\Container\Exceptions\UnknownServiceException::class);
        (new Container())->get('unknown');
    }

    public function testServiceTypeUnknown() : void
    {
        $this->expectException(\ClanCats\Container\Exceptions\UnknownServiceException::class);
        (new Container())->getServiceResolverType('unknown');
    }

    public function testBind() : void
    {
        $container = new Container();
        $container->setParameter('ps', 205);

        $container->bind('engine', Engine::class, false)
            ->calls('setPower', [':ps']);

        $container->bind('producer', Producer::class)
            ->arguments(['Volvo']);

        $container->bind('s60', Car::class, false)
            ->arguments(['@engine', '@producer']);

        $car = $container->get('s60');

        $this->assertInstanceOf(Car::class, $car);
        $this->assertInstanceOf(Engine::class, $car->engine);
        $this->assertInstanceOf(Producer::class, $car->producer);

        $this->assertEquals('Volvo', $car->producer->name);
        $this->assertEquals(205, $car->engine->power);
    }

    public function testBindFactory() : void
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

    public function testbindFactoryShared() : void
    {
        $container = new Container();
        $container->bindFactory('engine.custom', function($c) 
        {
            return new Engine();   
        });

        $container->bindFactoryShared('engine.d8', function($c) 
        {
            $engine = new Engine(); $engine->power = 300; return $engine;
        });

        $container->bindFactoryShared('engine.t8', function($c) 
        {
            $engine = new Engine(); $engine->power = 325; return $engine; 
        });

        $this->assertInstanceOf(Engine::class, $container->get('engine.custom'));
        $this->assertInstanceOf(Engine::class, $container->get('engine.d8'));
        $this->assertInstanceOf(Engine::class, $container->get('engine.t8'));

        $this->assertSame($container->get('engine.d8'), $container->get('engine.d8'));
        $this->assertSame($container->get('engine.t8'), $container->get('engine.t8'));
        $this->assertNotSame($container->get('engine.custom'), $container->get('engine.custom'));

        $container->bindFactoryShared('volvo.s90', function($c) 
        {
            return new Car($c->get('engine.d8')); 
        });

        $this->assertSame($container->get('engine.d8'), $container->get('volvo.s90')->engine);
        $this->assertEquals(300, $container->get('volvo.s90')->engine->power);
    }

    public function testBrokenCustomContainerFactoryType() : void
    {
        $this->expectException(\ClanCats\Container\Exceptions\UnknownServiceException::class);
        (new CustomContainer())->get('broken');
    }

    public function testResolveFromMethod() : void
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

    public function testContainerResolveSelf() : void
    {
        $containerA = new Container();
        $containerB = new Container();

        $this->assertInstanceOf(Container::class, $containerA);
        $this->assertInstanceOf(Container::class, $containerB);

        $this->assertSame($containerA, $containerA->get('container'));
        $this->assertNotSame($containerA, $containerB->get('container'));
    }

    public function testContainerHas() : void
    {
        $container = new Container();

        // the container always has itself
        $this->assertTrue($container->has('container'));
        $this->assertFalse($container->has('foo'));

        $container->bind('foo', function($c) {});

        $this->assertTrue($container->has('foo'));
    }

    public function testContainerSet() : void
    {
        $container = new Container();

        $this->assertFalse($container->has('foo'));

        $container->set('foo', 'bar');

        $this->assertTrue($container->has('foo'));
        $this->assertEquals('bar', $container->get('foo'));
    }

    public function testContainerSetSelfError() : void
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerException::class);
        (new Container())->set('container', 'shouldNotWork');
    }

    public function testServiceIsResolved() : void
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

    public function testReleaseService() : void
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

    public function testAvailable() : void
    {
        $container = new Container();

        $this->assertCount(1, $container->available());

        $container->bind('foo', false);
        $container->bind('bar', false);

        $this->assertEquals(['foo', 'bar', 'container'], $container->available());
    }

    public function testCustomProviderArray() : void
    {
        $container = new Container();
        $container->register(new CustomServiceProviderArray());

        $this->assertCount(4, $container->available());

        foreach(['car', 'engine', 'producer'] as $service)
        {
            $this->assertEquals(Container::RESOLVE_PROVIDER, $container->getServiceResolverType($service));
        }

        $this->assertInstanceOf(Car::class, $container->get('car'));
        $this->assertInstanceOf(Engine::class, $container->get('car')->engine);
        $this->assertInstanceOf(Producer::class, $container->get('car')->producer);

        $this->assertEquals('Audi', $container->get('car')->producer->name);
        $this->assertSame($container->get('car'), $container->get('car'));
        $this->assertEquals(315, $container->get('car')->engine->power);

        $this->assertNotSame($container->get('car')->engine, $container->get('engine'));
    }

    public function testRemove() : void
    {
        $container = new Container();

        $this->assertFalse($container->remove('unknown'));
        $this->assertFalse($container->remove('container'));

        // test remove from service provider
        $container->register(new CustomServiceProviderArray());

        $this->assertTrue($container->has('car'));
        $this->assertInstanceOf(Car::class, $container->get('car'));

        $this->assertTrue($container->remove('car'));
        $this->assertFalse($container->has('car'));

        // test remove from factory
        $container->bind('car', function($c) 
        {
            return new Car($c->get('engine'));
        });

        $this->assertTrue($container->has('car'));
        $this->assertInstanceOf(Car::class, $container->get('car'));

        $this->assertTrue($container->remove('car'));
        $this->assertFalse($container->has('car'));

        $container = new CustomContainer();

        $this->assertTrue($container->has('car'));
        $this->assertInstanceOf(Car::class, $container->get('car'));

        $this->assertTrue($container->remove('car'));
        $this->assertFalse($container->has('car'));

    }

    public function testSetMetaData() : void
    {
        $container = new Container();

        $container->bind('car', function($c) 
        {
            return new Car($c->get('engine'));
        });

        $container->setMetaData('car', 'tags', [['Cars']]);

        $this->assertEquals([['Cars']], $container->getMetaData('car', 'tags'));

        // make sure data is beeing overridden

        $container->setMetaData('car', 'tags', [['Ships']]);

        $this->assertEquals([['Ships']], $container->getMetaData('car', 'tags'));
    }

    public function testSetMetaDataInvalidArray() : void
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerException::class);
        $container = new Container();

        $container->bind('car', function($c) 
        {
            return new Car($c->get('engine'));
        });

        $container->setMetaData('car', 'tags', ['this', 'is', 'not', 'an', 'array']);   
    }

    public function testSetMetaDataInvalidService() : void
    {
        $this->expectException(\ClanCats\Container\Exceptions\UnknownServiceException::class);
        $container = new Container();
        $container->setMetaData('car', 'tags', [['Cars']]);
    }

    public function testAddMetaData() : void
    {
        $container = new Container();

        $container->bind('car', function($c) 
        {
            return new Car($c->get('engine'));
        });

        $container->addMetaData('car', 'tags', ['Cars']);
        $container->addMetaData('car', 'tags', ['Objects']);

        $this->assertEquals([['Cars'], ['Objects']], $container->getMetaData('car', 'tags'));

        // make sure data is beeing added
        $container->addMetaData('car', 'tags', ['Ships']);

        $this->assertEquals([['Cars'], ['Objects'], ['Ships']], $container->getMetaData('car', 'tags'));
    }

    public function testAddMetaDataInvalidService() : void
    {
        $this->expectException(\ClanCats\Container\Exceptions\UnknownServiceException::class);
        $container = new Container();
        $container->addMetaData('car', 'tags', [['Cars']]);
    }

    public function testGetMetaDataKeys() : void
    {
        $container = new Container();

        $container->bind('car', function($c) 
        {
            return new Car($c->get('engine'));
        });

        $container->addMetaData('car', 'tags', ['Cars']);
        $container->addMetaData('car', 'routing', ['GET', '/car']);

        $this->assertEquals(['tags', 'routing'], $container->getMetaDataKeys('car'));
    }

    public function testServiceNamesWithMetaData() : void
    {
        $container = new Container();

        $container->bind('car', function($c) 
        {
            return new Car($c->get('engine'));
        });

        $container->bind('bmw', function($c) 
        {
            return new Car($c->get('engine'));
        });

        $container->addMetaData('car', 'tags', ['Cars']);
        $container->addMetaData('bmw', 'tags', ['Cars']);

        $this->assertEquals([
            'car' => [['Cars']],
            'bmw' => [['Cars']]
        ], $container->serviceNamesWithMetaData('tags'));
    }

    public function testServiceAliases() : void
    {
        $container = new Container();
        $container->register(new CustomServiceProviderArray());

        $container->bind('car.default', function($c) {
            return new Car($c->get('engine'));
        });
        $container->alias('car.main', 'car.default');

        $this->assertTrue($container->has('car.default'));
        $this->assertTrue($container->has('car.main'));

        // try to resolve it 
        $this->assertInstanceOf(Car::class, $container->get('car.default'));
        $this->assertInstanceOf(Car::class, $container->get('car.main'));

        // make sure they are the same
        $this->assertSame($container->get('car.default'), $container->get('car.main'));

        // test resolve status
        $this->assertTrue($container->isResolved('car.default'));
        $this->assertTrue($container->isResolved('car.main'));

        // test removing an alias
        $container->remove('car.main');
        $this->assertFalse($container->has('car.main'));
        $this->assertTrue($container->has('car.default'));

        // test resolve status
        $this->assertTrue($container->isResolved('car.default'));
        $this->assertFalse($container->isResolved('car.main'));
    }

    public function testServiceAliasesRecursion() : void
    {
        $container = new Container();
        $container->register(new CustomServiceProviderArray());

        $container->alias('volvo', 'car');
        $container->alias('volvo.s60', 'volvo');
        $container->alias('my_car', 'volvo.s60');

        $this->assertInstanceOf(Car::class, $container->get('my_car'));
    }

    public function testServiceAliasMetaData() : void
    {
        $container = new Container();
        $container->register(new CustomServiceProviderArray());
        $container->addMetaData('car', 'test', ['foo']);

        $container->alias('car_alias', 'car');
        $container->addMetaData('car_alias', 'test', ['foo_alias']); 

        $this->assertEquals([['foo']], $container->getMetaData('car', 'test'));
        $this->assertEquals([['foo_alias']], $container->getMetaData('car_alias', 'test'));
    }

    public function testGetClassForServiceShared() : void
    {
        $container = new Container();
        
        // test with already resolved service
        $container->set('pre_resolved', new Car(new Engine()));
        $this->assertEquals(Car::class, $container->getClassForService('pre_resolved'));
    }

    public function testGetClassForServiceProvider() : void
    {
        $container = new Container();
        $container->register(new CustomServiceProviderArray());

        $this->assertEquals(Car::class, $container->getClassForService('car'));
        $this->assertEquals(Engine::class, $container->getClassForService('engine'));
    }

    public function testGetClassForServiceFactory() : void
    {
        $container = new Container();
        $container->bindClass('car.shared', Car::class, [], true);
        $container->bindClass('car.factory', Car::class, [], false);

        $this->assertEquals(Car::class, $container->getClassForService('car.shared'));
        $this->assertEquals(Car::class, $container->getClassForService('car.factory'));
    }

    public function testGetClassForServiceClosure() : void
    {
        $container = new Container();
        $container->bind('car', function($c) {
            return new Car($c->get('engine'));
        });

        // closures are not supported and will return null
        $this->assertEquals(null, $container->getClassForService('car'));
    }

    public function testGetClassForServiceAlias() : void 
    {
        $container = new Container();
        $container->bindClass('car.main', Car::class, [], true);
        $container->alias('car.alias', 'car.main');

        $this->assertEquals(Car::class, $container->getClassForService('car.alias'));
    }

    public function testGetClassForServiceMethod() : void
    {
        $container = new CustomContainer();
        
        $this->assertEquals(Car::class, $container->getClassForService('car'));
        $this->assertEquals(Engine::class, $container->getClassForService('engine'));
    }
}
