<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2026 Mario Döring
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
     * Set of integer keys already used by elements, kept so `push()` can find
     * the next free spot in O(1) amortized instead of scanning all elements.
     *
     * @var array<int, bool>
     */
    private array $usedIntKeys = [];

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

        // remember integer keys so push() can skip already used spots without
        // rescanning all elements. Integer-like string keys are normalized the
        // same way PHP coerces native array keys (e.g. "3" => 3) so a later
        // push() never collides with them.
        $key = $element->getKey();
        if (is_int($key)) {
            $this->usedIntKeys[$key] = true;
        } elseif (is_string($key) && (string)(int)$key === $key) {
            $this->usedIntKeys[(int)$key] = true;
        }
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
        while (isset($this->usedIntKeys[$this->index])) {
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

    /**
     * Deep clone the contained element nodes. The scalar index bookkeeping
     * copies by value so it needs no special handling.
     */
    public function __clone()
    {
        foreach ($this->elements as $index => $element) {
            $this->elements[$index] = clone $element;
        }
    }
}

