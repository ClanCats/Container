<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2022 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\{
    Nodes\ValueNode
};

class ParameterReferenceNode extends BaseNode implements AssignableNode
{
    /**
     * The parameters name
     * 
     * @var string
     */
    protected $name;

    /**
     * Parameter reference constructor
     * 
     * @param string        $name
     */
    public function __construct(string $name)
    {
        $this->setName($name);
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
     * Set the parameters name
     * 
     * @param string 			$name
     */
    public function setName(string $name) : void
    {
    	$this->name = $name;   
    }
}

