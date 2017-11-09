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
    Nodes\BaseNode as Node
};

class ArrayNode extends BaseNode implements AssignableNode
{
    /**
     * An array of ArrayElementNode
     * 
     * @var [AssignableNode]
     */
    protected $elements = [];

    /**
     * The current arrays index
     *
     * @var int
     */
    private $index = 0;

    /**
     * Get array elements
     * 
     * @return [ArrayElementNode]
     */
    public function getElements() : array 
    {
        return $this->elements;
    }

    /**
     * Add an ArrayElementNode to the current array
     * this will not check for dublicates.
     * 
     * @param $element              ArrayElementNode
     * @return void
     */
    public function addElement(ArrayElementNode $element) 
    {
        $this->elements[] = $element;
    }

    /**
     * Checks if an element with the given key already exists
     *
     * @param string|int            $key
     * @return bool
     */
    public function has($key)
    {
        foreach ($this->elements as $element) {
            if ($element->getKey() == $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Pushes an assignable node to the array elements
     *
     * @param AssignableNode            $value
     * @return void
     */
    public function push(AssignableNode $value)
    {
        // count the index up to find a free spot
        while ($this->has($this->index)) {
            $this->index++;
        }

        $this->addElement(new ArrayElementNode($this->index, $value));
    }
}

