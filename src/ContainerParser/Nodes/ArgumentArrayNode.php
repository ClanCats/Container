<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2026 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\{
    Nodes\BaseNode as Node
};

class ArgumentArrayNode extends BaseNode
{
    /**
     * An array of arguments to be passed on the services construction
     * 
     * @var array<AssignableNode>
     */
    protected $arguments = [];

    /**
     * Get all defined arguments
     * 
     * @return array<AssignableNode>
     */
    public function getArguments() : array 
    {
        return $this->arguments;
    }

    /**
     * Add an AssignableNode as argument to the service definition
     */
    public function addArgument(AssignableNode $argument): void
    {
        $this->arguments[] = $argument;
    }

    /**
     * Replace the full arguments array
     *
     * @param array<AssignableNode>         $arguments
     */
    public function setArguments(array $arguments): void
    {
        $this->arguments = $arguments;
    }

    /**
     * Deep clone the contained argument nodes so a copied node tree can be
     * mutated (e.g. during generic materialization) without affecting the
     * original.
     */
    public function __clone()
    {
        foreach ($this->arguments as $index => $argument) {
            $this->arguments[$index] = clone $argument;
        }
    }
}

