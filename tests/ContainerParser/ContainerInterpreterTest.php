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

    	$interpreter->handleParameterDefinition($artist);
    	$interpreter->handleParameterDefinition($song);

    	$this->assertEquals([
    		'artist' => 'Juse Ju',
    		'song' => 'Übertreib nich deine Rolle'
    	], $ns->getParameters());
    }
}