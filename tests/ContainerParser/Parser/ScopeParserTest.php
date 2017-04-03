<?php
namespace ClanCats\Container\Tests\ContainerParser\Parser;

use ClanCats\Container\Tests\TestCases\ParserTestCase;

use ClanCats\Container\ContainerParser\{
    Parser\ScopeParser,
    Nodes\ScopeNode,
    Token as T
};

class ScopeParserTest extends ParserTestCase
{
    protected function scopeParserFromCode(string $code) : ScopeParser 
    {
        return $this->parserFromCode(ScopeParser::class, $code);
    }

    protected function scopeNodeFromCode(string $code) : ScopeNode 
    {
        return $this->scopeParserFromCode($code)->parse();
    }

	public function testConstruct()
    {
    	$this->assertInstanceOf(ScopeParser::class, $this->scopeParserFromCode(''));
    }

    public function testParseParameterDefinition()
    {
        $scopeNode = $this->scopeNodeFromCode(':artist: "Edgar Wasser"');

        //var_dump($scopeNode); die;
    }
}