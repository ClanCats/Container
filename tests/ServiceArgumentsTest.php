<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    Container,
    ServiceArguments
};
use ClanCats\Container\Tests\TestServices\{
    Car, CarFactory, Engine
};

class ServiceArgumentsTest extends \PHPUnit\Framework\TestCase
{
    public function testConsturct()
    {
        $arguments = new ServiceArguments();
        $this->assertCount(0, $arguments->getAll());

        $arguments = new ServiceArguments(['foo', 'bar']);
        $this->assertCount(2, $arguments->getAll());

        $arguments = ServiceArguments::from(['foo', 'bar']);
        $this->assertCount(2, $arguments->getAll());
    }

    public function testRawArgument()
    {
        $arguments = new ServiceArguments();

        // test adding another raw argument
        $this->assertInstanceOf(ServiceArguments::class, $arguments->addRaw('foo'));
        $this->assertEquals('foo', $arguments->getAll()[0][0]);
        $this->assertEquals(ServiceArguments::RAW, $arguments->getAll()[0][1]);

        // test adding an object
        $arguments->addRaw($testDate = new \DateTime);
        $this->assertSame($testDate, $arguments->getAll()[1][0]);
        $this->assertEquals(ServiceArguments::RAW, $arguments->getAll()[1][1]);
    }

    public function testParameterArgument()
    {
        $arguments = new ServiceArguments();

        // test adding another parameter argument
        $this->assertInstanceOf(ServiceArguments::class, $arguments->addParameter('token'));
        $this->assertEquals('token', $arguments->getAll()[0][0]);
        $this->assertEquals(ServiceArguments::PARAMETER, $arguments->getAll()[0][1]);
    }

    public function testDependencyArgument()
    {
        $arguments = new ServiceArguments();

        // test adding another dependecy argument
        $this->assertInstanceOf(ServiceArguments::class, $arguments->addDependency('mail'));
        $this->assertEquals('mail', $arguments->getAll()[0][0]);
        $this->assertEquals(ServiceArguments::DEPENDENCY, $arguments->getAll()[0][1]);
    }

    public function testArgumentsFromArray()
    {
        $arguments = new ServiceArguments();

        $arguments->addArgumentsFromArray([
            '@foo',
            ':bar', 
            42,
        ]);

        // Check the values
        $this->assertEquals('foo', $arguments->getAll()[0][0]);
        $this->assertEquals(ServiceArguments::DEPENDENCY, $arguments->getAll()[0][1]);

        $this->assertEquals('bar', $arguments->getAll()[1][0]);
        $this->assertEquals(ServiceArguments::PARAMETER, $arguments->getAll()[1][1]);

        $this->assertEquals(42, $arguments->getAll()[2][0]);
        $this->assertEquals(ServiceArguments::RAW, $arguments->getAll()[2][1]);
    }

    public function testArgumentResolve()
    {
        $container = new Container();
        $container->set('foo', 'malcolm');
        $container->setParameter('bar', 'reynolds');

        $arguments = new ServiceArguments();
        $arguments->addArgumentsFromArray([
            '@foo',
            ':bar', 
            42,
        ]);

        $this->assertEquals(['malcolm', 'reynolds', 42], $arguments->resolve($container));
    }
}
