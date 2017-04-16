<?php
namespace ClanCats\Container\Tests\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\{
    Nodes\ScopeNode
};

/**
 * This test is probably overkill..
 */
class ScopeNodeTest extends \PHPUnit\Framework\TestCase
{
	public function testScopeNode()
    {
    	$scope = new ScopeNode;
        $this->assertEquals([], $scope->getNodes());
        $scope->addNode(new ScopeNode);
        $this->assertCount(1, $scope->getNodes());
    }
}