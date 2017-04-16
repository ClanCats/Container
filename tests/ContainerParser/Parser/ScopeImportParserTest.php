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

    /**
     * @expectedException ClanCats\Container\Exceptions\ContainerParserException
     */
    public function testInvalidKeyword()
    {
        $import = $this->scopeImportParserFromCode('importt test')->parse();
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\ContainerParserException
     */
    public function testInvalidAssign()
    {
        $import = $this->scopeImportParserFromCode('import 42')->parse();
    }
}