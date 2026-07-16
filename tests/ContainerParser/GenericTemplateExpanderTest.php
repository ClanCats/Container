<?php
namespace ClanCats\Container\Tests\ContainerParser;

use ClanCats\Container\Tests\TestCases\ParserTestCase;

use ClanCats\Container\ContainerParser\{
    GenericTemplateExpander,
    Parser\ServiceDefinitionParser,
    Nodes\GenericDefinitionNode,
    Nodes\GenericMaterializationNode,
    Nodes\ServiceDefinitionNode,
    Nodes\ServiceReferenceNode,
    Nodes\ParameterReferenceNode,
    Nodes\ValueNode
};

/**
 * Node level tests for the generic template expander in isolation: it must turn
 * a parsed generic template plus a materialization into a concrete
 * ServiceDefinitionNode purely through node to node substitution.
 */
class GenericTemplateExpanderTest extends ParserTestCase
{
    private function genericFromCode(string $code) : GenericDefinitionNode
    {
        $node = (new ServiceDefinitionParser($this->tokensFromCode($code)))->parse();
        $this->assertInstanceOf(GenericDefinitionNode::class, $node);

        return $node;
    }

    private function materializationFromCode(string $code) : GenericMaterializationNode
    {
        $node = (new ServiceDefinitionParser($this->tokensFromCode($code)))->parse();
        $this->assertInstanceOf(GenericMaterializationNode::class, $node);

        return $node;
    }

    private function expand(string $definition, string $materialization) : ServiceDefinitionNode
    {
        return (new GenericTemplateExpander())->expand(
            $this->genericFromCode($definition),
            $this->materializationFromCode($materialization)
        );
    }

    public function testNameAndOverrideComeFromMaterialization()
    {
        $service = $this->expand(
            '@repo<T>: App\\Repository',
            'override @repo.user: @repo<App\\User>'
        );

        $this->assertEquals('repo.user', $service->getName());
        $this->assertTrue($service->isOverride());
    }

    public function testClassConstantPlaceholderKeepsBackslashWrapping()
    {
        $service = $this->expand(
            "@repo<T>: App\\Repository\n  - setModel(T::class)",
            '@repo.user: @repo<App\\Model\\User>'
        );

        $args = $service->getConstructionActions()[0]->getArguments()->getArguments();
        $this->assertInstanceOf(ValueNode::class, $args[0]);
        $this->assertEquals(ValueNode::TYPE_CLASS_NAME, $args[0]->getType());
        $this->assertEquals('\\App\\Model\\User', $args[0]->getRawValue());
    }

    public function testBareIdentifierResolvesAsClassName()
    {
        $service = $this->expand(
            '@handler<TObj>: TObj',
            '@handler.a: @handler<App\\Handler\\A>'
        );

        $this->assertFalse($service->hasClassNamePlaceholder());
        $this->assertEquals('App\\Handler\\A', $service->getClassName());
    }

    public function testServiceReferenceTypeArgument()
    {
        $service = $this->expand(
            '@wrap<T>: App\\Wrap(T)',
            '@wrap.log: @wrap<@logger>'
        );

        $arg = $service->getArguments()->getArguments()[0];
        $this->assertInstanceOf(ServiceReferenceNode::class, $arg);
        $this->assertEquals('logger', $arg->getName());
    }

    public function testParameterReferenceTypeArgument()
    {
        $service = $this->expand(
            '@cache<T>: App\\Cache(T)',
            '@cache.p: @cache<:ttl>'
        );

        $arg = $service->getArguments()->getArguments()[0];
        $this->assertInstanceOf(ParameterReferenceNode::class, $arg);
        $this->assertEquals('ttl', $arg->getName());
    }

    public function testScalarTypeArgument()
    {
        $service = $this->expand(
            '@power<T>: App\\Engine(T)',
            '@power.weak: @power<50>'
        );

        $arg = $service->getArguments()->getArguments()[0];
        $this->assertInstanceOf(ValueNode::class, $arg);
        $this->assertSame(50, $arg->getRawValue());
    }

    public function testExpansionsAreIndependent()
    {
        $generic = $this->genericFromCode("@repo<T>: App\\Repository\n  - setModel(T::class)");
        $expander = new GenericTemplateExpander();

        $a = $expander->expand($generic, $this->materializationFromCode('@a: @repo<App\\A>'));
        $b = $expander->expand($generic, $this->materializationFromCode('@b: @repo<App\\B>'));

        // distinct node trees, no shared references, and the untouched template
        $this->assertNotSame($a, $b);
        $this->assertNotSame(
            $a->getConstructionActions()[0]->getArguments(),
            $b->getConstructionActions()[0]->getArguments()
        );

        $this->assertEquals('\\App\\A', $a->getConstructionActions()[0]->getArguments()->getArguments()[0]->getRawValue());
        $this->assertEquals('\\App\\B', $b->getConstructionActions()[0]->getArguments()->getArguments()[0]->getRawValue());

        // the template still holds a placeholder, it was never mutated
        $templateArg = $generic->getTemplate()->getConstructionActions()[0]->getArguments()->getArguments()[0];
        $this->assertInstanceOf(\ClanCats\Container\ContainerParser\Nodes\TypeParameterPlaceholderNode::class, $templateArg);
    }
}
