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
    Nodes\ArrayNode
};

class MetaDataAssignmentNode extends BaseNode
{
    /**
     * Meta data key
     * 
     * @var string
     */
    protected string $key;

    /**
     * Should the data be stacked on others
     * 
     * @var bool
     */
    protected bool $isStacked = true;

    /**
     * The data array
     * 
     * @var ArrayNode
     */
    protected ?ArrayNode $data = null;

    /**
     * Service definition constructor
     * 
     * @param string             $key
     * @param ArrayNode|null          $data
     */
    public function __construct(string $key, ?ArrayNode $data = null)
    {
        $this->setKey($key);
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
     * @param string          $key
     * @return void
     */
    public function setKey(string $key) : void
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
    public function setIsStacked(bool $isStacked) : void
    {
        $this->isStacked = $isStacked;
    }

    /**
     * Get the data 
     * 
     * @return ArrayNode|null
     */
    public function getData() : ?ArrayNode 
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
    public function setData(ArrayNode $data): void 
    {
        $this->data = $data;
    }
}

