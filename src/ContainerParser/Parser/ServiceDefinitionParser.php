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
    Nodes\ValueNode,
    Nodes\ServiceDefinitionNode
};

class ServiceDefinitionParser extends ContainerParser
{
    /**
     * Parse the next token
     *
     * @return null|Node
     */
    protected function next()
    {
        $definition = new ServiceDefinitionNode();

        if ($this->currentToken()->isType(T::TOKEN_OVERRIDE))
        {
            $definition->setIsOverride(true); $this->skipToken();
        }

        if (!$this->currentToken()->isType(T::TOKEN_DEPENDENCY))
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }

        // get the paramter name
        $serviceName = $this->currentToken()->getValue();

        // remove the "@" prefix
        $serviceName = substr($serviceName, 1);

        // set the name
        $definition->setName($serviceName);

        // now a assign ":" character must follow
        if (!$this->nextToken()->isType(T::TOKEN_ASSIGN))
        {
            throw $this->errorUnexpectedToken($this->nextToken());
        }

        // at this point we can skip the name and assign character
        $this->skipToken(2);

        // the next token must therefor be a identifier
        if (!$this->currentToken()->isType(T::TOKEN_IDENTIFIER))
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }

        // assign the class name from the identifiers value
        $definition->setClassName($this->currentToken()->getValue());
        $this->skipToken();

        if ($this->currentToken() && $this->currentToken()->isType(T::TOKEN_BRACE_OPEN))
        {
            $this->arguments = $this->parseChild(ArgumentArrayParser::class, $this->getTokensUntilClosingScope());
        }

        return $definition;
    }
}