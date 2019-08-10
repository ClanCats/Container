<?php
namespace ClanCats\Container\Tests\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\Nodes\{
    MetaDataAssignmentNode,
    ArrayNode
};

class MetaDataAssignmentNodeTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $node = new MetaDataAssignmentNode();
        $this->assertInstanceOf(MetaDataAssignmentNode::class, $node);

        $node = new MetaDataAssignmentNode('bar');
        $this->assertEquals('bar', $node->getKey());
    }

    public function testKey()
    {
        $node = new MetaDataAssignmentNode();
        $node->setKey('foo');

        $this->assertEquals('foo', $node->getKey());
    }

    public function testStacked()
    {
        $node = new MetaDataAssignmentNode();

        $this->assertTrue($node->isStacked());
        $node->setIsStacked(false);
        $this->assertFalse($node->isStacked());
    }

    public function testData()
    {
        $node = new MetaDataAssignmentNode();
        $data = new ArrayNode();

        $this->assertFalse($node->hasData());

        $node->setData($data);
        $this->assertEquals($data, $node->getData());

        $this->assertTrue($node->hasData());
    }

    public function testArgumentAccessWithoutArguments() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\LogicalNodeException::class);
        $node = new MetaDataAssignmentNode();
        $node->getData();
    }
}
