<?php
namespace ClanCats\Container\Tests\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\{
    Nodes\ValueNode,
    Token
};

class ValueNodeTest extends \PHPUnit\Framework\TestCase
{
	public function testConstruct()
    {
    	$node = new ValueNode(null, ValueNode::TYPE_NULL);

    	$this->assertNull($node->getRawValue());
    	$this->assertEquals(ValueNode::TYPE_NULL, $node->getType());
    }

    public function testFromToken()
    {
        // string
    	$node = ValueNode::fromToken(new Token(0, Token::TOKEN_STRING, "'Fatoni'"));
        $this->assertEquals("Fatoni", $node->getRawValue());
        $this->assertEquals(ValueNode::TYPE_STRING, $node->getType());

        // number
        $node = ValueNode::fromToken(new Token(0, Token::TOKEN_NUMBER, "3.14"));
        $this->assertEquals(3.14, $node->getRawValue());
        $this->assertEquals(ValueNode::TYPE_NUMBER, $node->getType());

        // bool true
        $node = ValueNode::fromToken(new Token(0, Token::TOKEN_BOOL_TRUE, "true"));
        $this->assertEquals(true, $node->getRawValue());
        $this->assertEquals(ValueNode::TYPE_BOOL_TRUE, $node->getType());

        // bool false
        $node = ValueNode::fromToken(new Token(0, Token::TOKEN_BOOL_FALSE, "false"));
        $this->assertEquals(false, $node->getRawValue());
        $this->assertEquals(ValueNode::TYPE_BOOL_FALSE, $node->getType());

        // null
        $node = ValueNode::fromToken(new Token(0, Token::TOKEN_NULL, "null"));
        $this->assertEquals(null, $node->getRawValue());
        $this->assertEquals(ValueNode::TYPE_NULL, $node->getType());
    }

    public function testSetType()
    {
    	$node = new ValueNode(null, ValueNode::TYPE_NULL);

    	foreach([
            ValueNode::TYPE_STRING, 
            ValueNode::TYPE_NUMBER, 
            ValueNode::TYPE_BOOL_TRUE, 
            ValueNode::TYPE_BOOL_FALSE,
            ValueNode::TYPE_NULL,
        ] as $type) 
        {
            $node->setType($type);
            $this->assertEquals($type, $node->getType());
        }
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\LogicalNodeException
     */
    public function testSetTypeUnknown()
    {
        $node = new ValueNode(null, ValueNode::TYPE_NULL);
        $node->setType(234234);
    }
}