<?php
namespace ClanCats\Container\Tests\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\Nodes\{
    ServiceMethodCallNode,
    ArgumentArrayNode
};

class ServiceMethodCallNodeTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $node = new ServiceMethodCallNode('test');
        $this->assertInstanceOf(ServiceMethodCallNode::class, $node);
    }

    public function testName()
    {
        $node = new ServiceMethodCallNode('test');
        $node->setName('foo');

        $this->assertEquals('foo', $node->getName());
    }

    public function testStacked()
    {
        $node = new ServiceMethodCallNode('test');

        $this->assertFalse($node->isStacked());
        $node->setIsStacked(true);
        $this->assertTrue($node->isStacked());
    }

    public function testArguments()
    {
        $node = new ServiceMethodCallNode('test');
        $arguments = new ArgumentArrayNode();

        $this->assertFalse($node->hasArguments());

        $node->setArguments($arguments);
        $this->assertEquals($arguments, $node->getArguments());

        $this->assertTrue($node->hasArguments());
    }

    public function testArgumentAccessWithoutArguments() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\LogicalNodeException::class);
        $node = new ServiceMethodCallNode('test');
        $node->getArguments();
    }
}
