<?php
namespace ClanCats\Container\Tests\ContainerParser\Parser;

use ClanCats\Container\Tests\TestCases\ParserTestCase;

use ClanCats\Container\ContainerParser\{
    Parser\ServiceMetaDataParser,
    Nodes\MetaDataAssignmentNode,
    Nodes\ValueNode,
    Token as T
};

/**
 * @group MetaDataParser
 */
class ServiceMetaDataParserTest extends ParserTestCase
{
    protected function serviceDefnitionParserFromCode(string $code) : ServiceMetaDataParser 
    {
        return $this->parserFromCode(ServiceMetaDataParser::class, $code);
    }

    protected function serviceDefnitionNodeFromCode(string $code) : MetaDataAssignmentNode 
    {
        return $this->serviceDefnitionParserFromCode($code)->parse();
    }

	public function testConstruct()
    {
    	$this->assertInstanceOf(ServiceMetaDataParser::class, $this->serviceDefnitionParserFromCode(''));
    }
    
    public function testSimpleAssign()
    {
        $def = $this->serviceDefnitionNodeFromCode('= tag: "Foo"');

        $this->assertEquals('tag', $def->getKey());
        $this->assertTrue($def->hasData());

        // get the arguments
       	$data = $def->getData()->convertToNativeArray();

       	$this->assertCount(1, $data);
       	$this->assertEquals(['Foo'], $data);
    }

    public function testMetaAssignmentWithoutArguments()
    {
        $def = $this->serviceDefnitionNodeFromCode('= command');

        $this->assertEquals('command', $def->getKey());
        $this->assertFalse($def->hasData());
    }


    public function testMetaAssignmentWithWhitespaces()
    {
        $def = $this->serviceDefnitionNodeFromCode('      = something');

        $this->assertEquals('something', $def->getKey());
        $this->assertFalse($def->hasData());
    }

    public function testMetaAssignmentWithManyLinesInBetween()
    {
        $def = $this->serviceDefnitionNodeFromCode("      = something: {\n    'This',\n    'is',\n    'Working'\n}");

        $this->assertEquals('something', $def->getKey());

        $this->assertEquals([['This', 'is', 'Working']], $def->getData()->convertToNativeArray());
    }

    public function testMissingIndicator() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->serviceDefnitionNodeFromCode('foo: "Should generate an error"');
    }

    public function testInvalidIdentifier() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->serviceDefnitionNodeFromCode('= "nope": 42');
    }

    public function testNoIdentifier() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->serviceDefnitionNodeFromCode('- : 1');
    }

    public function testSomethingSrangeInsteadOfAssignment() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->serviceDefnitionNodeFromCode('= nah()');
    }
}
