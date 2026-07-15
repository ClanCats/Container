<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    ServiceDefinition,
    ServiceArguments
};

class ServiceDefinitionTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $definition = new ServiceDefinition('\\Acme\\Demo');
        $this->assertEquals('\\Acme\\Demo', $definition->getClassName());
        $this->assertCount(0, $definition->getArguments()->getAll());

        $definition = new ServiceDefinition('\\Acme\\Demo', ['@foo', ':bar', 42]);
        $this->assertEquals('\\Acme\\Demo', $definition->getClassName());
        $this->assertCount(3, $definition->getArguments()->getAll());
    }

    public function testFor()
    {
        $definition = ServiceDefinition::for('\\Acme\\Demo', ['@foo', ':bar', 42]);

        $this->assertInstanceOf(ServiceDefinition::class, $definition);
        $this->assertEquals('\\Acme\\Demo', $definition->getClassName());

        $arguments = $definition->getArguments()->getAll();
        $this->assertCount(3, $arguments);

        $this->assertEquals('foo', $arguments[0][0]);
        $this->assertEquals(ServiceArguments::DEPENDENCY, $arguments[0][1]);
        $this->assertEquals('bar', $arguments[1][0]);
        $this->assertEquals(ServiceArguments::PARAMETER, $arguments[1][1]);
        $this->assertEquals(42, $arguments[2][0]);
        $this->assertEquals(ServiceArguments::RAW, $arguments[2][1]);
    }

    public function testFromArray()
    {
        $definition = ServiceDefinition::fromArray([
            'class' => '\\Acme\\Demo',
            'arguments' => ['@foo', ':bar'],
            'calls' => [
                ['method' => 'setName', 'arguments' => [':demo.name']],
                ['method' => 'setAge', 'arguments' => [42]],
            ],
        ]);

        $this->assertInstanceOf(ServiceDefinition::class, $definition);
        $this->assertEquals('\\Acme\\Demo', $definition->getClassName());
        $this->assertCount(2, $definition->getArguments()->getAll());

        $calls = $definition->getMethodCalls();
        $this->assertCount(2, $calls);
        $this->assertEquals('setName', $calls[0][0]);
        $this->assertInstanceOf(ServiceArguments::class, $calls[0][1]);
        $this->assertEquals('setAge', $calls[1][0]);
    }

    public function testFromArrayWithoutOptionalKeys()
    {
        $definition = ServiceDefinition::fromArray([
            'class' => '\\Acme\\Demo',
        ]);

        $this->assertEquals('\\Acme\\Demo', $definition->getClassName());
        $this->assertCount(0, $definition->getArguments()->getAll());
        $this->assertCount(0, $definition->getMethodCalls());
    }

    public function testFromArrayMissingClass()
    {
        $this->expectException(\ClanCats\Container\Exceptions\InvalidServiceException::class);
        ServiceDefinition::fromArray([
            'arguments' => ['@foo'],
        ]);
    }

    public function testFromArrayCallMissingMethod()
    {
        $this->expectException(\ClanCats\Container\Exceptions\InvalidServiceException::class);
        ServiceDefinition::fromArray([
            'class' => '\\Acme\\Demo',
            'calls' => [
                ['arguments' => [':demo.name']],
            ],
        ]);
    }

    public function testFromArrayCallMissingArguments()
    {
        $this->expectException(\ClanCats\Container\Exceptions\InvalidServiceException::class);
        ServiceDefinition::fromArray([
            'class' => '\\Acme\\Demo',
            'calls' => [
                ['method' => 'setName'],
            ],
        ]);
    }

    public function testArguments()
    {
        $definition = new ServiceDefinition('\\Acme\\Demo', ['foo']);

        $this->assertInstanceOf(ServiceDefinition::class, $definition->arguments(['@bar', ':baz']));

        $arguments = $definition->getArguments()->getAll();
        $this->assertCount(3, $arguments);
        $this->assertEquals('foo', $arguments[0][0]);
        $this->assertEquals(ServiceArguments::RAW, $arguments[0][1]);
        $this->assertEquals('bar', $arguments[1][0]);
        $this->assertEquals(ServiceArguments::DEPENDENCY, $arguments[1][1]);
        $this->assertEquals('baz', $arguments[2][0]);
        $this->assertEquals(ServiceArguments::PARAMETER, $arguments[2][1]);
    }

    public function testRawArgument()
    {
        $definition = new ServiceDefinition('\\Acme\\Demo');

        $this->assertInstanceOf(ServiceDefinition::class, $definition->addRawArgument('foo'));

        $arguments = $definition->getArguments()->getAll();
        $this->assertCount(1, $arguments);
        $this->assertEquals('foo', $arguments[0][0]);
        $this->assertEquals(ServiceArguments::RAW, $arguments[0][1]);
    }

    public function testDependencyArgument()
    {
        $definition = new ServiceDefinition('\\Acme\\Demo');

        $this->assertInstanceOf(ServiceDefinition::class, $definition->addDependencyArgument('storage'));

        $arguments = $definition->getArguments()->getAll();
        $this->assertCount(1, $arguments);
        $this->assertEquals('storage', $arguments[0][0]);
        $this->assertEquals(ServiceArguments::DEPENDENCY, $arguments[0][1]);
    }

    public function testParameterArgument()
    {
        $definition = new ServiceDefinition('\\Acme\\Demo');

        $this->assertInstanceOf(ServiceDefinition::class, $definition->addParameterArgument('session_token'));

        $arguments = $definition->getArguments()->getAll();
        $this->assertCount(1, $arguments);
        $this->assertEquals('session_token', $arguments[0][0]);
        $this->assertEquals(ServiceArguments::PARAMETER, $arguments[0][1]);
    }

    public function testCalls()
    {
        $definition = new ServiceDefinition('\\Acme\\Demo');

        $this->assertInstanceOf(ServiceDefinition::class, $definition->calls('setName', ['Mario']));
        $definition->calls('setAge', [42]);

        $calls = $definition->getMethodCalls();
        $this->assertCount(2, $calls);
        $this->assertEquals('setName', $calls[0][0]);
        $this->assertInstanceOf(ServiceArguments::class, $calls[0][1]);
        $this->assertEquals('Mario', $calls[0][1]->getAll()[0][0]);
        $this->assertEquals('setAge', $calls[1][0]);
    }

    public function testAddMethodCall()
    {
        $definition = new ServiceDefinition('\\Acme\\Demo');

        $arguments = new ServiceArguments(['Mario']);
        $this->assertInstanceOf(ServiceDefinition::class, $definition->addMethodCall('setName', $arguments));

        $calls = $definition->getMethodCalls();
        $this->assertCount(1, $calls);
        $this->assertEquals('setName', $calls[0][0]);
        $this->assertSame($arguments, $calls[0][1]);
    }

    public function testCallsToSameMethodStack()
    {
        $definition = new ServiceDefinition('\\Acme\\Demo');

        $definition->calls('push', ['foo']);
        $definition->calls('push', ['bar']);

        $calls = $definition->getMethodCalls();
        $this->assertCount(2, $calls);
        $this->assertEquals('push', $calls[0][0]);
        $this->assertEquals('push', $calls[1][0]);
    }

    public function testMetaData()
    {
        $definition = new ServiceDefinition('\\Acme\\Demo');

        $this->assertEquals([], $definition->getMetaData());

        $definition->addMetaData('tags', ['Foo']);
        $definition->addMetaData('tags', ['Bar']);
        $definition->addMetaData('routing', ['GET', '/demo']);

        $this->assertEquals([
            'tags' => [['Foo'], ['Bar']],
            'routing' => [['GET', '/demo']],
        ], $definition->getMetaData());
    }
}
