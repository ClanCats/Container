<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    Container,
    ServiceFactoryArguments
};
use ClanCats\Container\Tests\TestServices\{
    Car, CarFactory, Engine
};

class ServiceFactoryArgumentsTest extends \PHPUnit_Framework_TestCase
{
    public function testConsturct()
    {
        $arguments = new ServiceFactoryArguments();
        $this->assertCount(0, $arguments->getAll());

        $arguments = new ServiceFactoryArguments(['foo', 'bar']);
        $this->assertCount(2, $arguments->getAll());

        $arguments = ServiceFactoryArguments::from(['foo', 'bar']);
        $this->assertCount(2, $arguments->getAll());
    }

    public function testRawArgument()
    {
        $arguments = new ServiceFactoryArguments();

        // test adding another raw argument
        $this->assertInstanceOf(ServiceFactoryArguments::class, $arguments->addRaw('foo'));
        $this->assertEquals('foo', $arguments->getAll()[0][0]);
        $this->assertEquals(ServiceFactoryArguments::RAW, $arguments->getAll()[0][1]);

        // test adding an object
        $arguments->addRaw($testDate = new \DateTime);
        $this->assertSame($testDate, $arguments->getAll()[1][0]);
        $this->assertEquals(ServiceFactoryArguments::RAW, $arguments->getAll()[1][1]);
    }

    public function testParameterArgument()
    {
        $arguments = new ServiceFactoryArguments();

        // test adding another parameter argument
        $this->assertInstanceOf(ServiceFactoryArguments::class, $arguments->addParameter('token'));
        $this->assertEquals('token', $arguments->getAll()[0][0]);
        $this->assertEquals(ServiceFactoryArguments::PARAMETER, $arguments->getAll()[0][1]);
    }

    public function testDependencyArgument()
    {
        $arguments = new ServiceFactoryArguments();

        // test adding another dependecy argument
        $this->assertInstanceOf(ServiceFactoryArguments::class, $arguments->addDependency('mail'));
        $this->assertEquals('mail', $arguments->getAll()[0][0]);
        $this->assertEquals(ServiceFactoryArguments::DEPENDENCY, $arguments->getAll()[0][1]);
    }

    public function testArgumentsFromArray()
    {
        $arguments = new ServiceFactoryArguments();

        $arguments->addArgumentsFromArray([
            '@foo',
            ':bar', 
            42,
        ]);

        // Check the values
        $this->assertEquals('foo', $arguments->getAll()[0][0]);
        $this->assertEquals(ServiceFactoryArguments::DEPENDENCY, $arguments->getAll()[0][1]);

        $this->assertEquals('bar', $arguments->getAll()[1][0]);
        $this->assertEquals(ServiceFactoryArguments::PARAMETER, $arguments->getAll()[1][1]);

        $this->assertEquals(42, $arguments->getAll()[2][0]);
        $this->assertEquals(ServiceFactoryArguments::RAW, $arguments->getAll()[2][1]);
    }

    public function testArgumentResolve()
    {
        $container = new Container();
        $container->set('foo', 'malcolm');
        $container->setParameter('bar', 'reynolds');

        $arguments = new ServiceFactoryArguments();
        $arguments->addArgumentsFromArray([
            '@foo',
            ':bar', 
            42,
        ]);

        $this->assertEquals(['malcolm', 'reynolds', 42], $arguments->resolve($container));
    }
}
