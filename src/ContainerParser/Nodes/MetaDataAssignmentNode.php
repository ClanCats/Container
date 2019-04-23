<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2019 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser\Nodes;

use ClanCats\Container\Exceptions\LogicalNodeException;

use ClanCats\Container\ContainerParser\{
    Nodes\ArrayNode
};

class MetaDataAssignmentNode extends BaseNode
{
    /**
     * Meta data key
     * 
     * @var string
     */
    protected $key;

    /**
     * Should the data be stacked on others
     * 
     * @param string
     */
    protected $isStacked = true;

    /**
     * The data array
     * 
     * @var ArrayNode
     */
    protected $data;

    /**
     * Service definition constructor
     * 
     * @param string        $name
     * @param string        $className
     */
    public function __construct(string $key = null, ArrayNode $data = null)
    {
        if (!is_null($key)) { $this->setKey($key); }
        if (!is_null($data)) { $this->setData($data); }
    }

    /**
     * Meta data key
     * 
     * @return string
     */
    public function getKey() : string 
    {
        return $this->key;
    }

    /**
     * Set the meta data key
     * 
     * @param Node          $node
     * @return void
     */
    public function setKey(string $key)
    {
        $this->key = $key;   
    }

    /**
     * Is the data stacked?
     * 
     * @return bool 
     */
    public function isStacked() : bool
    {
        return $this->isStacked;
    }

    /**
     * Set if the data should be stacked
     * 
     * @param bool          $isStacked
     * @return void
     */
    public function setIsStacked(bool $isStacked)
    {
        $this->isStacked = $isStacked;
    }

    /**
     * Get the data 
     * 
     * @return ArrayNode
     */
    public function getData() : ArrayNode 
    {
        if (!$this->hasData()) 
        {
            throw new LogicalNodeException("This meta data node has no data.");
        }

        return $this->data;
    }

    /**
     * Check if arguments are defined 
     * Note the arguments can still be empty
     * 
     * @return ArgumentArrayNode
     */
    public function hasData() : bool 
    {
        return !is_null($this->data);
    }

    /**
     * Set the data
     * 
     * @param ArrayNode         $data
     */
    public function setData(ArrayNode $data) 
    {
        $this->data = $data;
    }
}

