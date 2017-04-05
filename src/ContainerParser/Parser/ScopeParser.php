<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2017 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser\Parser;

use ClanCats\Container\ContainerParser\{
    Nodes\BaseNode as Node,
    ContainerParser,
    Token as T,

    // contextual node
    Nodes\ScopeNode
};

class ScopeParser extends ContainerParser
{
    /**
     * The current scope node
     * 
     * @param ScopeNode
     */
    protected $scope;

    /**
     * Prepare the current parser 
     * 
     * @return void
     */
    protected function prepare() 
    {
        $this->scope = new ScopeNode;
    }

    /**
     * Return the current result
     * 
     * @return null|Node
     */
    protected function node() : Node
    {
        return $this->scope;
    }

    /**
     * Parse the next token
     *
     * @return null|Node
     */
    protected function next()
    {
        $token = $this->currentToken();

        if ($token->isType(T::TOKEN_PARAMETER)) 
        {
            $this->scope->addNode($this->parseChild(ParameterDefinitionParser::class));
        }
        elseif ($token->isType(T::TOKEN_LINE)) 
        {
            $this->skipToken();
        }
        else 
        {
            throw $this->errorUnexpectedToken($token);
        }
    }
}

