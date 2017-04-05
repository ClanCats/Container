<?php
namespace ClanCats\Container\Tests\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\{
    Nodes\ParameterDefinitionNode,
    Nodes\ValueNode,
    Token
};

class ParameterDefinitionNodeTest extends \PHPUnit\Framework\TestCase
{
	public function testConstruct()
    {
    	$node = new ParameterDefinitionNode('test', new ValueNode(null, ValueNode::TYPE_NULL));
        $this->assertInstanceOf(ParameterDefinitionNode::class, $node);
    }

    public function testName()
    {
        $node = new ParameterDefinitionNode('test', new ValueNode(null, ValueNode::TYPE_NULL));

        $this->assertEquals('test', $node->getName());
        
        $node->setName('bar');

        $this->assertEquals('bar', $node->getName());
    }

    public function testValue()
    {
        $value = new ValueNode(null, ValueNode::TYPE_NULL);
        $node = new ParameterDefinitionNode('test', $value);

        $this->assertEquals($value, $node->getValue());
    }
}