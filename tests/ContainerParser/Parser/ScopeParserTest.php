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

    public function testInvalidOverrideKeyword() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->scopeNodeFromCode('override 42'); // actually i want this in the feature
    }

     public function testUnexpectedToken()
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->scopeNodeFromCode(":test: 42\n42"); // actually i want this in the feature
    }

    public function testParserExceptionReportsCorrectLineAfterHeredoc()
    {
        // the point of the heredoc line-counting fix is that error messages built
        // from Token::getLine() stay accurate after a heredoc block; exercise the
        // real exception message here, not just the token accessor.
        $code = ":doc: <<<EOT\nhello\nworld\nEOT\n:test: 42\n42";

        // the offending token is the trailing, standalone "42" - i.e. the *last*
        // occurrence of "42" in the source (the first one is part of ":test: 42").
        $badTokenPos = strrpos($code, '42');
        $this->assertNotFalse($badTokenPos);
        $expectedLine = substr_count(substr($code, 0, $badTokenPos), "\n") + 1;

        try {
            $this->scopeNodeFromCode($code);
            $this->fail('Expected a ContainerParserException to be thrown.');
        } catch (\ClanCats\Container\Exceptions\ContainerParserException $e) {
            $this->assertStringContainsString('given at line ' . $expectedLine, $e->getMessage());
        }
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
