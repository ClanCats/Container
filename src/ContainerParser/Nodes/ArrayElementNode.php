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
    Nodes\BaseNode as Node
};

class ArrayElementNode extends BaseNode
{
    /**
     * The elements key
     * this can be a string or an int if the key is being generated.
     * 
     * @var string|int
     */
    protected $key;

    /**
     * The elements values
     *
     * @var AssignableNode
     */
    protected AssignableNode $value;

    /**
     * Array Element constructor
     * 
     * @param string|int         $key
     * @param AssignableNode     $value
     */
    public function __construct($key, AssignableNode $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Get the elements key
     * 
     * @return string|int
     */
    public function getKey() 
    {
        return $this->key;
    }

    /**
     * Get the elements value
     * 
     * @return AssignableNode
     */
    public function getValue() : AssignableNode 
    {
        return $this->value;
    }
}
