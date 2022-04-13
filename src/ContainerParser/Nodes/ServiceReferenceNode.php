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

class ServiceReferenceNode extends BaseNode implements AssignableNode
{
    /**
     * The parameters name
     * 
     * @var string
     */
    protected string $name;

    /**
     * Service reference constructor
     * 
     * @param string        $name
     */
    public function __construct(string $name)
    {
        $this->setName($name);
    }

    /**
     * Get the Service name
     * 
     * @return string
     */
    public function getName() : string 
    {
        return $this->name;
    }

    /**
     * Set the Service name
     * 
     * @param string 			$name
     * @return void
     */
    public function setName(string $name) : void
    {
    	$this->name = $name;   
    }
}

