<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    ContainerBuilder,
    ServiceDefinition
};
use ClanCats\Container\Tests\TestServices\{
    Car, Engine, Producer
};

class ContainerBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testContainerName()
    {
        $builder = new ContainerBuilder('TestContainer');

        $this->assertEquals('TestContainer', $builder->getContainerName());
    }

    /**
     * @expectedException TypeError
     */
    public function testContainerNameInvalidType()
    {
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
     * @expectedException ClanCats\Container\Exceptions\ContainerBuilderException
     * @dataProvider invalidContainerNameProvider
     */
    public function testContainerNameEmpty($name)
    {
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

        // has class definition
        $this->assertContains('class TestContainer', $code);

        // has superclass
        $this->assertContains('extends ClanCatsContainer', $code);
    }

    public function testGenerateArgumentsCode()
    {
        $builder = new ContainerBuilder('TestContainer');

        // Test dependency
        $builder->add('foo', 'Test', ['@foo']);
        $this->assertContains("Test(\$this->resolvedSharedServices['foo'] ?? \$this->resolvedSharedServices['foo'] = \$this->resolveFoo())", $builder->generate());

        // Test parameter
        $builder->add('foo', 'Test', [':foo']);
        $this->assertContains("Test(\$this->getParameter('foo'))", $builder->generate());

        // Test string
        $builder->add('foo', 'Test', ['foo']);
        $this->assertContains("Test('foo')", $builder->generate());

        // Test number
        $builder->add('foo', 'Test', [42]);
        $this->assertContains("Test(42)", $builder->generate());

        // Test bool
        $builder->add('foo', 'Test', [true]);
        $this->assertContains("Test(true)", $builder->generate());

        // Test array
        $builder->add('foo', 'Test', [['a', 2, 'c' => 'b']]);
        $this->assertContains("Test(array (
  0 => 'a',
  1 => 2,
  'c' => 'b',
))", $builder->generate());
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\ContainerBuilderException
     */
    public function testGenerateArgumentsCodeInvalid()
    {
        $builder = new ContainerBuilder('TestContainer');
        $builder->add('foo', 'Test', ['@42']);
        $builder->generate();
    }

    public function testGenerateResolverTypes()
    {
        $builder = new ContainerBuilder('TestContainer');

        $builder->add('foo', 'Test');
        $this->assertContains("\$serviceResolverType = ['foo' => 0];", $builder->generate());
        
        $builder->add('bar', 'Test');
        $this->assertContains("\$serviceResolverType = ['foo' => 0, 'bar' => 0];", $builder->generate());
    }

    public function testGenerateResolverMapping()
    {
        $builder = new ContainerBuilder('TestContainer');

        $builder->add('foo', 'Test');
        $this->assertContains("protected \$resolverMethods = ['foo' => 'resolveFoo'];", $builder->generate());
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

    /**
     * @expectedException ClanCats\Container\Exceptions\ContainerBuilderException
     */
    public function testGenerateNormalizedServiceNameWithoutService()
    {
        $this->executePrivateMethod('getResolverMethodName', ['foo']);
    }
}
