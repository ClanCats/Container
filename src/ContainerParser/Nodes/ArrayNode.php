<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2024 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser\Nodes;

use ClanCats\Container\Exceptions\LogicalNodeException;

use ClanCats\Container\ContainerParser\{
    Nodes\BaseNode as Node
};

class ArrayNode extends BaseNode implements AssignableNode
{
    /**
     * An array of ArrayElementNode
     * 
     * @var array<ArrayElementNode>
     */
    protected array $elements = [];

    /**
     * The current arrays index
     *
     * @var int
     */
    private int $index = 0;

    /**
     * Get array elements
     * 
     * @return array<ArrayElementNode>
     */
    public function getElements() : array 
    {
        return $this->elements;
    }

    /**
     * Add an ArrayElementNode to the current array
     * this will not check for dublicates.
     * 
     * @param ArrayElementNode          $element
     * @return void
     */
    public function addElement(ArrayElementNode $element) : void
    {
        $this->elements[] = $element;
    }

    /**
     * Checks if an element with the given key already exists
     *
     * @param string|int            $key
     * @return bool
     */
    public function has($key) : bool
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
    public function push(AssignableNode $value) : void
    {
        // count the index up to find a free spot
        while ($this->has($this->index)) {
            $this->index++;
        }

        $this->addElement(new ArrayElementNode($this->index, $value));
    }

    /**
     * Converts the current array to a PHP array
     * This will throw an exception when the array contains a reference
     * to a service or parameter
     *
     * @return array<mixed>
     */
    public function convertToNativeArray() : array
    {
        $array = [];

        foreach($this->getElements() as $element)
        {
            $value = $element->getValue();

            if ($value instanceof ValueNode) {
                $array[$element->getKey()] = $value->getRawValue();
            } 
            elseif ($value instanceof ArrayNode) {
                $array[$element->getKey()] = $value->convertToNativeArray();
            }
            else {
                throw new LogicalNodeException("You cannot convert a ctn array to PHP that contains a reference to a service or parameter.");
            }
        }

        return $array;
    }
}

