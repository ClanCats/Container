<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    Container,
    ContainerBuilder,
    ContainerNamespace,
    ContainerFactory
};
use ClanCats\Container\Tests\TestServices\{
    Car, Engine, Producer
};

/**
 * End to end tests exercising the full pipeline:
 *
 *     .ctn file -> ContainerNamespace -> ContainerBuilder -> generated class -> resolved services
 */
class IntegrationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * The temporary cache directory used by the factory based test
     *
     * @var string
     */
    private $cacheDir;

    protected function setUp() : void
    {
        $this->cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ccc_integration_' . uniqid();
        mkdir($this->cacheDir);
    }

    protected function tearDown() : void
    {
        if (is_dir($this->cacheDir)) {
            foreach (glob($this->cacheDir . DIRECTORY_SEPARATOR . '*') as $file) {
                @unlink($file);
            }
            @rmdir($this->cacheDir);
        }
    }

    /**
     * Assert that a freshly built container resolves the services defined in
     * the "phpunit.container" fixture and wires their dependencies correctly.
     */
    private function assertContainerResolvesFixture(Container $container) : void
    {
        $car = $container->get('car');

        $this->assertInstanceOf(Car::class, $car);
        $this->assertInstanceOf(Engine::class, $car->engine);
        $this->assertInstanceOf(Producer::class, $car->producer);

        // the raw string argument from the ".ctn" file is wired through
        $this->assertEquals('phpunit-producer', $car->producer->name);

        // the services are shared, so the injected dependencies are the very
        // same instances the container resolves directly
        $this->assertSame($container->get('engine'), $car->engine);
        $this->assertSame($container->get('producer'), $car->producer);

        // resolving a shared service twice returns the same instance
        $this->assertSame($car, $container->get('car'));
    }

    public function testNamespaceParsesIntoLoadableContainer()
    {
        // parse the container file into a namespace
        $namespace = new ContainerNamespace();
        $namespace->parse(__DIR__ . '/phpunit.container');

        // import the namespace into a builder and generate the container class
        $className = 'IntegrationBuiltContainer';
        $builder = new ContainerBuilder($className);
        $builder->importNamespace($namespace);

        $code = $builder->generate();

        // load the generated class (strip the leading php open tag for eval)
        eval('?>' . $code);

        $this->assertTrue(class_exists($className, false));

        $container = new $className();
        $this->assertInstanceOf(Container::class, $container);

        $this->assertContainerResolvesFixture($container);
    }

    public function testEndToEndViaFactory()
    {
        $factory = new ContainerFactory($this->cacheDir);

        // this is the real, user facing entry point: the factory runs the builder
        // callback, caches the generated class and returns a ready to use container
        $container = $factory->create('IntegrationFactoryContainer', function(ContainerBuilder $builder) {
            $namespace = new ContainerNamespace();
            $namespace->parse(__DIR__ . '/phpunit.container');
            $builder->importNamespace($namespace);
        });

        $this->assertContainerResolvesFixture($container);
    }
}
