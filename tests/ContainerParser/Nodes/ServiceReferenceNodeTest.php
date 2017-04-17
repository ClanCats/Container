<?php
namespace ClanCats\Container\Tests\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\{
    Nodes\ServiceReferenceNode
};

/**
 * This test is probably overkill..
 */
class ServiceReferenceNodeTest extends \PHPUnit\Framework\TestCase
{
	public function testServiceReferenceNode()
    {
    	$ref = new ServiceReferenceNode('foo');

    	$this->assertEquals('foo', $ref->getName());

    	$ref->setName('bar');

    	$this->assertEquals('bar', $ref->getName());
    }
}