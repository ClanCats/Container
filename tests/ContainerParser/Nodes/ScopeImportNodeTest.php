<?php
namespace ClanCats\Container\Tests\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\{
    Nodes\ScopeImportNode
};

/**
 * This test is probably overkill..
 */
class ScopeImportNodeTest extends \PHPUnit\Framework\TestCase
{
	public function testScopeImportNode()
    {
    	$import = new ScopeImportNode;
    	$import->setPath('foo');
    	$this->assertEquals('foo', $import->getPath());
    }
}