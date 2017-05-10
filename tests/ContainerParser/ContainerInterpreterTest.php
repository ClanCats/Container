<?php
namespace ClanCats\Container\Tests\ContainerParser;

use ClanCats\Container\ServiceDefinition;
use ClanCats\Container\ContainerNamespace;

use ClanCats\Container\ContainerParser\{
    ContainerInterpreter,

    // nodes
    Nodes\ScopeNode,
    Nodes\ScopeImportNode,
    Nodes\ParameterDefinitionNode,
    Nodes\ServiceDefinitionNode,
    Nodes\ValueNode
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

    	$interpreter->handleParameterDefinition($artist);
    	$interpreter->handleParameterDefinition($song);
    	$interpreter->handleParameterDefinition($song2);

    	$this->assertEquals([
    		'artist' => 'Juse Ju',
    		'song' => 'DAYONE'
    	], $ns->getParameters());
    }

    /**
     * @expectedException \ClanCats\Container\Exceptions\ContainerInterpreterException
     */
    public function testHandleParameterDefinitionWithoutOverride()
    {
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

    /**
     * @expectedException \ClanCats\Container\Exceptions\ContainerInterpreterException
     */
    public function testHandleScopeImportEmptyPath()
    {
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

    /**
     * @expectedException \ClanCats\Container\Exceptions\ContainerInterpreterException
     */
    public function testHandleServiceDefinitionWithoutOverride()
    {
        $ns = new ContainerNamespace();
        $interpreter = new ContainerInterpreter($ns);

        $testA = new ServiceDefinitionNode('a', 'A');
        $testB = new ServiceDefinitionNode('a', 'B');

        $interpreter->handleServiceDefinition($testA);
        $interpreter->handleServiceDefinition($testB);
    }

}