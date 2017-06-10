<?php
namespace ClanCats\Container\Tests\ContainerParser\Parser;

use ClanCats\Container\Tests\TestCases\ParserTestCase;

use ClanCats\Container\ContainerParser\{
    Parser\ServiceMethodCallParser,
    Nodes\ServiceMethodCallNode,
    Nodes\ValueNode,
    Token as T
};

class ServiceMethodCallParserTest extends ParserTestCase
{
    protected function serviceDefnitionParserFromCode(string $code) : ServiceMethodCallParser 
    {
        return $this->parserFromCode(ServiceMethodCallParser::class, $code);
    }

    protected function serviceDefnitionNodeFromCode(string $code) : ServiceMethodCallNode 
    {
        return $this->serviceDefnitionParserFromCode($code)->parse();
    }

	public function testConstruct()
    {
    	$this->assertInstanceOf(ServiceMethodCallParser::class, $this->serviceDefnitionParserFromCode(''));
    }
    
    public function testSimpleAssign()
    {
        $def = $this->serviceDefnitionNodeFromCode('- setName("Ray")');

        $this->assertEquals('setName', $def->getName());
        $this->assertTrue($def->hasArguments());

        // get the arguments
       	$arguments = $def->getArguments()->getArguments();

       	$this->assertCount(1, $arguments);

       	$name = $arguments[0];

       	$this->assertEquals('Ray', $name->getRawValue());
    }
}