<?php
namespace ClanCats\Container\Tests\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\Nodes\{
    ArrayNode,
    ArrayElementNode,
    ValueNode,
    ParameterReferenceNode,
    ServiceReferenceNode
};

class ArrayNodeTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $node = new ArrayNode();
        $this->assertInstanceOf(ArrayNode::class, $node);
    }

    public function testAddElement()
    {
        $node = new ArrayNode();
        $this->assertEquals([], $node->getElements());

        $node->addElement(new ArrayElementNode('one', new ValueNode(1, ValueNode::TYPE_NUMBER)));

        $this->assertCount(1, $node->getElements());

        $node->addElement(new ArrayElementNode('two', new ValueNode(2, ValueNode::TYPE_NUMBER)));

        $this->assertCount(2, $node->getElements());

        $keys = [];
        foreach($node->getElements() as $element)
        {
            $keys[] = $element->getKey();
        }

        $this->assertEquals(['one', 'two'], $keys);
    }

    public function testHas()
    {
        $node = new ArrayNode();
        $this->assertFalse($node->has('nope'));

        $node->addElement(new ArrayElementNode('yep', new ValueNode(1, ValueNode::TYPE_NUMBER)));

        $this->assertTrue($node->has('yep'));
    }

    public function testPush()
    {
        $node = new ArrayNode();

        $node->push(new ValueNode(1, ValueNode::TYPE_NUMBER));
        $node->push(new ValueNode(2, ValueNode::TYPE_NUMBER));
        $node->push(new ValueNode(3, ValueNode::TYPE_NUMBER));

        $this->assertCount(3, $node->getElements());
        
        // check the keys
        $keys = [];
        foreach($node->getElements() as $element) {
            $keys[] = $element->getKey();
        }
        $this->assertEquals([0, 1, 2], $keys);

        // Manually set item on index 3 to check if the next pushed element is not overwritten
        $node->addElement(new ArrayElementNode(3, new ValueNode(4, ValueNode::TYPE_NUMBER)));
        $node->push(new ValueNode(5, ValueNode::TYPE_NUMBER));

        $this->assertCount(5, $node->getElements());

        // check the keys
        $keys = [];
        foreach($node->getElements() as $element) {
            $keys[] = $element->getKey();
        }
        $this->assertEquals([0, 1, 2, 3, 4], $keys);

    }

    public function testConvertToNative()
    {
        // simple array
        $node = new ArrayNode();

        $node->push(new ValueNode(1, ValueNode::TYPE_NUMBER));
        $node->push(new ValueNode(2, ValueNode::TYPE_NUMBER));
        $node->push(new ValueNode(3, ValueNode::TYPE_NUMBER));

        $this->assertEquals([1, 2, 3], $node->convertToNativeArray());

        // assoc
        $node = new ArrayNode();
        $node->addElement(new ArrayElementNode(1, new ValueNode(10, ValueNode::TYPE_NUMBER)));
        $node->addElement(new ArrayElementNode('test', new ValueNode(20, ValueNode::TYPE_NUMBER)));

        $this->assertEquals([1 => 10, 'test' => 20], $node->convertToNativeArray());

        // multidimensional
        $parent = new ArrayNode();
        $parent->addElement(new ArrayElementNode('A', $node));
        $parent->addElement(new ArrayElementNode('B', $node));

        $this->assertEquals(['A' => [1 => 10, 'test' => 20], 'B' => [1 => 10, 'test' => 20]], $parent->convertToNativeArray());
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\LogicalNodeException
     */
    public function testConvertToNativeWithRef()
    {
        $node = new ArrayNode();
        $node->push(new ParameterReferenceNode('foo'));
        $node->convertToNativeArray();
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\LogicalNodeException
     */
    public function testConvertToNativeWithRefService()
    {
        $node = new ArrayNode();
        $node->push(new ServiceReferenceNode('bar'));
        $node->convertToNativeArray();
    }
}