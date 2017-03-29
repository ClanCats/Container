<?php 
namespace ClanCats\Container\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\{
    Nodes\BaseNode as Node
};

class ScopeNode extends BaseNode
{
    /**
     * An array of parsed nodes
     * 
     * @var array
     */
    protected $nodes = [];

    /**
     * Add a node to the scope
     * 
     * @param Node 			$node
     * @return void
     */
    public function addNode(Node $node)
    {
    	$this->nodes[] = $node;
    }
}

