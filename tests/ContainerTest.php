<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    Container
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
}
