<?php
namespace ClanCats\Container\Tests\ContainerParser;

use ClanCats\Container\{
    Container,
    ContainerBuilder,
    ContainerNamespace,
    ServiceArguments
};
use ClanCats\Container\Exceptions\ContainerInterpreterException;
use ClanCats\Container\Tests\TestServices\{
    Engine, Producer, Car
};

use ClanCats\Container\ContainerParser\{
    ContainerLexer,
    ContainerInterpreter,
    Parser\ScopeParser,
    Nodes\ScopeNode
};

class GenericsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * A per test unique counter so generated container class names never clash
     * across the eval based end to end tests.
     *
     * @var int
     */
    private static int $classCounter = 0;

    /**
     * Parse the given container code into a fresh namespace.
     */
    private function namespaceFromCode(string $code) : ContainerNamespace
    {
        $ns = new ContainerNamespace();

        $lexer = new ContainerLexer($code, 'generics.ctn');
        $parser = new ScopeParser($lexer->tokens());

        $node = $parser->parse();
        $this->assertInstanceOf(ScopeNode::class, $node);

        $interpreter = new ContainerInterpreter($ns);
        $interpreter->handleScope($node);

        return $ns;
    }

    /**
     * Compile the given namespace into an actual, instantiated container so the
     * expanded generics can be exercised at runtime.
     */
    private function buildContainer(ContainerNamespace $ns) : Container
    {
        $className = 'GenericsBuiltContainer' . (self::$classCounter++);

        $builder = new ContainerBuilder($className);
        $builder->importNamespace($ns);

        eval('?>' . $builder->generate());

        $this->assertTrue(class_exists($className, false));

        return new $className();
    }

    /**
     * Convenience: parse code and build the resulting container in one step.
     */
    private function containerFromCode(string $code) : Container
    {
        return $this->buildContainer($this->namespaceFromCode($code));
    }

    /*
     * -----------------------------------------------------------------
     *  Registration
     * -----------------------------------------------------------------
     */

    public function testGenericIsRegisteredButNotAService()
    {
        $ns = $this->namespaceFromCode(<<<'CTN'
        @repository<T>: App\Repository(@db)
          - setModel(T::class)
        CTN);

        $this->assertTrue($ns->hasGeneric('repository'));
        $this->assertEquals(['repository'], array_keys($ns->getGenerics()));

        // the generic itself must never become a service or alias
        $this->assertFalse($ns->hasService('repository'));
        $this->assertEquals([], $ns->getServices());
        $this->assertEquals([], $ns->getAliases());
    }

    public function testDottedGenericName()
    {
        $ns = $this->namespaceFromCode(<<<'CTN'
        @engine.powered<TPower>: App\Engine
          - setPower(TPower)

        @engine.weak: @engine.powered<50>
        CTN);

        $this->assertTrue($ns->hasGeneric('engine.powered'));
        $this->assertTrue($ns->hasService('engine.weak'));
    }

    /*
     * -----------------------------------------------------------------
     *  Substitution semantics
     * -----------------------------------------------------------------
     */

    public function testClassNameTypeArgumentSubstitution()
    {
        $ns = $this->namespaceFromCode(<<<'CTN'
        @repository<T>: App\Repository(@db)
          - setModel(T::class)
          = tag: 'repository'

        @repository.user: @repository<App\Model\User>
        @repository.post: @repository<App\Model\Post>
        CTN);

        $user = $ns->getServices()['repository.user'];
        $this->assertEquals('App\\Repository', $user->getClassName());

        // the constructor dependency is kept verbatim
        $this->assertEquals([['db', ServiceArguments::DEPENDENCY]], $user->getArguments()->getAll());

        // the T::class placeholder resolved to the concrete class name string
        $calls = $user->getMethodCalls();
        $this->assertEquals('setModel', $calls[0][0]);
        $this->assertEquals([['\\App\\Model\\User', ServiceArguments::RAW]], $calls[0][1]->getAll());

        // meta data is expanded too
        $this->assertEquals(['tag' => [['repository']]], $user->getMetaData());

        // the second materialization gets its own class
        $post = $ns->getServices()['repository.post'];
        $this->assertEquals([['\\App\\Model\\Post', ServiceArguments::RAW]], $post->getMethodCalls()[0][1]->getAll());
    }

    public function testBareIdentifierTypeArgumentAsClassName()
    {
        $ns = $this->namespaceFromCode(<<<'CTN'
        @handler<TCommand, THandler>: THandler(@bus)
          = command: TCommand::class

        @handler.register: @handler<App\Command\Register, App\Handler\RegisterHandler>
        CTN);

        $service = $ns->getServices()['handler.register'];

        // the bare THandler placeholder became the service class name
        $this->assertEquals('App\\Handler\\RegisterHandler', $service->getClassName());
        $this->assertEquals([['bus', ServiceArguments::DEPENDENCY]], $service->getArguments()->getAll());
        $this->assertEquals(['command' => [['\\App\\Command\\Register']]], $service->getMetaData());
    }

    public function testTypeParameterAsConstructorArgument()
    {
        // a bare placeholder used directly as a constructor argument only parses
        // after substitution, proving substitution happens before parsing.
        $ns = $this->namespaceFromCode(<<<'CTN'
        @wrapper<TInner>: App\Wrapper(TInner, 'static')
        @wrapper.logged: @wrapper<@logger>
        CTN);

        $service = $ns->getServices()['wrapper.logged'];
        $this->assertEquals([
            ['logger', ServiceArguments::DEPENDENCY],
            ['static', ServiceArguments::RAW],
        ], $service->getArguments()->getAll());
    }

    public function testTypeParameterUsedMultipleTimes()
    {
        $ns = $this->namespaceFromCode(<<<'CTN'
        @twin<T>: App\Twin(T::class)
          - first(T::class)
          - second(T::class)

        @twin.user: @twin<App\User>
        CTN);

        $service = $ns->getServices()['twin.user'];

        $this->assertEquals([['\\App\\User', ServiceArguments::RAW]], $service->getArguments()->getAll());

        foreach ($service->getMethodCalls() as $call) {
            $this->assertEquals([['\\App\\User', ServiceArguments::RAW]], $call[1]->getAll());
        }
    }

    public function testServiceAndParameterAndScalarTypeArguments()
    {
        $ns = $this->namespaceFromCode(<<<'CTN'
        :page.cache_ttl: 300

        @cache<TDriver, TTtl>: TDriver(@redis)
          - setTtl(TTtl)

        @cache.session: @cache<App\Cache\RedisDriver, 3600>
        @cache.page: @cache<App\Cache\ApcuDriver, :page.cache_ttl>
        @cache.tagged: @cache<App\Cache\RedisDriver, @default_ttl>
        CTN);

        // scalar type argument
        $session = $ns->getServices()['cache.session'];
        $this->assertEquals('App\\Cache\\RedisDriver', $session->getClassName());
        $this->assertEquals([[3600, ServiceArguments::RAW]], $session->getMethodCalls()[0][1]->getAll());

        // parameter reference type argument
        $page = $ns->getServices()['cache.page'];
        $this->assertEquals('App\\Cache\\ApcuDriver', $page->getClassName());
        $this->assertEquals([['page.cache_ttl', ServiceArguments::PARAMETER]], $page->getMethodCalls()[0][1]->getAll());

        // service reference type argument
        $tagged = $ns->getServices()['cache.tagged'];
        $this->assertEquals([['default_ttl', ServiceArguments::DEPENDENCY]], $tagged->getMethodCalls()[0][1]->getAll());
    }

    public function testStringBoolAndNullScalarTypeArguments()
    {
        $ns = $this->namespaceFromCode(<<<'CTN'
        @flagged<TName, TFlag, TFallback>: App\Flag(TName)
          - toggle(TFlag)
          - fallback(TFallback)

        @flagged.one: @flagged<'hello', true, null>
        CTN);

        $service = $ns->getServices()['flagged.one'];

        $this->assertEquals([['hello', ServiceArguments::RAW]], $service->getArguments()->getAll());

        $calls = $service->getMethodCalls();
        $this->assertSame([[true, ServiceArguments::RAW]], $calls[0][1]->getAll());
        $this->assertSame([[null, ServiceArguments::RAW]], $calls[1][1]->getAll());
    }

    public function testArraysInGenericBodyArePreserved()
    {
        // a multi line array argument in the template must survive expansion
        $ns = $this->namespaceFromCode(<<<'CTN'
        @tagged<T>: App\Tagged(T::class, {
            'primary',
            'secondary'
        })
          = tags: 'a', 'b'

        @tagged.user: @tagged<App\User>
        CTN);

        $service = $ns->getServices()['tagged.user'];

        // the multi line array constructor argument survived expansion intact
        $this->assertEquals([
            ['\\App\\User', ServiceArguments::RAW],
            [['primary', 'secondary'], ServiceArguments::RAW],
        ], $service->getArguments()->getAll());

        $this->assertEquals(['tags' => [['a', 'b']]], $service->getMetaData());
    }

    public function testGenericWithOnlyAConstructor()
    {
        $ns = $this->namespaceFromCode(<<<'CTN'
        @simple<T>: T
        @simple.a: @simple<App\A>
        CTN);

        $this->assertEquals('App\\A', $ns->getServices()['simple.a']->getClassName());
    }

    public function testWhitespaceAndNewlinesInsideAngleBrackets()
    {
        $ns = $this->namespaceFromCode(<<<'CTN'
        @pair<A, B>: App\Pair(A::class, B::class)

        @pair.one: @pair< App\First , App\Second >
        CTN);

        $service = $ns->getServices()['pair.one'];
        $this->assertEquals([
            ['\\App\\First', ServiceArguments::RAW],
            ['\\App\\Second', ServiceArguments::RAW],
        ], $service->getArguments()->getAll());
    }

    /*
     * -----------------------------------------------------------------
     *  Isolation between materializations
     * -----------------------------------------------------------------
     */

    public function testMaterializationsAreIndependent()
    {
        // each materialization is re-parsed independently, so mutating one
        // must never bleed into another built from the same generic.
        $ns = $this->namespaceFromCode(<<<'CTN'
        @repository<T>: App\Repository
          - setModel(T::class)

        @repository.a: @repository<App\A>
        @repository.b: @repository<App\B>
        CTN);

        $a = $ns->getServices()['repository.a'];
        $b = $ns->getServices()['repository.b'];

        $this->assertNotSame($a, $b);
        $this->assertEquals([['\\App\\A', ServiceArguments::RAW]], $a->getMethodCalls()[0][1]->getAll());
        $this->assertEquals([['\\App\\B', ServiceArguments::RAW]], $b->getMethodCalls()[0][1]->getAll());
    }

    /*
     * -----------------------------------------------------------------
     *  Override
     * -----------------------------------------------------------------
     */

    public function testOverrideGenericDefinition()
    {
        $ns = $this->namespaceFromCode(<<<'CTN'
        @repository<T>: App\Repository(@db)
          - setModel(T::class)

        override @repository<T>: App\ReadOnlyRepository(@db)
          - setModel(T::class)

        @repository.user: @repository<App\Model\User>
        CTN);

        $this->assertEquals('App\\ReadOnlyRepository', $ns->getServices()['repository.user']->getClassName());
    }

    public function testOverrideMaterializedService()
    {
        $ns = $this->namespaceFromCode(<<<'CTN'
        @repository<T>: App\Repository(@db)
          - setModel(T::class)

        @repository.user: @repository<App\Model\User>
        override @repository.user: @repository<App\Model\Admin>
        CTN);

        // the override re-materialized the service with a different type argument
        $service = $ns->getServices()['repository.user'];
        $this->assertEquals([['\\App\\Model\\Admin', ServiceArguments::RAW]], $service->getMethodCalls()[0][1]->getAll());
    }

    public function testMaterializedServiceCanBeUpdated()
    {
        // a materialized service behaves like any other and can be extended
        // afterwards through a regular update block.
        $ns = $this->namespaceFromCode(<<<'CTN'
        @engine.powered<TPower>: App\Engine
          - setPower(TPower)

        @engine.custom: @engine.powered<100>

        @engine.custom
          - setName('custom')
        CTN);

        $service = $ns->getServices()['engine.custom'];
        $calls = $service->getMethodCalls();

        // the method call from the generic body plus the one added by the update
        $this->assertCount(2, $calls);
        $this->assertEquals('setPower', $calls[0][0]);
        $this->assertEquals('setName', $calls[1][0]);
    }

    /*
     * -----------------------------------------------------------------
     *  Errors
     * -----------------------------------------------------------------
     */

    public function testUnknownGenericThrows()
    {
        $this->expectException(ContainerInterpreterException::class);
        $this->expectExceptionMessage('unknown generic "nope"');
        $this->namespaceFromCode('@x: @nope<App\\Foo>');
    }

    public function testArityMismatchTooFewThrows()
    {
        $this->expectException(ContainerInterpreterException::class);
        $this->expectExceptionMessage('expects 2 type argument(s) but 1');
        $this->namespaceFromCode(<<<'CTN'
        @pair<A, B>: App\Pair(A::class, B::class)
        @x: @pair<App\Only>
        CTN);
    }

    public function testArityMismatchTooManyThrows()
    {
        $this->expectException(ContainerInterpreterException::class);
        $this->expectExceptionMessage('expects 1 type argument(s) but 2');
        $this->namespaceFromCode(<<<'CTN'
        @single<A>: App\Single(A::class)
        @x: @single<App\One, App\Two>
        CTN);
    }

    public function testRedefiningGenericWithoutOverrideThrows()
    {
        $this->expectException(ContainerInterpreterException::class);
        $this->namespaceFromCode(<<<'CTN'
        @repository<T>: App\Repository(@db)
        @repository<T>: App\Other(@db)
        CTN);
    }

    public function testGenericWithoutTypeParameterThrows()
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->namespaceFromCode('@repository<>: App\\Repository(@db)');
    }

    public function testDuplicateTypeParameterThrows()
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerParserException::class);
        $this->expectExceptionMessage('Duplicate generic type parameter "T"');
        $this->namespaceFromCode('@pair<T, T>: App\\Pair(T::class)');
    }

    /*
     * -----------------------------------------------------------------
     *  End to end: build a real container and resolve services
     * -----------------------------------------------------------------
     */

    public function testEndToEndScalarSubstitutionResolves()
    {
        $container = $this->containerFromCode(<<<'CTN'
        @engine.powered<TPower>: ClanCats\Container\Tests\TestServices\Engine
          - setPower(TPower)

        @engine.weak: @engine.powered<50>
        @engine.strong: @engine.powered<9000>
        CTN);

        $weak = $container->get('engine.weak');
        $strong = $container->get('engine.strong');

        $this->assertInstanceOf(Engine::class, $weak);
        $this->assertInstanceOf(Engine::class, $strong);

        // the scalar type argument was wired all the way through to the object
        $this->assertSame(50, $weak->power);
        $this->assertSame(9000, $strong->power);

        // distinct materializations resolve to distinct instances
        $this->assertNotSame($weak, $strong);
    }

    public function testEndToEndStringConstructorArgumentResolves()
    {
        $container = $this->containerFromCode(<<<'CTN'
        @named<TName>: ClanCats\Container\Tests\TestServices\Producer(TName)

        @producer.acme: @named<'ACME Corp'>
        CTN);

        $producer = $container->get('producer.acme');

        $this->assertInstanceOf(Producer::class, $producer);
        $this->assertSame('ACME Corp', $producer->name);
    }

    public function testEndToEndServiceReferenceTypeArgumentResolves()
    {
        // a generic whose type argument is another service reference; the
        // materialized car must be wired with the exact engine instance.
        $container = $this->containerFromCode(<<<'CTN'
        @engine: ClanCats\Container\Tests\TestServices\Engine
        @producer: ClanCats\Container\Tests\TestServices\Producer('built')

        @car<TEngine>: ClanCats\Container\Tests\TestServices\Car(TEngine, @producer)

        @car.main: @car<@engine>
        CTN);

        $car = $container->get('car.main');

        $this->assertInstanceOf(Car::class, $car);
        $this->assertInstanceOf(Engine::class, $car->engine);
        $this->assertInstanceOf(Producer::class, $car->producer);

        // the injected engine is the very same shared instance
        $this->assertSame($container->get('engine'), $car->engine);
        $this->assertSame('built', $car->producer->name);
    }

    public function testCompiledContainerContainsNoGenericArtifacts()
    {
        $ns = $this->namespaceFromCode(<<<'CTN'
        @engine.powered<TPower>: ClanCats\Container\Tests\TestServices\Engine
          - setPower(TPower)

        @engine.weak: @engine.powered<50>
        CTN);

        $builder = new ContainerBuilder('CompiledArtifactContainer');
        $builder->importNamespace($ns);
        $code = $builder->generate();

        // generics are a pure compile time construct: nothing leaks into the
        // generated PHP, only the expanded concrete service remains.
        $this->assertStringNotContainsStringIgnoringCase('generic', $code);
        $this->assertStringContainsString('engine.weak', $code);
    }

    /*
     * -----------------------------------------------------------------
     *  Cross file: generics defined in an imported file
     * -----------------------------------------------------------------
     */

    public function testGenericsResolveAcrossImports()
    {
        $ns = new ContainerNamespace();
        $ns->importDirectory(__DIR__ . '/../ctn/generics');
        $ns->parse(__DIR__ . '/../ctn/generics/app.ctn');

        // the generic defined in "templates.ctn" is registered
        $this->assertTrue($ns->hasGeneric('engine.powered'));
        $this->assertTrue($ns->hasGeneric('named.producer'));

        // and materialized into concrete services in "app.ctn"
        $this->assertTrue($ns->hasService('engine.weak'));
        $this->assertTrue($ns->hasService('engine.strong'));
        $this->assertTrue($ns->hasService('producer.acme'));

        $container = $this->buildContainer($ns);

        $this->assertSame(50, $container->get('engine.weak')->power);
        $this->assertSame(9000, $container->get('engine.strong')->power);
        $this->assertSame('ACME Corp', $container->get('producer.acme')->name);
    }
}
