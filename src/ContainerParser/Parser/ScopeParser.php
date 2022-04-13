<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2022 Mario Döring
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
     * @var ScopeNode
     */
    protected ScopeNode $scope;

    /**
     * Prepare the current parser 
     * 
     * @return void
     */
    protected function prepare() : void
    {
        $this->scope = new ScopeNode;
    }

    /**
     * Return the current result
     */
    protected function node() : ScopeNode
    {
        return $this->scope;
    }
    
    /**
     * Parse the next token
     */
    protected function next() : ?Node
    {
        $token = $this->currentToken();

        // is the current state in override mode?
        if ($token->isType(T::TOKEN_OVERRIDE)) 
        {
            // set the indicating token to the next one
            $token = $this->nextToken();

            // only allow override of service and parameter definitions
            if (!($token->isType(T::TOKEN_PARAMETER) || $token->isType(T::TOKEN_DEPENDENCY)))
            {
                throw $this->errorUnexpectedToken($this->currentToken());
            }
        }

        // is parameter definition
        if ($token->isType(T::TOKEN_PARAMETER)) 
        {
            $this->scope->addNode($this->parseChild(ParameterDefinitionParser::class));
        }

        // is service definition
        elseif ($token->isType(T::TOKEN_DEPENDENCY)) 
        {
            $this->scope->addNode($this->parseChild(ServiceDefinitionParser::class));
        }

        // import another scope
        elseif ($token->isType(T::TOKEN_IMPORT)) 
        {
            $this->scope->addNode($this->parseChild(ScopeImportParser::class));
        }

        // just a linebreak
        elseif ($token->isType(T::TOKEN_LINE)) 
        {
            $this->skipToken();
        }

        // anything else?
        else 
        {
            throw $this->errorUnexpectedToken($token);
        }

        return null;
    }
}

