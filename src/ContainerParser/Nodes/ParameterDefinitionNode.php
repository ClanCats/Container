<?php 
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
}

