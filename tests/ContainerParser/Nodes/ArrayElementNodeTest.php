<?php
namespace ClanCats\Container\Tests\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\Nodes\{
    ArrayNode,
    ArrayElementNode,
    ValueNode,
    ParameterReferenceNode,
    ServiceReferenceNode
};

class ArrayElementNodeTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $node = new ArrayElementNode('test', new ValueNode(42, ValueNode::TYPE_NUMBER));
        $this->assertInstanceOf(ArrayElementNode::class, $node);
    }

    public function testScalarValue()
    {
        // number
        $node = new ArrayElementNode(0, new ValueNode(42, ValueNode::TYPE_NUMBER));
        $this->assertEquals(0, $node->getKey());
        $this->assertEquals(42, $node->getValue()->getRawValue());

        // string
        $node = new ArrayElementNode(1, new ValueNode("hey", ValueNode::TYPE_STRING));
        $this->assertEquals(1, $node->getKey());
        $this->assertEquals("hey", $node->getValue()->getRawValue());

        // bool
        $node = new ArrayElementNode(2, new ValueNode(true, ValueNode::TYPE_BOOL_TRUE));
        $this->assertEquals(2, $node->getKey());
        $this->assertEquals(true, $node->getValue()->getRawValue());
    }

    public function testAssocKey()
    {
        $node = new ArrayElementNode("the answer", new ValueNode(42, ValueNode::TYPE_NUMBER));
        $this->assertEquals("the answer", $node->getKey());
    }

    public function testReferenceValue()
    {
        // service
        $node = new ArrayElementNode(0, new ServiceReferenceNode("test"));
        $this->assertEquals("test", $node->getValue()->getName());

        // parameter
        $node = new ArrayElementNode(0, new ParameterReferenceNode("test"));
        $this->assertEquals("test", $node->getValue()->getName());
    }

    public function testArrayValue()
    {
        $node = new ArrayElementNode(0, new ArrayNode);
        $this->assertInstanceOf(ArrayNode::class, $node->getValue());
    }
}