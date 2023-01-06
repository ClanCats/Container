<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2023 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\{
    Nodes\BaseNode as Node
};

class ScopeNode extends BaseNode
{
    /**
     * An array of parsed nodes
     * 
     * @var array<Node>
     */
    protected array $nodes = [];

    /**
     * Add a node to the scope
     * 
     * @param Node 			$node
     * @return void
     */
    public function addNode(Node $node) : void
    {
    	$this->nodes[] = $node;
    }

    /**
     * Returns the nodes array
     * 
     * @return array<Node>
     */
    public function getNodes() : array 
    {
        return $this->nodes;
    }
}

