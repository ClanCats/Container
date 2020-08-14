<?php
namespace ClanCats\Container\Tests\ContainerParser\Parser;

use ClanCats\Container\Tests\TestCases\ParserTestCase;

use ClanCats\Container\ContainerParser\{
    Parser\ServiceDefinitionParser,
    Nodes\ServiceDefinitionNode,
    Nodes\ValueNode,
    Token as T
};

class ServiceDefinitionParserTest extends ParserTestCase
{
    protected function serviceDefnitionParserFromCode(string $code) : ServiceDefinitionParser 
    {
        return $this->parserFromCode(ServiceDefinitionParser::class, $code);
    }

    protected function serviceDefnitionNodeFromCode(string $code) : ServiceDefinitionNode 
    {
        return $this->serviceDefnitionParserFromCode($code)->parse();
    }

	public function testConstruct()
    {
    	$this->assertInstanceOf(ServiceDefinitionParser::class, $this->serviceDefnitionParserFromCode(''));
    }
    
    public function testSimpleAssign()
    {
        $def = $this->serviceDefnitionNodeFromCode('@logger: Acme\\Log()');

        $this->assertEquals('logger', $def->getName());
        $this->assertEquals('Acme\\Log', $def->getClassName());

        // should have arguments but they are empty
        $this->assertTrue($def->hasArguments());
        $this->assertCount(0, $def->getArguments()->getArguments());
    }

    public function testWithNoArgument()
    {
        $def = $this->serviceDefnitionNodeFromCode('@logger: Acme\\Log');

        $this->assertEquals('logger', $def->getName());
        $this->assertEquals('Acme\\Log', $def->getClassName());
        $this->assertFalse($def->hasArguments());
    }

    public function testAssignWithArguments()
    {
        $def = $this->serviceDefnitionNodeFromCode('@logger: Acme\\Log(@log.handler, :path)');
        $this->assertTrue($def->hasArguments());

        $arguments = $def->getArguments()->getArguments();

        $this->assertCount(2, $arguments);
        $this->assertEquals('log.handler', $arguments[0]->getName());
        $this->assertEquals('path', $arguments[1]->getName());
    }

    public function testOverride()
    {
        $def = $this->serviceDefnitionNodeFromCode('@logger: Acme\\Log');
        $this->assertFalse($def->isOverride());

        $def = $this->serviceDefnitionNodeFromCode('override @logger: Acme\\Log');
        $this->assertTrue($def->isOverride());
    }

    public function testAlias()
    {
        $def = $this->serviceDefnitionNodeFromCode('@logger: Acme\\Log');
        $this->assertFalse($def->isAlias());

        $this->assertEquals(null, $def->getAliasTarget());

        $def = $this->serviceDefnitionNodeFromCode('@logger: @logger.default');
        $this->assertTrue($def->isAlias());

        $this->assertEquals('logger.default', $def->getAliasTarget()->getName());
    }

    public function testInvalidAliasDefinitionArguments() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->serviceDefnitionNodeFromCode('logger: @logger.default("foo")');
    }

    public function testInvalidAliasDefinitionFunctionCall() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->serviceDefnitionNodeFromCode("logger: @logger.default\n- setSomething()");
    }

    public function testInvalidAliasDefinitionMetaAssign() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->serviceDefnitionNodeFromCode("logger: @logger.default\n= data");
    }

    public function testMethodCalls()
    {
        $def = $this->serviceDefnitionNodeFromCode("@logger: Acme\\Log\n- setName('app')");

        $actions = $def->getConstructionActions();

        $this->assertCount(1, $actions);

        $this->assertEquals('setName', $actions[0]->getName());
    }

    public function testMultipleMethodCalls()
    {
        $def = $this->serviceDefnitionNodeFromCode("@logger: Acme\\Log\n- setName('app')\n- setLevel(1)");

        $actions = $def->getConstructionActions();

        $this->assertCount(2, $actions);

        $this->assertEquals('setName', $actions[0]->getName());
        $this->assertEquals('setLevel', $actions[1]->getName());
    }

    public function testMultipleMethodCallsServiceUpdate()
    {
        $def = $this->serviceDefnitionNodeFromCode("@logger\n- setName('app')\n- setLevel(1)");

        $actions = $def->getConstructionActions();

        $this->assertCount(2, $actions);
        $this->assertTrue($def->isUpdate());
        $this->assertEquals('setName', $actions[0]->getName());
        $this->assertEquals('setLevel', $actions[1]->getName());
    }

    public function testMetaDataAssignments()
    {
        $def = $this->serviceDefnitionNodeFromCode("@logger: Acme\\Log\n= listen: 'kernel.exception'");

        $meta = $def->getMetaDataAssignemnts();

        $this->assertCount(1, $meta);

        $this->assertEquals('listen', $meta[0]->getKey());
    }

    public function testMetaDataAssignmentsServiceUpdate()
    {
        $def = $this->serviceDefnitionNodeFromCode("@logger\n= listen: 'kernel.exception'");

        $meta = $def->getMetaDataAssignemnts();

        $this->assertCount(1, $meta);
        $this->assertTrue($def->isUpdate());
        $this->assertEquals('listen', $meta[0]->getKey());
    }

    public function testMetaDataAssignmentsMultiple()
    {
        $def = $this->serviceDefnitionNodeFromCode("@logger: Acme\\Log\n= listen: 'kernel.exception', method: 'handle'\n= tag: 'logger'");

        $meta = $def->getMetaDataAssignemnts();
        
        $this->assertCount(2, $meta);

        $this->assertEquals('listen', $meta[0]->getKey());
        $this->assertEquals('tag', $meta[1]->getKey());

        $this->assertEquals(['kernel.exception', 'method' => 'handle'], $meta[0]->getData()->convertToNativeArray());
        $this->assertEquals(['logger'], $meta[1]->getData()->convertToNativeArray());
    }

    public function testMetaDataAssignmentsMultiline()
    {
        $def = $this->serviceDefnitionNodeFromCode("@logger: Acme\\Log\n= listen: 'kernel.exception', {'A', 'B'}\n= tag: {'logger', 'event'}");

        $meta = $def->getMetaDataAssignemnts();
        
        $this->assertCount(2, $meta);

        $this->assertEquals('listen', $meta[0]->getKey());
        $this->assertEquals('tag', $meta[1]->getKey());

        $this->assertEquals(['kernel.exception', ['A', 'B']], $meta[0]->getData()->convertToNativeArray());
        $this->assertEquals([['logger', 'event']], $meta[1]->getData()->convertToNativeArray());
    }

    public function testMissingDependencyIndicator() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->serviceDefnitionNodeFromCode('logger: Acme\\Log');
    }

    public function testServiceIsUpdate() 
    {
        $def = $this->serviceDefnitionNodeFromCode('@logger');
        $this->assertTrue($def->isUpdate());

        $def = $this->serviceDefnitionNodeFromCode('@logger: Acme\\Log');
        $this->assertFalse($def->isUpdate());
    }

    public function testWrongAssignment() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->serviceDefnitionNodeFromCode('@logger: :foo');
    }
}
