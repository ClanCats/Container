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
        $node = new ServiceMethodCallNode();
        $this->assertInstanceOf(ServiceMethodCallNode::class, $node);
    }

    public function testName()
    {
        $node = new ServiceMethodCallNode();
        $node->setName('foo');

        $this->assertEquals('foo', $node->getName());
    }

    public function testStacked()
    {
        $node = new ServiceMethodCallNode();

        $this->assertFalse($node->isStacked());
        $node->setIsStacked(true);
        $this->assertTrue($node->isStacked());
    }

    public function testArguments()
    {
        $node = new ServiceMethodCallNode();
        $arguments = new ArgumentArrayNode();

        $this->assertFalse($node->hasArguments());

        $node->setArguments($arguments);
        $this->assertEquals($arguments, $node->getArguments());

        $this->assertTrue($node->hasArguments());
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\LogicalNodeException
     */
    public function testArgumentAccessWithoutArguments()
    {
        $node = new ServiceMethodCallNode();
        $node->getArguments();
    }
}