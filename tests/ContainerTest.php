<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    Container
};
use ClanCats\Container\Tests\TestServices\{
    Car, Engine, Producer
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
    }
}
