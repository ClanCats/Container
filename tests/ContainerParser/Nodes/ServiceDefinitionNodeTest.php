<?php
namespace ClanCats\Container\Tests\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\Nodes\{
    ServiceDefinitionNode,
    ValueNode,
    ParameterReferenceNode,
    ServiceReferenceNode
};

class ServiceDefinitionNodeTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $node = new ServiceDefinitionNode();
        $this->assertInstanceOf(ServiceDefinitionNode::class, $node);
    }

    public function testName()
    {
        $node = new ServiceDefinitionNode();
        $node->setName('foo');

        $this->assertEquals('foo', $node->getName());
    }

    public function testClassName()
    {
        $node = new ServiceDefinitionNode();
        $node->setClassName('foo');
        
        $this->assertEquals('foo', $node->getClassName());
    }

    public function testOverride()
    {
        $node = new ServiceDefinitionNode();

        $this->assertFalse($node->isOverride());
        $node->setIsOverride(true);
        $this->assertTrue($node->isOverride());
    }

    public function testArguments()
    {
        $node = new ServiceDefinitionNode();

        $argument1 = new ValueNode(42, ValueNode::TYPE_NUMBER);
        $argument2 = new ParameterReferenceNode('test');
        $argument3 = new ServiceReferenceNode('dude');

        $this->assertEquals([], $node->getArguments());

        $node->addArgument($argument1);

        $this->assertEquals([$argument1], $node->getArguments());

        // add all others
        $node->addArgument($argument2);
        $node->addArgument($argument3);

        $this->assertEquals([$argument1, $argument2, $argument3], $node->getArguments());
    }
}