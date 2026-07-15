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

    public function testImportDirectory()
    {
        $namespace = new ContainerNamespace();
        $namespace->importDirectory(__DIR__ . '/ctn/import');

        // the ".ctn" files are registered under their relative path (without extension),
        // nested files keep their sub directory as part of the import name
        $this->assertTrue($namespace->has('services'));
        $this->assertTrue($namespace->has('sub/database'));

        // non ".ctn" files are ignored by default
        $this->assertFalse($namespace->has('template'));
    }

    public function testImportDirectoryWithPrefix()
    {
        $namespace = new ContainerNamespace();
        $namespace->importDirectory(__DIR__ . '/ctn/import', 'app');

        $this->assertTrue($namespace->has('app/services'));
        $this->assertTrue($namespace->has('app/sub/database'));
    }

    public function testImportDirectoryWithCustomExtension()
    {
        $namespace = new ContainerNamespace();
        $namespace->importDirectory(__DIR__ . '/ctn/import', '', '.tpl');

        // only the custom extension is picked up now
        $this->assertTrue($namespace->has('template'));
        $this->assertFalse($namespace->has('services'));
    }

    public function testImportFromVendor()
    {
        $namespace = new ContainerNamespace();
        $namespace->importFromVendor(__DIR__ . '/ctn/vendor');

        $this->assertTrue($namespace->has('acme/foo'));
        $this->assertTrue($namespace->has('acme/bar'));
        $this->assertEquals('/some/path/to/foo.ctn', $namespace->getPath('acme/foo'));
    }

    public function testImportFromVendorKeepsExistingPaths()
    {
        // a path already defined on the namespace must win over the vendor map
        $namespace = new ContainerNamespace([
            'acme/foo' => '/local/override/foo.ctn',
        ]);
        $namespace->importFromVendor(__DIR__ . '/ctn/vendor');

        $this->assertEquals('/local/override/foo.ctn', $namespace->getPath('acme/foo'));
        $this->assertTrue($namespace->has('acme/bar'));
    }

    public function testImportFromVendorMissingMap()
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerNamespaceException::class);
        $namespace = new ContainerNamespace();
        $namespace->importFromVendor(__DIR__ . '/ctn/does-not-exist');
    }
}
