<?php
namespace ClanCats\Container\Tests\ContainerParser;

use ClanCats\Container\ServiceDefinition;
use ClanCats\Container\ContainerNamespace;
use ClanCats\Container\ServiceArguments;

use ClanCats\Container\ContainerParser\{
    ContainerInterpreter,

    // nodes
    Nodes\ArrayNode,
    Nodes\ScopeNode,
    Nodes\ScopeImportNode,
    Nodes\ParameterDefinitionNode,
    Nodes\ServiceDefinitionNode,
    Nodes\ValueNode,
    Nodes\ArgumentArrayNode,
    Nodes\ServiceReferenceNode,
    Nodes\ParameterReferenceNode,
    Nodes\ServiceMethodCallNode
};

class ContainerInterpreterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Little helper to assert the scope interpreter
     */
    public function assertContainerNamespaceScopeCallback(array $nodes, callable $callback) 
    {
        $ns = new ContainerNamespace();
        $interpreter = new ContainerInterpreter($ns);

        $scope = new ScopeNode();
        foreach($nodes as $node) 
        {
            $scope->addNode($node);
        }

        $interpreter->handleScope($scope);

        call_user_func_array($callback, [&$ns]);
    }

	public function testConstruct()
    {
    	$ns = new ContainerNamespace();
    	$interpreter = new ContainerInterpreter($ns);

    	$this->assertInstanceOf(ContainerInterpreter::class, $interpreter);
    }

    public function testHandleScopeWithImport()
    {
        // mock the namespace
        $ns = $this->createMock(ContainerNamespace::class);

        $ns->expects($this->once())
            ->method('setParameter');

        $interpreter = new ContainerInterpreter($ns);

        // and return some simple code
        $ns->method('getCode')
            ->willReturn(':foo: "bar"');

        $scope = new ScopeNode();
        $import = new ScopeImportNode();
        $import->setPath('doesnt/matter');

        $scope->addNode($import);

        $interpreter->handleScope($scope);
    }

    public function testHandleScopeWithParameterDefinition()
    {
        $artist = new ParameterDefinitionNode('artist', new ValueNode('Juse Ju', ValueNode::TYPE_STRING));

        $this->assertContainerNamespaceScopeCallback([$artist], function($ns) 
        {
            $this->assertEquals(['artist' => 'Juse Ju'], $ns->getParameters());
        });
    }

    public function testHandleScopeWithServiceDefinition()
    {
        $service = new ServiceDefinitionNode('logger', 'Log');

        $this->assertContainerNamespaceScopeCallback([$service], function($ns)  use($service)
        {
            $services = $ns->getServices();

            $this->assertCount(1, $services);
            $this->assertInstanceOf(ServiceDefinition::class, $services['logger']);
            $this->assertEquals('Log', $services['logger']->getClassName());
        });
    }

    public function testHandleParameterDefinition()
    {
    	$ns = new ContainerNamespace();
    	$interpreter = new ContainerInterpreter($ns);

    	$artist = new ParameterDefinitionNode('artist', new ValueNode('Juse Ju', ValueNode::TYPE_STRING));
    	$song = new ParameterDefinitionNode('song', new ValueNode('Übertreib nich deine Rolle', ValueNode::TYPE_STRING));
    	$song2 = new ParameterDefinitionNode('song', new ValueNode('DAYONE', ValueNode::TYPE_STRING));
    	$song2->setIsOverride(true);

        $array = new ArrayNode();
        $array->push(new ValueNode('Edgar Wasser', ValueNode::TYPE_STRING));
        $array->push(new ValueNode('Fatoni', ValueNode::TYPE_STRING));
        $features = new ParameterDefinitionNode('features', $array);

    	$interpreter->handleParameterDefinition($artist);
    	$interpreter->handleParameterDefinition($song);
    	$interpreter->handleParameterDefinition($song2);
        $interpreter->handleParameterDefinition($features);

    	$this->assertEquals([
    		'artist' => 'Juse Ju',
    		'song' => 'DAYONE',
            'features' => ['Edgar Wasser', 'Fatoni']
    	], $ns->getParameters());
    }

    public function testHandleAliasDefinition()
    {
        $ns = new ContainerNamespace();
        $interpreter = new ContainerInterpreter($ns);

        $aliasdef = new ServiceDefinitionNode('foo');
        $aliasdef->setIsAlias(true);
        $aliasdef->setAliasTarget(new ServiceReferenceNode('bar'));

        $interpreter->handleServiceDefinition($aliasdef);

        $this->assertEquals([
            'foo' => 'bar'
        ], $ns->getAliases());
    }

    public function testHandleParameterDefinitionWithoutOverride() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerInterpreterException::class);
        	$ns = new ContainerNamespace();
    	$interpreter = new ContainerInterpreter($ns);

    	$testA = new ParameterDefinitionNode('test', new ValueNode('foo', ValueNode::TYPE_STRING));
    	$testB = new ParameterDefinitionNode('test', new ValueNode('bar', ValueNode::TYPE_STRING));

    	$interpreter->handleParameterDefinition($testA);
    	$interpreter->handleParameterDefinition($testB);
    }

    public function testHandleScopeImport()
    {
        // mock the namespace
        $ns = $this->createMock(ContainerNamespace::class);

        // and return some simple code
        $ns->method('getCode')
            ->willReturn(':foo: "bar"');

        $ns->expects($this->exactly(1))
            ->method('setParameter')
            ->withConsecutive(
                [$this->equalTo('foo'), 'bar']
            );

        $interpreter = new ContainerInterpreter($ns);

        $import = new ScopeImportNode();
        $import->setPath('acme/test');

        $interpreter->handleScopeImport($import);
    }

    public function testHandleScopeImportEmptyPath() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerInterpreterException::class);
        $ns = new ContainerNamespace();
        $interpreter = new ContainerInterpreter($ns);

        $import = new ScopeImportNode();

        $interpreter->handleScopeImport($import);
    }

    public function testHandleServiceDefinition()
    {
        $ns = new ContainerNamespace();
        $interpreter = new ContainerInterpreter($ns);

        $logger = new ServiceDefinitionNode('logger', 'Log');
        $adapter1 = new ServiceDefinitionNode('log.adapter', 'Log\\FileAdapter');
        $adapter2 = new ServiceDefinitionNode('log.adapter', 'Log\\DBAdapter');
        $adapter2->setIsOverride(true);

        $interpreter->handleServiceDefinition($logger);
        $interpreter->handleServiceDefinition($adapter1);
        $interpreter->handleServiceDefinition($adapter2);

        $services = $ns->getServices();

        $this->assertEquals('Log', $services['logger']->getClassName());
        $this->assertEquals('Log\\DBAdapter', $services['log.adapter']->getClassName());
    }

    public function testHandleServiceDefinitionWithArguments()
    {
        $ns = new ContainerNamespace();
        $interpreter = new ContainerInterpreter($ns);

        $logger = new ServiceDefinitionNode('logger', 'Log');
        $adapter = new ServiceDefinitionNode('log.adapter', 'Log\\FileAdapter');    

        // prepare a sample array
        $array = new ArrayNode();
        $array->push(new ValueNode('Foo', ValueNode::TYPE_STRING));
        $array->push(new ValueNode('Bar', ValueNode::TYPE_STRING));

        $arguments = new ArgumentArrayNode();
        $arguments->addArgument(new ServiceReferenceNode('log.adapter'));
        $arguments->addArgument(new ParameterReferenceNode('log.name'));
        $arguments->addArgument(new ValueNode(true, ValueNode::TYPE_BOOL_TRUE));
        $arguments->addArgument($array);

        $logger->setArguments($arguments);

        $interpreter->handleServiceDefinition($logger);
        $interpreter->handleServiceDefinition($adapter);

        $services = $ns->getServices();

        $this->assertCount(2, $services); 

        $loggerArguments = $services['logger']->getArguments()->getAll();

        $this->assertCount(4, $loggerArguments);

        // check dependency
        $this->assertEquals('log.adapter', $loggerArguments[0][0]);
        $this->assertEquals(ServiceArguments::DEPENDENCY, $loggerArguments[0][1]);

        // check parameter
        $this->assertEquals('log.name', $loggerArguments[1][0]);
        $this->assertEquals(ServiceArguments::PARAMETER, $loggerArguments[1][1]);

        // check value
        $this->assertEquals(true, $loggerArguments[2][0]);
        $this->assertEquals(ServiceArguments::RAW, $loggerArguments[2][1]);

        // check array
        $this->assertEquals(['Foo', 'Bar'], $loggerArguments[3][0]);
        $this->assertEquals(ServiceArguments::RAW, $loggerArguments[3][1]);
    }

    public function testHandleServiceDefinitionWithoutOverride() 
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerInterpreterException::class);
        $ns = new ContainerNamespace();
        $interpreter = new ContainerInterpreter($ns);

        $testA = new ServiceDefinitionNode('a', 'A');
        $testB = new ServiceDefinitionNode('a', 'B');

        $interpreter->handleServiceDefinition($testA);
        $interpreter->handleServiceDefinition($testB);
    }

    public function testHandleServiceDefinitionConstructionArguments()
    {
        $ns = new ContainerNamespace();
        $interpreter = new ContainerInterpreter($ns);

        $logger = new ServiceDefinitionNode('logger', 'Log');
        $logger->addConstructionAction(new ServiceMethodCallNode('wake'));
        $logger->addConstructionAction(new ServiceMethodCallNode('sleep'));

        $interpreter->handleServiceDefinition($logger);

        $services = $ns->getServices();

        $calls = $services['logger']->getMethodCalls();

        $this->assertCount(2, $calls);
    }

    public function testHandleUpdateServiceDefinitionConstructionArguments()
    {
        $ns = new ContainerNamespace();
        $interpreter = new ContainerInterpreter($ns);

        // first definition
        $logger = new ServiceDefinitionNode('logger', 'Log');
        $logger->addConstructionAction(new ServiceMethodCallNode('wake'));
        $interpreter->handleServiceDefinition($logger);

        // update
        $loggerUpdate = new ServiceDefinitionNode('logger');
        $loggerUpdate->setIsUpdate(true);
        $loggerUpdate->addConstructionAction(new ServiceMethodCallNode('sleep'));
        $interpreter->handleServiceDefinition($loggerUpdate);

        $services = $ns->getServices();

        $calls = $services['logger']->getMethodCalls();

        $this->assertCount(2, $calls);
        $this->assertEquals(['wake', 'sleep'], array_map(function($v) { return $v[0]; }, $calls));
    }

    public function testHandleUpdateServiceDefinitionNotInUniverse()
    {
        $this->expectException(\ClanCats\Container\Exceptions\ContainerInterpreterException::class);
        
        $ns = new ContainerNamespace();
        $interpreter = new ContainerInterpreter($ns);
        $loggerUpdate = new ServiceDefinitionNode('logger');
        $loggerUpdate->setIsUpdate(true);
        $loggerUpdate->addConstructionAction(new ServiceMethodCallNode('sleep'));
        $interpreter->handleServiceDefinition($loggerUpdate);
    }
}
