<?php
namespace ClanCats\Container\Tests\ContainerParser\Parser;

use ClanCats\Container\Tests\TestCases\ParserTestCase;

use ClanCats\Container\ContainerParser\{
    Parser\ScopeParser,
    Nodes\ScopeNode,
    Token as T,

    Nodes\ParameterDefinitionNode,
    Nodes\ServiceDefinitionNode,
    Nodes\ScopeImportNode
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

        // override
        $scopeNode = $this->scopeNodeFromCode("override :artist.dj: 'V -Reater'\n:artist.markus: 'maeckes'");

        $nodes = $scopeNode->getNodes();
        $this->assertCount(2, $nodes);
        $this->assertInstanceOf(ParameterDefinitionNode::class, $nodes[0]);
        $this->assertInstanceOf(ParameterDefinitionNode::class, $nodes[1]);
        $this->assertTrue($nodes[0]->isOverride());
        $this->assertFalse($nodes[1]->isOverride());
    }

    /**
     * @expectedException ClanCats\Container\Exceptions\ContainerParserException
     */
    public function testInvalidOverrideKeyword()
    {
        $this->scopeNodeFromCode('override 42'); // actually i want this in the feature
    }

     /**
     * @expectedException ClanCats\Container\Exceptions\ContainerParserException
     */
    public function testUnexpectedToken()
    {
        $this->scopeNodeFromCode(":test: 42\n42"); // actually i want this in the feature
    }

    public function testParseImport()
    {
        $scopeNode = $this->scopeNodeFromCode('import foo/bar');

        $nodes = $scopeNode->getNodes();

        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(ScopeImportNode::class, $nodes[0]);

        $this->assertEquals('foo/bar', $nodes[0]->getPath());
    }

    public function testParseServiceDefinition()
    {
        $scopeNode = $this->scopeNodeFromCode('@artist.eddi: Person(:artist.eddi)');

        $nodes = $scopeNode->getNodes();
        $this->assertCount(1, $nodes);
        $this->assertInstanceOf(ServiceDefinitionNode::class, $nodes[0]);

        $this->assertEquals('artist.eddi', $nodes[0]->getName());
        $this->assertEquals('Person', $nodes[0]->getClassName());

        // test multiple service defintions after another
        $scopeNode = $this->scopeNodeFromCode("@engine.pulse: Acme\Engine(40, 42)\n@engine.pulse: Acme\Engine(40)");

        $nodes = $scopeNode->getNodes();
        $this->assertCount(2, $nodes);
    }
}