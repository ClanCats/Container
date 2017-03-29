<?php 
namespace ClanCats\Container\ContainerParser\Parser;

use ClanCats\Container\ContainerParser\{
    Nodes\BaseNode as Node,
    ContainerParser

    // contextual node
    
};

class ParameterDefinitionParser extends ContainerParser
{
	/**
     * The current scope node
     * 
     * @param ScopeNode
     */
    protected $param;

    /**
     * Prepare the current parser 
     * 
     * @return void
     */
    protected function prepare() 
    {
       
    }

    /**
     * Return the current result
     * 
     * @return null|Node
     */
    protected function node() : Node
    {
        return new Node();
    }

    /**
     * Parse the next token
     *
     * @return null|Node
     */
    protected function next()
    {
        $this->skipToken();
        //var_dump($this->currentToken()); die;
    }
}