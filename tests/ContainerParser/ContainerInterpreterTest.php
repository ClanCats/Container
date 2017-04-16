<?php
namespace ClanCats\Container\Tests\ContainerParser;

use ClanCats\Container\ContainerNamespace;

use ClanCats\Container\ContainerParser\{
    ContainerInterpreter,

    // nodes
    Nodes\ParameterDefinitionNode,
    Nodes\ValueNode
};

class ContainerInterpreterTest extends \PHPUnit\Framework\TestCase
{
	public function testConstruct()
    {
    	$ns = new ContainerNamespace();
    	$interpreter = new ContainerInterpreter($ns);

    	$this->assertInstanceOf(ContainerInterpreter::class, $interpreter);
    }

    public function testHandleScope()
    {
    	
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
}