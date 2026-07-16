<?php
namespace ClanCats\Container\Tests\ContainerParser\Parser;

use ClanCats\Container\Tests\TestCases\ParserTestCase;

use ClanCats\Container\ContainerParser\{
    Parser\ServiceDefinitionParser,
    Nodes\GenericDefinitionNode,
    Nodes\GenericMaterializationNode,
    Nodes\ServiceDefinitionNode,
    Nodes\ServiceMethodCallNode,
    Nodes\TypeParameterPlaceholderNode,
    Nodes\ArrayNode,
    Token as T
};

/**
 * Parser level tests that assert the raw node structure produced for generic
 * definitions and materializations (before interpretation / expansion).
 */
class GenericParsingTest extends ParserTestCase
{
    private function nodeFromCode(string $code)
    {
        return (new ServiceDefinitionParser($this->tokensFromCode($code)))->parse();
    }

    public function testDefinitionProducesGenericNode()
    {
        $node = $this->nodeFromCode("@repository<T>: App\\Repository(@db)\n  - setModel(T::class)");

        $this->assertInstanceOf(GenericDefinitionNode::class, $node);
        $this->assertEquals('repository', $node->getName());
        $this->assertEquals(['T'], $node->getTypeParameters());
        $this->assertFalse($node->isOverride());

        // the body is parsed once into a real service definition template
        $template = $node->getTemplate();
        $this->assertInstanceOf(ServiceDefinitionNode::class, $template);
        $this->assertEquals('App\\Repository', $template->getClassName());

        // the `T::class` usage inside the body became a type parameter placeholder
        $calls = $template->getConstructionActions();
        $this->assertCount(1, $calls);
        $this->assertInstanceOf(ServiceMethodCallNode::class, $calls[0]);
        $this->assertEquals('setModel', $calls[0]->getName());

        $args = $calls[0]->getArguments()->getArguments();
        $this->assertCount(1, $args);
        $this->assertInstanceOf(TypeParameterPlaceholderNode::class, $args[0]);
        $this->assertEquals('T', $args[0]->getName());
        $this->assertTrue($args[0]->isClassConstant());
    }

    public function testDefinitionWithMultipleTypeParameters()
    {
        $node = $this->nodeFromCode('@handler<TCommand, THandler>: THandler(@bus)');

        $this->assertInstanceOf(GenericDefinitionNode::class, $node);
        $this->assertEquals(['TCommand', 'THandler'], $node->getTypeParameters());
    }

    public function testOverrideDefinitionFlag()
    {
        $node = $this->nodeFromCode('override @repository<T>: App\\Repository(@db)');

        $this->assertInstanceOf(GenericDefinitionNode::class, $node);
        $this->assertTrue($node->isOverride());
    }

    public function testDefinitionBodyStopsAtNextStatement()
    {
        // two statements in one token stream: the generic must only capture its
        // own body, the trailing "@other" definition must not be swallowed.
        $parser = new ServiceDefinitionParser($this->tokensFromCode(
            "@repository<T>: App\\Repository(@db)\n  - setModel(T::class)\n@other: App\\Other"
        ));

        $node = $parser->parse();
        $this->assertInstanceOf(GenericDefinitionNode::class, $node);

        // the template must only contain this generics own body and must not
        // have swallowed the trailing "@other" definition
        $template = $node->getTemplate();
        $this->assertEquals('App\\Repository', $template->getClassName());
        $this->assertCount(1, $template->getConstructionActions());
        $this->assertEquals('setModel', $template->getConstructionActions()[0]->getName());
    }

    public function testDefinitionBodyKeepsLineBreaksInsideScopes()
    {
        // a multi line array argument contains line breaks that must NOT be
        // treated as the end of the definition body.
        $node = $this->nodeFromCode("@tagged<T>: App\\Tagged(T::class, {\n 'a',\n 'b'\n})");

        $this->assertInstanceOf(GenericDefinitionNode::class, $node);

        // the multi line array argument survived intact inside the template: the
        // line breaks inside the scope did not terminate the definition body
        $args = $node->getTemplate()->getArguments()->getArguments();
        $this->assertCount(2, $args);
        $this->assertInstanceOf(TypeParameterPlaceholderNode::class, $args[0]);
        $this->assertInstanceOf(ArrayNode::class, $args[1]);
        $this->assertCount(2, $args[1]->getElements());
    }

    public function testMaterializationProducesMaterializationNode()
    {
        $node = $this->nodeFromCode('@repository.user: @repository<App\\Model\\User>');

        $this->assertInstanceOf(GenericMaterializationNode::class, $node);
        $this->assertEquals('repository.user', $node->getName());
        $this->assertEquals('repository', $node->getGenericName());
        $this->assertFalse($node->isOverride());

        $arguments = $node->getTypeArguments();
        $this->assertCount(1, $arguments);
        $this->assertEquals('App\\Model\\User', $arguments[0]->getValue());
    }

    public function testMaterializationWithMixedArgumentKinds()
    {
        $node = $this->nodeFromCode('@cache.page: @cache<App\\Cache\\Apcu, 3600, @redis, :ttl>');

        $this->assertInstanceOf(GenericMaterializationNode::class, $node);

        $arguments = $node->getTypeArguments();
        $this->assertCount(4, $arguments);
        $this->assertEquals(T::TOKEN_IDENTIFIER, $arguments[0]->getType());
        $this->assertEquals(T::TOKEN_NUMBER, $arguments[1]->getType());
        $this->assertEquals(T::TOKEN_DEPENDENCY, $arguments[2]->getType());
        $this->assertEquals(T::TOKEN_PARAMETER, $arguments[3]->getType());
    }

    public function testOverrideMaterializationFlag()
    {
        $node = $this->nodeFromCode('override @repository.user: @repository<App\\Model\\User>');

        $this->assertInstanceOf(GenericMaterializationNode::class, $node);
        $this->assertTrue($node->isOverride());
    }

    public function testRegularServiceIsUnaffected()
    {
        // a plain service definition must still parse into a ServiceDefinitionNode
        $node = $this->nodeFromCode('@logger: App\\Log(@handler)');
        $this->assertInstanceOf(ServiceDefinitionNode::class, $node);
    }

    public function testBareIdentifierInNormalDefinitionStillThrows()
    {
        // placeholder recognition is scoped to generic bodies: a stray bare
        // identifier in a normal definition must still be a parse error.
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->nodeFromCode('@x: App\\Foo(bar)');
    }

    public function testUnknownIdentifierInGenericBodyThrows()
    {
        // inside a generic body only declared type parameters become
        // placeholders, an unknown identifier is still a parse error.
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->nodeFromCode('@x<T>: App\\Foo(unknown)');
    }

    public function testEmptyTypeParameterListThrows()
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->nodeFromCode('@repository<>: App\\Repository');
    }

    public function testMissingAssignAfterTypeParametersThrows()
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->nodeFromCode('@repository<T> App\\Repository');
    }

    public function testUnterminatedTypeArgumentListThrows()
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->nodeFromCode('@repository.user: @repository<App\\Model\\User');
    }

    public function testArrayTypeArgumentThrows()
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->nodeFromCode("@x: @repository<{'a', 'b'}>");
    }
}
