<?php
namespace ClanCats\Container\Tests\ContainerParser\Parser;

use ClanCats\Container\Tests\TestCases\ParserTestCase;

use ClanCats\Container\ContainerParser\{
    Parser\ArgumentArrayParser,
    Nodes\ArgumentArrayNode,
    Nodes\ValueNode,
    Nodes\ParameterReferenceNode,
    Nodes\ServiceReferenceNode,
    Token as T
};

class ArgumentArrayParserTest extends ParserTestCase
{
    protected function argumentsArrayParserFromCode(string $code) : ArgumentArrayParser 
    {
        return $this->parserFromCode(ArgumentArrayParser::class, $code);
    }

    protected function argumentsArrayNodeFromCode(string $code) : ArgumentArrayNode 
    {
        return $this->argumentsArrayParserFromCode($code)->parse();
    }

	public function testConstruct()
    {
    	$this->assertInstanceOf(ArgumentArrayParser::class, $this->argumentsArrayParserFromCode(''));
    }

    public function testArgumentArrayOfValues()
    {
        $arguments = $this->argumentsArrayNodeFromCode('"hello", "world"');

        $this->assertCount(2, $arguments->getArguments());

        $arguments = $arguments->getArguments();

        foreach(['hello', 'world'] as $k => $word) {

            $this->assertEquals(ValueNode::TYPE_STRING, $arguments[$k]->getType());
            $this->assertEquals($word, $arguments[$k]->getRawValue());
        }

        // check the value types
        $arguments = $this->argumentsArrayNodeFromCode('"galaxy", 42, true, false, null');
        $this->assertCount(5, $arguments->getArguments());
        $arguments = $arguments->getArguments();

        $this->assertEquals(ValueNode::TYPE_STRING, $arguments[0]->getType());
        $this->assertEquals('galaxy', $arguments[0]->getRawValue());
        $this->assertEquals(ValueNode::TYPE_NUMBER, $arguments[1]->getType());
        $this->assertEquals(42, $arguments[1]->getRawValue());
        $this->assertEquals(ValueNode::TYPE_BOOL_TRUE, $arguments[2]->getType());
        $this->assertEquals(true, $arguments[2]->getRawValue());
        $this->assertEquals(ValueNode::TYPE_BOOL_FALSE, $arguments[3]->getType());
        $this->assertEquals(false, $arguments[3]->getRawValue());
        $this->assertEquals(ValueNode::TYPE_NULL, $arguments[4]->getType());
        $this->assertEquals(null, $arguments[4]->getRawValue());
    }

    public function testArgumentArrayOfParameters()
    {
        $arguments = $this->argumentsArrayNodeFromCode(':hello, :world');

        $this->assertCount(2, $arguments->getArguments());

        $arguments = $arguments->getArguments();

        foreach(['hello', 'world'] as $k => $word) {

            $argument = $arguments[$k];

            $this->assertInstanceOf(ParameterReferenceNode::class, $argument);
            $this->assertEquals($word, $argument->getName());
        }
    }

    public function testArgumentArrayOfServices()
    {
        $arguments = $this->argumentsArrayNodeFromCode('@hello, @world');

        $this->assertCount(2, $arguments->getArguments());

        $arguments = $arguments->getArguments();

        foreach(['hello', 'world'] as $k => $word) {

            $argument = $arguments[$k];

            $this->assertInstanceOf(ServiceReferenceNode::class, $argument);
            $this->assertEquals($word, $argument->getName());
        }
    }
}