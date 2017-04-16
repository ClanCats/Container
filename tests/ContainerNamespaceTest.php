<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    ContainerNamespace
};

class ContainerNamespaceTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $namespace = new ContainerNamespace();
        $this->assertInstanceOf(ContainerNamespace::class, $namespace);
    }

    public function testParameters()
    {
        $namespace = new ContainerNamespace();

        $this->assertEquals([], $namespace->getParameters());
        $this->assertFalse($namespace->hasParameter('foo'));

        $namespace->setParameter('foo', 'bar');

        $this->assertTrue($namespace->hasParameter('foo'));
        $this->assertEquals(['foo' => 'bar'], $namespace->getParameters());
    }
}
