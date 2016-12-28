<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    Container,
    ServiceFactory
};
use ClanCats\Container\Tests\TestServices\{
    Car, CarFactory, Engine, Producer
};

class ServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testConsturct()
    {
        $factory = new ServiceFactory('\\Acme\\Demo', ['@foo', ':bar', true, 12, 'helo']);

        $this->assertEquals('\\Acme\\Demo', $factory->getClassName());
        $this->assertCount(5, $factory->getArguments()->getAll());

        $factory = ServiceFactory::for('\\Acme\\Demo', ['@foo', ':bar', true, 12, 'helo']);

        $this->assertEquals('\\Acme\\Demo', $factory->getClassName());
        $this->assertCount(5, $factory->getArguments()->getAll());
    }

    public function testArguments()
    {
        $factory = new ServiceFactory('\\Acme\\Demo', ['foo']);

        $factory->addRawArgument('bar');
        $factory->addDependencyArgument('lorem');
        $factory->addParameterArgument('ipsum');

        $this->assertCount(4, $factory->getArguments()->getAll());
        $this->assertEquals('foo', $factory->getArguments()->getAll()[0][0]);
        $this->assertEquals('bar', $factory->getArguments()->getAll()[1][0]);
        $this->assertEquals('lorem', $factory->getArguments()->getAll()[2][0]);
        $this->assertEquals('ipsum', $factory->getArguments()->getAll()[3][0]);
    }

    public function testCreate()
    {
        $container = new Container();

        $container->bind('engine', function() 
        {
            return new Engine();
        });

        $factory = new ServiceFactory(Car::class, ['@engine']);
        $car = $factory->create($container);
        
        $this->assertInstanceOf(Car::class, $car);
        $this->assertInstanceOf(Engine::class, $car->engine);
    }

    public function testBindServiceFactory()
    {
        $container = new Container();
        $container->setParameter('producer.name', 'Lexus');

        $container->bind('engine', ServiceFactory::for(Engine::class));
        $container->bind('producer', ServiceFactory::for(Producer::class, [':producer.name']));
        $container->bind('car', ServiceFactory::for(Car::class, ['@engine', '@producer']));
        
        $car = $container->get('car');
        
        $this->assertInstanceOf(Car::class, $car);
        $this->assertInstanceOf(Engine::class, $car->engine);
        $this->assertInstanceOf(Producer::class, $car->producer);
        $this->assertEquals('Lexus', $car->producer->name);
    }

    public function testBindCustomServiceFactoryClass()
    {
        $container = new Container();

        $container->bindSharedFactory('d8', function($c) 
        {
            $engine = new Engine(); $engine->power = 300; return $engine;
        });

        $container->bindSharedFactory('t8', function($c) 
        {
            $engine = new Engine(); $engine->power = 325; return $engine; 
        });

        $container->bind('diesel', new CarFactory('d8'), false);
        $container->bind('benzin', new CarFactory('t8'), false);
        $container->bind('demo', new CarFactory('t8'), true);

        $this->assertEquals(300, $container->get('diesel')->engine->power);
    } 
}
