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
    Nodes\ValueNode
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
     * @var [[int:type, string:name, [arguments]]]
     */
    protected $constructionActions = [];

    /**
     * The available construction action types
     */
    const ACTION_CALL = 0;
    const ACTION_ASSIGN_PROPERTY = 1;

    /**
     * An array of meta data assigned to the service definition
     * 
     * @var [AssignableNode]
     */
    protected $meta = [];

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
}

