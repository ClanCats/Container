<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2018 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser\Nodes;

use ClanCats\Container\Exceptions\LogicalNodeException;

use ClanCats\Container\ContainerParser\{
    Nodes
};

class ServiceMethodCallNode extends BaseNode implements ConstructionActionNode
{
    /**
     * The method name beeing called
     * 
     * @var string
     */
    protected $name;

    /**
     * Should the call be stacked on others
     * 
     * @param string
     */
    protected $isStacked = false;

    /**
     * An array of arguments to be passed on the services construction
     * 
     * @var ArgumentArrayNode
     */
    protected $arguments;

    /**
     * Service definition constructor
     * 
     * @param string        $name
     * @param string        $className
     */
    public function __construct(string $name = null, ArgumentArrayNode $arguments = null)
    {
        if (!is_null($name)) { $this->setName($name); }
        if (!is_null($arguments)) {  $this->setArguments($arguments); }
    }

    /**
     * Get the services name
     * 
     * @return string
     */
    public function getName() : string 
    {
        return $this->name;
    }

    /**
     * Set the parameters name
     * 
     * @param Node          $node
     * @return void
     */
    public function setName(string $name)
    {
        $this->name = $name;   
    }

    /**
     * Is the method call stacked?
     * 
     * @return bool 
     */
    public function isStacked() : bool
    {
        return $this->isStacked;
    }

    /**
     * Set if this method call should be stacked
     * 
     * @param bool          $isStacked
     * @return void
     */
    public function setIsStacked(bool $isStacked)
    {
        $this->isStacked = $isStacked;
    }

    /**
     * Get defined arguments node
     * 
     * @return ArgumentArrayNode
     */
    public function getArguments() : ArgumentArrayNode 
    {
        if (!$this->hasArguments()) 
        {
            throw new LogicalNodeException("This service definition has no arguments.");
        }

        return $this->arguments;
    }

    /**
     * Check if arguments are defined 
     * Note the arguments can still be empty
     * 
     * @return ArgumentArrayNode
     */
    public function hasArguments() : bool 
    {
        return !is_null($this->arguments);
    }

    /**
     * Set the arguments array
     * 
     * @param ArgumentArrayNode         $arguments
     */
    public function setArguments(ArgumentArrayNode $arguments) 
    {
        $this->arguments = $arguments;
    }
}

