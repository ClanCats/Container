<?php
namespace ClanCats\Container\Tests\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\Nodes\{
    ServiceDefinitionNode,
    ArgumentArrayNode
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
        $node = new ServiceDefinitionNode();
        $node->getArguments();
    }
}