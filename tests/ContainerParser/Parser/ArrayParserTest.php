<?php
namespace ClanCats\Container\Tests\ContainerParser\Parser;

use ClanCats\Container\Tests\TestCases\ParserTestCase;

use ClanCats\Container\ContainerParser\{
    Parser\ArrayParser,
    Nodes\ArrayNode,
    Nodes\ValueNode,
    Nodes\ParameterReferenceNode,
    Nodes\ServiceReferenceNode,
    Token as T
};

class ArrayParserTest extends ParserTestCase
{
    protected function arrayParserFromCode(string $code) : ArrayParser 
    {
        return $this->parserFromCode(ArrayParser::class, $code);
    }

    protected function arrayNodeFromCode(string $code) : ArrayNode 
    {
        return $this->arrayParserFromCode($code)->parse();
    }

	public function testConstruct()
    {
    	$this->assertInstanceOf(ArrayParser::class, $this->arrayParserFromCode(''));
    }

    // public function testArrayOfValues()
    // {
    //     $array = $this->arrayNodeFromCode('"hello", "world"');

    //     var_dump($array);
    // }
}