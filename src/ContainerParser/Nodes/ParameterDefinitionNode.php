<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2017 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\{
    Nodes\BaseNode as Node,
    Nodes\ValueNode
};

class ParameterDefinitionNode extends BaseNode
{
    /**
     * The parameters name
     * 
     * @var string
     */
    protected $name;

    /**
     * The value that is being defined
     * 
     * @param ValueNode
     */
    protected $value;

    /**
     * Does this definition override existing ones?
     * 
     * @var bool
     */
    protected $isOverride = false;

    /**
     * Parameter definition constructor
     * 
     * @param string        $node
     * @param ValueNode     $value
     */
    public function __construct(string $name, ValueNode $value)
    {
        $this->setName($name);
        $this->setValue($value);
    }

    /**
     * Get the paramters name
     * 
     * @return string
     */
    public function getName() : string 
    {
        return $this->name;
    }

    /**
     * Get the parameters value
     * 
     * @return ValueNode
     */
    public function getValue() : ValueNode 
    {
        return $this->value;
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
     * Set the parameters value
     * 
     * @param ValueNode             $value
     * @return void
     */
    public function setValue(ValueNode $value)
    {
        $this->value = $value;
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
}

