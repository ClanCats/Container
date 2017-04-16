<?php
namespace ClanCats\Container\Tests\ContainerParser\Parser;

use ClanCats\Container\Tests\TestCases\ParserTestCase;

use ClanCats\Container\ContainerParser\{
    Parser\ParameterDefinitionParser,
    Nodes\ParameterDefinitionNode,
    Nodes\ValueNode,
    Token as T
};

class ParameterDefinitionParserTest extends ParserTestCase
{
    protected function parameterDefnitionParserFromCode(string $code) : ParameterDefinitionParser 
    {
        return $this->parserFromCode(ParameterDefinitionParser::class, $code);
    }

    protected function parameterDefnitionNodeFromCode(string $code) : ParameterDefinitionNode 
    {
        return $this->parameterDefnitionParserFromCode($code)->parse();
    }

	public function testConstruct()
    {
    	$this->assertInstanceOf(ParameterDefinitionParser::class, $this->parameterDefnitionParserFromCode(''));
    }

    public function testStringAssign()
    {
        $def = $this->parameterDefnitionNodeFromCode(':artist: "Edgar Wasser"');

        $this->assertInstanceOf(ParameterDefinitionNode::class, $def);
        $this->assertEquals('artist', $def->getName());
        $this->assertEquals(ValueNode::TYPE_STRING, $def->getValue()->getType());
        $this->assertEquals('Edgar Wasser', $def->getValue()->getRawValue());
    }

    public function testNumberAssign()
    {
        $def = $this->parameterDefnitionNodeFromCode(':number.pi: 3.14');

        $this->assertInstanceOf(ParameterDefinitionNode::class, $def);
        $this->assertEquals('number.pi', $def->getName());
        $this->assertEquals(ValueNode::TYPE_NUMBER, $def->getValue()->getType());
        $this->assertEquals(3.14, $def->getValue()->getRawValue());
    }

    public function testNullAssign()
    {
        $def = $this->parameterDefnitionNodeFromCode(':well.nothing: null');

        $this->assertInstanceOf(ParameterDefinitionNode::class, $def);
        $this->assertEquals('well.nothing', $def->getName());
        $this->assertEquals(ValueNode::TYPE_NULL, $def->getValue()->getType());
        $this->assertEquals(null, $def->getValue()->getRawValue());
    }

    public function testBoolAssign()
    {
        $def = $this->parameterDefnitionNodeFromCode(':enable.this: true');

        $this->assertInstanceOf(ParameterDefinitionNode::class, $def);
        $this->assertEquals('enable.this', $def->getName());
        $this->assertEquals(ValueNode::TYPE_BOOL_TRUE, $def->getValue()->getType());
        $this->assertEquals(true, $def->getValue()->getRawValue());

        $def = $this->parameterDefnitionNodeFromCode(':enable.this: false');

        $this->assertInstanceOf(ParameterDefinitionNode::class, $def);
        $this->assertEquals('enable.this', $def->getName());
        $this->assertEquals(ValueNode::TYPE_BOOL_FALSE, $def->getValue()->getType());
        $this->assertEquals(false, $def->getValue()->getRawValue());
    }

    public function testOverride()
    {
        $def = $this->parameterDefnitionNodeFromCode(':default.definition: true');
        $this->assertFalse($def->isOverride());

        $def = $this->parameterDefnitionNodeFromCode('override :default.definition: false');
        $this->assertTrue($def->isOverride());
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\ContainerParserException
     */
    public function testInvalidDefinition()
    {
        $def = $this->parameterDefnitionNodeFromCode('the_parameter_indicator_is_missing');
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\ContainerParserException
     */
    public function testMissingAssign()
    {
        $def = $this->parameterDefnitionNodeFromCode(':foo 42');
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\ContainerParserException
     */
    public function testInvalidAssignValue()
    {
        $def = $this->parameterDefnitionNodeFromCode(':foo: @bar'); // actually i want this in the feature
    }
}