<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    ContainerNamespace,
    ServiceDefinition
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

    public function testAlias()
    {
        $namespace = new ContainerNamespace();

        $this->assertEquals([], $namespace->getAliases());
        $this->assertFalse($namespace->hasAlias('foo'));

        $namespace->setAlias('foo', 'bar');

        $this->assertTrue($namespace->hasAlias('foo'));
        $this->assertEquals(['foo' => 'bar'], $namespace->getAliases());
    }

    public function testServices()
    {
        $namespace = new ContainerNamespace();

        $this->assertEquals([], $namespace->getServices());
        $this->assertFalse($namespace->hasService('logger'));

        $definition = new ServiceDefinition("Logger");

        $namespace->setService('logger', $definition);

        $this->assertTrue($namespace->hasService('logger'));
        $this->assertEquals(['logger' => $definition], $namespace->getServices());
    }

    public function testHasPath()
    {
        $namespace = new ContainerNamespace([
            'test' => '/some/path/to/test',
            'test/foo/bar' => '/some/path/to/test/foo/bar',
        ]);

        $this->assertTrue($namespace->has('test'));
        $this->assertTrue($namespace->has('test/foo/bar'));

        $this->assertFalse($namespace->has('does/not/exist'));
    }

    public function testGetCode()
    {
        $namespace = new ContainerNamespace([
            'phpunit' => __DIR__ . '/phpunit.container',
        ]);

        $this->assertNotEmpty($namespace->getCode('phpunit'));
    }

    public function testGetCodeUndefinedPath() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerNamespaceException::class);
        $namespace = new ContainerNamespace();
        $namespace->getCode('nope');
    }

    public function testGetCodeInvalidPath() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerNamespaceException::class);
        $namespace = new ContainerNamespace([
            'phpunit' => __DIR__ . '/wrong.container',
        ]);
        $namespace->getCode('phpunit');
    }

    public function testParse()
    {
        $namespace = new ContainerNamespace();
        $namespace->parse(__DIR__ . '/phpunit.container');

        $this->assertNotEmpty($namespace->getServices());
    }
}
