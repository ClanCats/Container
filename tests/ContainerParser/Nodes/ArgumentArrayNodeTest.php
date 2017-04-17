<?php
namespace ClanCats\Container\Tests\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\Nodes\{
    ArgumentArrayNode,
    ValueNode,
    ParameterReferenceNode,
    ServiceReferenceNode
};

class ArgumentArrayNodeTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $node = new ArgumentArrayNode();
        $this->assertInstanceOf(ArgumentArrayNode::class, $node);
    }

    public function testArguments()
    {
        $node = new ArgumentArrayNode();

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