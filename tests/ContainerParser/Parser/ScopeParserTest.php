<?php
namespace ClanCats\Container\Tests\ContainerParser\Parser;

use ClanCats\Container\Tests\TestCases\ParserTestCase;

use ClanCats\Container\ContainerParser\{
    Parser\ScopeParser,
    Nodes\ScopeNode,
    Token as T,

    Nodes\ParameterDefinitionNode
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
        $scopeNode = $this->scopeNodeFromCode(':artist.eddi: "Edgar Wasser"');

        $nodes = $scopeNode->getNodes();
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(ParameterDefinitionNode::class, $nodes[0]);

        // multiple
        $scopeNode = $this->scopeNodeFromCode(":artist.toni: 'Fatoni'\n:artist.justus: 'Juse Ju'");

        $nodes = $scopeNode->getNodes();
        $this->assertCount(2, $nodes);
        $this->assertInstanceOf(ParameterDefinitionNode::class, $nodes[0]);
        $this->assertInstanceOf(ParameterDefinitionNode::class, $nodes[1]);
    }
}