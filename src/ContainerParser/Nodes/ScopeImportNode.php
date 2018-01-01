<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2018 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser\Nodes;

class ScopeImportNode extends BaseNode
{
    /**
     * The path of the import statement
     * 
     * @var string
     */
    protected $path = '';

    /**
     * Set the current import path
     * 
     * @param Node 			$node
     * @return void
     */
    public function setPath(string $path)
    {
    	$this->path = $path;
    }

    /**
     * Returns the current import path
     * 
     * @return array
     */
    public function getPath() : string 
    {
        return $this->path;
    }
}

