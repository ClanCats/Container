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
        $arguments = new ServiceFactoryArguments([true, 42]);
        $this->assertEquals(true, $arguments->getAll()[0][0]);
        $this->assertEquals(ServiceFactoryArguments::RAW, $arguments->getAll()[0][1]);
        $this->assertEquals(42, $arguments->getAll()[1][0]);
        $this->assertEquals(ServiceFactoryArguments::RAW, $arguments->getAll()[1][1]);

        // test adding another raw argument
        $this->assertInstanceOf(ServiceFactoryArguments::class, $arguments->addRaw('foo'));
        $this->assertEquals('foo', $arguments->getAll()[2][0]);
        $this->assertEquals(ServiceFactoryArguments::RAW, $arguments->getAll()[2][1]);

        // test adding an object
        $arguments->addRaw($testDate = new \DateTime);
        $this->assertSame($testDate, $arguments->getAll()[3][0]);
        $this->assertEquals(ServiceFactoryArguments::RAW, $arguments->getAll()[3][1]);
    }
}
