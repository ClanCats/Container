<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2017 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser\Nodes;

use ClanCats\Container\Exceptions\LogicalNodeException;

use ClanCats\Container\ContainerParser\{
    Nodes\ValueNode,
    Nodes\ConstructionActionNode
};

class ServiceDefinitionNode extends BaseNode
{
    /**
     * The service name
     * 
     * @var string
     */
    protected $name;

    /**
     * The services class name
     * 
     * @param string
     */
    protected $className;

    /**
     * Does this definition override existing ones?
     * 
     * @var bool
     */
    protected $isOverride = false;

    /**
     * An array of arguments to be passed on the services construction
     * 
     * @var ArgumentArrayNode
     */
    protected $arguments;

    /**
     * An array of actions to take place after construction
     * 
     * @var [ConstructionActionNode]
     */
    protected $constructionActions = [];

    /**
     * Service definition constructor
     * 
     * @param string        $name
     * @param string        $className
     */
    public function __construct(string $name = null, string $className = null)
    {
        if (!is_null($name)) { $this->setName($name); }
        if (!is_null($className)) {  $this->setClassName($className); }
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
     * @param Node 			$node
     * @return void
     */
    public function setName(string $name)
    {
    	$this->name = $name;   
    }

    /**
     * Get the services class name
     * 
     * @return string
     */
    public function getClassName() : string 
    {
        return $this->className;
    }

    /**
     * Get the services class name
     * 
     * @return string
     */
    public function setClassName(string $className) 
    {
        $this->className = $className;
    }

    /**
     * Get if this definition override existing ones?
     * 
     * @return bool 
     */
    public function isOverride() : bool
    {
        return $this->isOverride;
    }

    /**
     * Set if this definition override existing ones.
     * 
     * @param bool          $isOverride
     * @return void
     */
    public function setIsOverride(bool $isOverride)
    {
        $this->isOverride = $isOverride;
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

    /**
     * Add a construction action to the service definition
     * 
     * @param ConstructionActionNode                $action
     * @return void
     */
    public function addConstructionAction(ConstructionActionNode $action)
    {
        $this->constructionActions[] = $action;
    }

    /**
     * Get all construction actions
     * 
     * @return array[ConstructionActionNode]
     */
    public function getConstructionActions() : array
    {
        return $this->constructionActions;
    }
}

