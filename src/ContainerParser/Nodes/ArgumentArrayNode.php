<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2018 Mario Döring
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
     * @var [AssignableNode]
     */
    protected $arguments = [];

    /**
     * Get all defined arguments
     * 
     * @return [AssignableNode]
     */
    public function getArguments() : array 
    {
        return $this->arguments;
    }

    /**
     * Add an AssignableNode as argument to the service definition
     * 
     * @return [AssignableNode]
     */
    public function addArgument(AssignableNode $argument) 
    {
        $this->arguments[] = $argument;
    }
}

