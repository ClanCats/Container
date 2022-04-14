<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    ContainerBuilder,
    ServiceDefinition,
    ServiceArguments,
    ContainerNamespace,
    Container
};
use ClanCats\Container\Tests\TestServices\{
    Car, Engine, Producer
};

class ContainerBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Compatibilty proxy
     * @todo: remove this as soon as we deprecate PHPUnit 6 
     */
    protected function assertStringContainsStringProxy(string $needle, string $haystack)
    {
        if (PHP_VERSION_ID >= 70300 && method_exists(get_parent_class($this), 'assertStringContainsString')) {
            return $this->assertStringContainsString($needle, $haystack);
        }

        return $this->assertContains($needle, $haystack);
    }

    /**
     * Compatibilty proxy
     * @todo: remove this as soon as we deprecate PHPUnit 6 
     */
    protected function assertStringNotContainsStringProxy(string $needle, string $haystack)
    {
        if (PHP_VERSION_ID >= 70300 && method_exists(get_parent_class($this), 'assertStringNotContainsString')) {
            return $this->assertStringNotContainsString($needle, $haystack);
        }

        return $this->assertNotContains($needle, $haystack);
    }

    public function testContainerName()
    {
        $builder = new ContainerBuilder('TestContainer');

        $this->assertEquals('TestContainer', $builder->getContainerName());
    }

    public function testContainerNameInvalidType() 
    {
        $this->expectException(\TypeError::class);
        new ContainerBuilder(null);
    }

    public function invalidContainerNameProvider()
    {
        return [
            [''],
            [0],
            [1],
            [42],
            [true],
            [false],
            ['foo bar'],
            ['foo.bar'],
            ['foo/bar'],
            ['1foo']
        ];
    }

    /**
     * @dataProvider invalidContainerNameProvider
     */
    public function testContainerNameEmpty($name)
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerBuilderException::class);
        new ContainerBuilder($name);
    }

    public function testSetContainerName()
    {
        $builder = new ContainerBuilder('Foo');

        $this->assertEquals('Foo', $builder->getContainerName());
        $this->assertEquals('Foo', $builder->getContainerClassName());

        $builder->setContainerName('Foo_Bar');
        $this->assertEquals('Foo_Bar', $builder->getContainerName());
        $this->assertEquals('Foo_Bar', $builder->getContainerClassName());

        $builder->setContainerName('someContainer1');
        $this->assertEquals('someContainer1', $builder->getContainerName());
        $this->assertEquals('someContainer1', $builder->getContainerClassName());

        $builder->setContainerName('\\Foo\\Bar');
        $this->assertEquals('Foo\\Bar', $builder->getContainerName());
        $this->assertEquals('Foo', $builder->getContainerNamespace());
        $this->assertEquals('Bar', $builder->getContainerClassName());

        $builder->setContainerName('Foo\\Bar\\Test');
        $this->assertEquals('Foo\\Bar\\Test', $builder->getContainerName());
        $this->assertEquals('Foo\\Bar', $builder->getContainerNamespace());
        $this->assertEquals('Test', $builder->getContainerClassName());
    }

    public function testAddService()
    {
        $builder = new ContainerBuilder('TestContainer');

        $this->assertEquals([], $builder->getServices());
        $this->assertEquals([], $builder->getSharedNames());

        $engineDefinition = ServiceDefinition::for(Engine::class);
        $builder->addService('engine', $engineDefinition);

        // test the default
        $this->assertEquals(['engine' => $engineDefinition], $builder->getServices());
        $this->assertEquals(['engine'], $builder->getSharedNames());

        // removing the shared service
        $builder->addService('engine', $engineDefinition, false);

        $this->assertEquals(['engine' => $engineDefinition], $builder->getServices());
        $this->assertEquals([], $builder->getSharedNames());

        // test adding a second
        $builder->addService('engine', $engineDefinition, true);
        $carDefinition = ServiceDefinition::for(Car::class);
        $builder->addService('car', $carDefinition);

        $this->assertEquals(['engine' => $engineDefinition, 'car' => $carDefinition], $builder->getServices());
        $this->assertEquals(['engine', 'car'], array_values($builder->getSharedNames()));
    }

    public function testAddServiceInvalid() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerBuilderException::class);
        $builder = new ContainerBuilder('TestContainer');
        $builder->addService('.engine', ServiceDefinition::for(Engine::class));
    }

    public function testAdd()
    {
        $builder = new ContainerBuilder('TestContainer');

        $this->assertInstanceOf(ServiceDefinition::class, $builder->add('engine', Engine::class));

        $this->assertEquals(['engine'], array_keys($builder->getServices()));
        $this->assertEquals(['engine'], $builder->getSharedNames());
    }

    public function testAddArray()
    {
        $builder = new ContainerBuilder('TestContainer');

        $builder->addArray([
            'car' => [
                'class' => Car::class,
                'arguments' => ['@engine', '@producer']
            ],
            
            'producer' => [
                'class' => Producer::class,
                'arguments' => ['BMW']
            ],

            'engine' => [
                'class' => Engine::class,
                'shared' => false,
                'calls' => [
                    ['method' => 'setPower', 'arguments' => [137]]
                ]
            ],
        ]);

        $this->assertEquals(['car', 'producer', 'engine'], array_keys($builder->getServices()));
        $this->assertEquals(['car', 'producer'], $builder->getSharedNames());
    }

    public function testGenerate()
    {
        $builder = new ContainerBuilder('TestContainer');

        $code = $builder->generate();

        $this->assertStringNotContainsStringProxy('namespace', $code);
        $this->assertStringContainsStringProxy('use ' . Container::class . ' as ClanCatsContainer', $code);
        $this->assertStringContainsStringProxy('class TestContainer', $code);
        $this->assertStringContainsStringProxy('extends ClanCatsContainer', $code);

        // Now test the behaviour with a namespace
        $builder->setContainerName('\\PHPUnit\\Test\\ExampleContainer');
        $code = $builder->generate();

        $this->assertStringContainsStringProxy('namespace PHPUnit\\Test;', $code);
        $this->assertStringContainsStringProxy('class ExampleContainer', $code);
        $this->assertStringContainsStringProxy('extends ClanCatsContainer', $code);
    }

    public function testGenerateArgumentsCode()
    {
        // Test dependency
        $builder = new ContainerBuilder('TestContainer');
        $builder->add('foo', 'Test', ['@bar']);
        $builder->add('bar', 'Bar', []);
        $this->assertStringContainsStringProxy("\Test(\$this->resolvedSharedServices['bar'] ?? \$this->resolvedSharedServices['bar'] = \$this->resolveBar())", $builder->generate());

        // test prototype dependency
        $builder = new ContainerBuilder('TestContainer');
        $builder->add('foo', 'Test', ['@bar']);
        $builder->add('bar', 'Bar', [], false);
        $this->assertStringContainsStringProxy("\Test(\$this->resolveBar())", $builder->generate());

        // Test Unknown dependency
        $builder = new ContainerBuilder('TestContainer');
        $builder->add('foo', 'Test', ['@bar']);
        $this->assertStringContainsStringProxy("\Test(\$this->get('bar'))", $builder->generate());

         // Test Container dependency
        $builder = new ContainerBuilder('TestContainer');
        $builder->add('foo', 'Test', ['@container']);
        $this->assertStringContainsStringProxy("\Test(\$this)", $builder->generate());

        // Test parameter
        $builder = new ContainerBuilder('TestContainer');
        $builder->add('foo', 'Test', [':foo']);
        $this->assertStringContainsStringProxy("Test(\$this->getParameter('foo'))", $builder->generate());

        // Test string
        $builder = new ContainerBuilder('TestContainer');
        $builder->add('foo', 'Test', ['foo']);
        $this->assertStringContainsStringProxy("Test('foo')", $builder->generate());

        // Test number
        $builder = new ContainerBuilder('TestContainer');
        $builder->add('foo', 'Test', [42]);
        $this->assertStringContainsStringProxy("Test(42)", $builder->generate());

        // Test bool
        $builder = new ContainerBuilder('TestContainer');
        $builder->add('foo', 'Test', [true]);
        $this->assertStringContainsStringProxy("Test(true)", $builder->generate());

        // Test array
        $builder = new ContainerBuilder('TestContainer');
        $builder->add('foo', 'Test', [['a', 2, 'c' => 'b']]);
        $this->assertStringContainsStringProxy("Test(array (
  0 => 'a',
  1 => 2,
  'c' => 'b',
))", $builder->generate());
    }

    /**
     * @todo optimize this test...
     */
    public function testGenerateMetaData()
    {
        $builder = new ContainerBuilder('TestContainer');

        $engineDefinition = ServiceDefinition::for(Engine::class);
        $engineDefinition->addMetaData('tags', ['Foo']);
        $engineDefinition->addMetaData('tags', ['Bar']);
        $builder->addService('engine', $engineDefinition);

        $this->assertStringContainsStringProxy("\$metadata = array", $builder->generate());
        $this->assertStringContainsStringProxy("'tags' =>", $builder->generate());
        $this->assertStringContainsStringProxy("'engine' =>", $builder->generate());
        $this->assertStringContainsStringProxy("0 => 'Foo',", $builder->generate());
    }

    public function testGenerateResolverTypes()
    {
        $builder = new ContainerBuilder('TestContainer');

        $builder->add('foo', 'Test');
        $this->assertStringContainsStringProxy("\$serviceResolverType = ['foo' => 0];", $builder->generate());
        
        $builder->add('bar', 'Test');
        $this->assertStringContainsStringProxy("\$serviceResolverType = ['foo' => 0, 'bar' => 0];", $builder->generate());
    }

    public function testGenerateResolverMapping()
    {
        $builder = new ContainerBuilder('TestContainer');

        $builder->add('foo', 'Test');
        $this->assertStringContainsStringProxy("protected array \$resolverMethods = ['foo' => 'resolveFoo'];", $builder->generate());

        $builder = new ContainerBuilder('TestContainer');

        $builder->add('foo.bar_test', 'Test');
        $this->assertStringContainsStringProxy("protected array \$resolverMethods = ['foo.bar_test' => 'resolveFooBarTest'];", $builder->generate());
    }

    public function testGenerateResolverMethods()
    {
        $builder = new ContainerBuilder('TestContainer');

        // test method name
        $builder->add('foo', 'Test');
        $this->assertStringContainsStringProxy("public function resolveFoo()", $builder->generate());

        $builder->add('foo.bar', 'Test');
        $this->assertStringContainsStringProxy("public function resolveFooBar()", $builder->generate());

        $builder->add('fooBar', 'Test');
        $this->assertStringContainsStringProxy("public function resolveFooBar1()", $builder->generate());

        // test instance creation
        $builder = new ContainerBuilder('TestContainer');

        $builder->add('foo', '\\Test');
        $this->assertStringContainsStringProxy("\$instance = new \\Test();", $builder->generate());

        // without full namespace
        $builder->add('foo', 'Test');
        $this->assertStringContainsStringProxy("\$instance = new \\Test();", $builder->generate());

        // test shared instance
        $builder = new ContainerBuilder('TestContainer');
        $builder->add('foo', 'Test', [], false);
        $this->assertStringNotContainsStringProxy("\$this->resolvedSharedServices['foo'] = \$instance;", $builder->generate());

        $builder->add('bar', 'Test', [], true);
        $this->assertStringContainsStringProxy("\$this->resolvedSharedServices['bar'] = \$instance;", $builder->generate());

        // test method calls
        $builder = new ContainerBuilder('TestContainer');

        $builder->add('person', 'Person')
            ->calls('setName', ['Mario'])
            ->calls('setAge', [42]);

        $this->assertStringContainsStringProxy("\$instance->setName('Mario');", $builder->generate());
        $this->assertStringContainsStringProxy("\$instance->setAge(42);", $builder->generate());
    }

    /**
     * I Agree, testing private methods directly is bad practice.
     * It just is so convenient here.. I should change this in future
     */
    protected function executePrivateMethod($methodName, $argument, ContainerBuilder $builder = null)
    {
        if (is_null($builder)) {
            $builder = new ContainerBuilder('TestContainer');
        }

        $method = new \ReflectionMethod(ContainerBuilder::class, $methodName);
        $method->setAccessible(true);

        return $method->invoke($builder, ...$argument);
    }

    /**
     * Test the invalid non numeric string 
     * ----
     */

    protected function assertinvalidServiceBuilderStringTrue($value)
    {
        $this->assertTrue($this->executePrivateMethod('invalidServiceBuilderString', [$value]));
    }
    protected function assertinvalidServiceBuilderStringFalse($value)
    {
        $this->assertFalse($this->executePrivateMethod('invalidServiceBuilderString', [$value]));
    }

    public function testinvalidServiceBuilderString()
    {
        $this->assertinvalidServiceBuilderStringTrue(0);
        $this->assertinvalidServiceBuilderStringTrue(42);
        $this->assertinvalidServiceBuilderStringTrue(true);
        $this->assertinvalidServiceBuilderStringTrue(false);
        $this->assertinvalidServiceBuilderStringTrue('');
        $this->assertinvalidServiceBuilderStringTrue(' ');
        $this->assertinvalidServiceBuilderStringTrue('1');
        $this->assertinvalidServiceBuilderStringTrue('1test');
        $this->assertinvalidServiceBuilderStringTrue('.');
        $this->assertinvalidServiceBuilderStringTrue('.test');
        $this->assertinvalidServiceBuilderStringTrue('_test');
        $this->assertinvalidServiceBuilderStringTrue('test.');
        $this->assertinvalidServiceBuilderStringTrue('test_');
        $this->assertinvalidServiceBuilderStringTrue('/test');
        $this->assertinvalidServiceBuilderStringTrue('\\test');
        $this->assertinvalidServiceBuilderStringTrue(' test');

        $this->assertinvalidServiceBuilderStringFalse('test');
        $this->assertinvalidServiceBuilderStringFalse('test1');
        $this->assertinvalidServiceBuilderStringFalse('foo.bar');
        $this->assertinvalidServiceBuilderStringFalse('foo_bar');
        $this->assertinvalidServiceBuilderStringFalse('fooBar');
    }

    /**
     * Test resolver method name generation
     * ----
     */

    protected function assertGetResolverMethodName($expected, $serviceName, ContainerBuilder $builder = null)
    {
        if (is_null($builder)) {
            $builder = new ContainerBuilder('TestContainer');
        }
        
        $builder->add($serviceName, Engine::class);
        $this->assertEquals($expected, $this->executePrivateMethod('getResolverMethodName', [$serviceName], $builder));
    }

    public function testGenerateResolverMethodName()
    {
        $this->assertGetResolverMethodName('resolveFoo', 'foo');
        $this->assertGetResolverMethodName('resolveFooBar', 'foo.bar');
        $this->assertGetResolverMethodName('resolveFooBar', 'fooBar');
        $this->assertGetResolverMethodName('resolveFooBarTest', 'foo.bar_test');
        $this->assertGetResolverMethodName('resolveFooBarTest', 'foo.bar.test');
    }

    public function testGenerateResolverMethodNameConflicts()
    {
        $builder = new ContainerBuilder('TestContainer');

        $this->assertGetResolverMethodName('resolveFooBar', 'foo.bar', $builder);
        $this->assertGetResolverMethodName('resolveFooBar1', 'fooBar', $builder);
        $this->assertGetResolverMethodName('resolveFooBar2', 'foo_bar', $builder);
        $this->assertGetResolverMethodName('resolveFooBar3', 'foo__bar', $builder);
    }

    public function testGenerateNormalizedServiceNameWithoutService() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerBuilderException::class);
        $this->executePrivateMethod('getResolverMethodName', ['foo']);
    }

    /**
     * Namespace import tests
     * ----
     */

    public function testImportNamespaceParameters()
    {
        $namespace = new ContainerNamespace();
        $namespace->setParameter('plane', 'A320');
        $namespace->setParameter('airport', 'TXL');

        $builder = new ContainerBuilder('TestContainer');
        $builder->importNamespace($namespace);

        $this->assertStringContainsStringProxy("'plane' => 'A320'", $builder->generate());
        $this->assertStringContainsStringProxy("'airport' => 'TXL'", $builder->generate());
    }

    public function testImportAliases()
    {
        $namespace = new ContainerNamespace();
        $namespace->setAlias('logger', 'logger.default');
        $namespace->setAlias('db', 'db.connector.mysql');

        $builder = new ContainerBuilder('TestContainer');
        $builder->importNamespace($namespace);

        $this->assertStringContainsStringProxy("'logger' => 'logger.default'", $builder->generate());
        $this->assertStringContainsStringProxy("'db' => 'db.connector.mysql'", $builder->generate());
    }

    public function testImportNamespaceServices()
    {
        $namespace = new ContainerNamespace();
        $namespace->setService('car', new ServiceDefinition(Car::class, ['@engine']));

        $builder = new ContainerBuilder('TestContainer');
        $builder->importNamespace($namespace);

        $services = $builder->getServices();

        $this->assertCount(1, $services);

        $this->assertInstanceOf(ServiceDefinition::class, $services['car']);
        $this->assertEquals(Car::class, $services['car']->getClassName());

        $arguments = $services['car']->getArguments();

        $this->assertInstanceOf(ServiceArguments::class, $arguments);
        $this->assertEquals('engine', $arguments->getAll()[0][0]);
        $this->assertEquals(ServiceArguments::DEPENDENCY, $arguments->getAll()[0][1]);
    }
}
