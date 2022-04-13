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
    Nodes\ValueNode,
    Nodes\ConstructionActionNode,
    Nodes\MetaDataAssignmentNode,
    Nodes\ServiceReferenceNode
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
     * @var string
     */
    protected $className;

    /**
     * The services alias target
     * 
     * @var ServiceReferenceNode
     */
    protected ?ServiceReferenceNode $aliasTarget = null;

    /**
     * Does this definition override existing ones?
     * 
     * @var bool
     */
    protected bool $isOverride = false;

    /**
     * In case of an alias the className hold the alias target
     * 
     * @var bool
     */
    protected bool $isAlias = false;

    /**
     * The node only updates an already present service
     *
     * @var bool
     */
    protected bool $isUpdate = false;

    /**
     * An array of arguments to be passed on the services construction
     * 
     * @var ArgumentArrayNode
     */
    protected ?ArgumentArrayNode $arguments = null;

    /**
     * An array of actions to take place after construction
     * 
     * @var array<ConstructionActionNode>
     */
    protected array $constructionActions = [];

    /**
     * An array of actions to take place after construction
     * 
     * @var array<MetaDataAssignmentNode>
     */
    protected array $metaDataAssignments = [];

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
     * @param string 			$name
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
     * Set the services class name
     */
    public function setClassName(string $className): void 
    {
        $this->className = $className;
    }

    /**
     * Get the services alias target name
     * 
     * @return ServiceReferenceNode
     */
    public function getAliasTarget() : ?ServiceReferenceNode 
    {
        return $this->aliasTarget;
    }

    /**
     * Set the services alias target name
     */
    public function setAliasTarget(ServiceReferenceNode $aliasTarget): void 
    {
        $this->aliasTarget = $aliasTarget;
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
     * Get if this definition is an alias to another one
     * 
     * @return bool 
     */
    public function isAlias() : bool
    {
        return $this->isAlias;
    }

    /**
     * Set if this definition is an alias to another one
     * 
     * @param bool          $isAlias
     * @return void
     */
    public function setIsAlias(bool $isAlias)
    {
        $this->isAlias = $isAlias;
    }

    /**
     * Get if this definition is a service update
     * 
     * @return bool 
     */
    public function isUpdate() : bool
    {
        return $this->isUpdate;
    }

    /**
     * Set if this definition is a service update
     * 
     * @param bool          $isUpdate
     * @return void
     */
    public function setIsUpdate(bool $isUpdate)
    {
        $this->isUpdate = $isUpdate;
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
     * @return array<ConstructionActionNode>
     */
    public function getConstructionActions() : array
    {
        return $this->constructionActions;
    }

    /**
     * Add meta data assignment
     * 
     * @param MetaDataAssignmentNode                $meta
     * @return void
     */
    public function addMetaDataAssignemnt(MetaDataAssignmentNode $meta)
    {
        $this->metaDataAssignments[] = $meta;
    }

    /**
     * Get all meta data assignemnts
     * 
     * @return array<MetaDataAssignmentNode>
     */
    public function getMetaDataAssignemnts() : array
    {
        return $this->metaDataAssignments;
    }
}

