<?php
namespace ClanCats\Container\Tests\ContainerParser\Parser;

use ClanCats\Container\Tests\TestCases\ParserTestCase;

use ClanCats\Container\ContainerParser\{
    Parser\ReferenceParser,
    Nodes\ParameterReferenceNode,
    Nodes\ServiceReferenceNode,
    Token as T
};

class ReferenceParserTest extends ParserTestCase
{
    protected function referenceParserFromCode(string $code) : ReferenceParser 
    {
        return $this->parserFromCode(ReferenceParser::class, $code);
    }

	public function testConstruct()
    {
    	$this->assertInstanceOf(ReferenceParser::class, $this->referenceParserFromCode(''));
    }

    public function testParameter()
    {
        $parameter = $this->referenceParserFromCode(':parameter')->parse();

        $this->assertInstanceOf(ParameterReferenceNode::class, $parameter);
        $this->assertEquals('parameter', $parameter->getName());
    }

    public function testService()
    {
        $service = $this->referenceParserFromCode('@service')->parse();

        $this->assertInstanceOf(ServiceReferenceNode::class, $service);
        $this->assertEquals('service', $service->getName());
    }

    public function testInvalid()
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->referenceParserFromCode('invalid')->parse();
    }

    public function testAdvancesTokenIndex()
    {
        // the parser must consume the single token it reads so that
        // parseChild() / getParsedTokensCount() advances callers correctly
        $parser = $this->referenceParserFromCode('@service');
        $parser->parse();

        $this->assertEquals(1, $parser->getParsedTokensCount());
        $this->assertEquals(1, $parser->getIndex());
    }
}
