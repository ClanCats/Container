<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2019 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\{
    Nodes\ValueNode
};

class ConcatNode extends BaseNode implements AssignableNode
{
    /**
     * The first node
     * 
     * @var BaseNode
     */
    protected $nodeA;

    /**
     * The second node
     * 
     * @var BaseNode
     */
    protected $nodeB;

    /**
     * Concat constructor
     * 
     * @param BaseNode        $nodeA
     * @param BaseNode        $nodeB
     */
    public function __construct(BaseNode $nodeA, BaseNode $nodeB)
    {
        $this->setNodeA($nodeA);
        $this->setNodeB($nodeB);
    }

    /**
     * Get the first node
     * 
     * @return string
     */
    public function getNodeA() : BaseNode 
    {
        return $this->nodeA;
    }

    /**
     * Get the second node
     * 
     * @return string
     */
    public function getNodeB() : BaseNode 
    {
        return $this->nodeB;
    }

    /**
     * Set first node
     * 
     * @param BaseNode          $node
     * @return void
     */
    public function setNodeA(BaseNode $node)
    {
        $this->nodeB = $node;   
    }

    /**
     * Set second node
     * 
     * @param BaseNode          $node
     * @return void
     */
    public function setNodeA(BaseNode $node)
    {
        $this->nodeB = $node;   
    }
}

