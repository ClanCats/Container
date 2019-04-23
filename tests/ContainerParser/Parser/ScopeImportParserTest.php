<?php
namespace ClanCats\Container\Tests\ContainerParser\Parser;

use ClanCats\Container\Tests\TestCases\ParserTestCase;

use ClanCats\Container\ContainerParser\{
    Parser\ScopeImportParser,
    Nodes\ScopeNode,
    Token as T,

    Nodes\ParameterDefinitionNode
};

class ScopeImportParserTest extends ParserTestCase
{
    protected function scopeImportParserFromCode(string $code) : ScopeImportParser 
    {
        return $this->parserFromCode(ScopeImportParser::class, $code);
    }

	public function testConstruct()
    {
    	$this->assertInstanceOf(ScopeImportParser::class, $this->scopeImportParserFromCode(''));
    }

    public function testParse()
    {
        $import = $this->scopeImportParserFromCode('import a')->parse();
        $this->assertEquals('a', $import->getPath());

        $import = $this->scopeImportParserFromCode('import acme/test')->parse();
        $this->assertEquals('acme/test', $import->getPath());

        $import = $this->scopeImportParserFromCode('import acme\\test')->parse();
        $this->assertEquals('acme\\test', $import->getPath());
    }

    public function testInvalidKeyword() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $import = $this->scopeImportParserFromCode('importt test')->parse();
    }

    public function testInvalidAssign() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $import = $this->scopeImportParserFromCode('import 42')->parse();
    }
}
