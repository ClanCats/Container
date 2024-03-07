<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2024 Mario Döring
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
    protected string $path = '';

    /**
     * Set the current import path
     * 
     * @param string 			$path
     */
    public function setPath(string $path) : void
    {
    	$this->path = $path;
    }

    /**
     * Returns the current import path
     */
    public function getPath() : string 
    {
        return $this->path;
    }
}

