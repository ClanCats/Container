<?php
namespace ClanCats\Container\Tests\ContainerParser\Parser;

use ClanCats\Container\Tests\TestCases\ParserTestCase;

use ClanCats\Container\ContainerParser\{
    Parser\ScopeParser,
    Token as T
};

class ScopeParserTest extends ParserTestCase
{
    protected function scopeParserFromCode(string $code) : ScopeParser 
    {
        return $this->parserFromCode(ScopeParser::class, $code);
    }

	public function testConstruct()
    {
    	$this->assertInstanceOf(ScopeParser::class, $this->scopeParserFromCode(''));
    }

    public function testParseParameterDefinition()
    {
        $parser = $this->scopeParserFromCode(':artist: "Edgar Wasser"');
        $scopeNode = $parser->parse();
    }
}