<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2022 Mario Döring
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
    protected string $name;

    /**
     * Should the call be stacked on others
     * 
     * @var bool
     */
    protected bool $isStacked = false;

    /**
     * An array of arguments to be passed on the services construction
     * 
     * @var ArgumentArrayNode
     */
    protected ?ArgumentArrayNode $arguments = null;

    /**
     * Service definition constructor
     * 
     * @param string                   $name
     * @param ArgumentArrayNode|null        $arguments
     */
    public function __construct(string $name, ?ArgumentArrayNode $arguments = null)
    {
        $this->setName($name);
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
     * @param string          $name
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
        if (is_null($this->arguments)) {
            throw new LogicalNodeException("This service definition has no arguments.");
        }

        return $this->arguments;
    }

    /**
     * Check if arguments are defined 
     * Note the arguments can still be empty
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
    public function setArguments(ArgumentArrayNode $arguments): void 
    {
        $this->arguments = $arguments;
    }
}

