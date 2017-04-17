<?php
namespace ClanCats\Container\Tests\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\{
    Nodes\ParameterReferenceNode
};

/**
 * This test is probably overkill..
 */
class ParameterReferenceNodeTest extends \PHPUnit\Framework\TestCase
{
	public function testServiceReferenceNode()
    {
    	$ref = new ParameterReferenceNode('foo');

    	$this->assertEquals('foo', $ref->getName());

    	$ref->setName('bar');

    	$this->assertEquals('bar', $ref->getName());
    }
}