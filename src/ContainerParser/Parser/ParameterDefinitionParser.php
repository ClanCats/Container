<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2020 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser\Parser;

use ClanCats\Container\ContainerParser\{
    Nodes\BaseNode as Node,
    ContainerParser,
    Token as T,

    // contextual node
    Nodes\ValueNode,
    Nodes\ParameterDefinitionNode
};

class ParameterDefinitionParser extends ContainerParser
{
    /**
     * Parse the next token
     *
     * @return null|Node
     */
    protected function next()
    {
        $isOverride = false;
        if ($this->currentToken()->isType(T::TOKEN_OVERRIDE))
        {
            $isOverride = true; $this->skipToken();
        }

        if (!$this->currentToken()->isType(T::TOKEN_PARAMETER))
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }

        // get the paramter name
        $parameterName = $this->currentToken()->getValue();

        // remove the ":" prefix
        $parameterName = substr($parameterName, 1);

        // now a assign ":" character must follow
        if (!$this->nextToken()->isType(T::TOKEN_ASSIGN))
        {
            throw $this->errorUnexpectedToken($this->nextToken());
        }

        // at this point we can skip the name and assign character
        $this->skipToken(2);

        // we do allow skipping linebreaks here
        $this->skipTokenOfType([T::TOKEN_LINE]);

        $parameterValue = null;

        // Parameters can contain an array so we need to check 
        // for an open scope here
        if ($this->currentToken()->isType(T::TOKEN_SCOPE_OPEN)) 
        {
            $parameterValue = $this->parseChild(
                ArrayParser::class, 
                $this->getTokensUntilClosingScope(
                    false, 
                    T::TOKEN_SCOPE_OPEN, 
                    T::TOKEN_SCOPE_CLOSE
                ), 
                false
            );
        }
        // otherwise we expect a scalar value
        elseif ($this->currentToken()->isValue())
        {
            $parameterValue = ValueNode::fromToken($this->currentToken());
            $this->skipToken();
        }
        else 
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }

        // create the definition node
        $definition = new ParameterDefinitionNode($parameterName, $parameterValue);
        $definition->setIsOverride($isOverride);

        // return the paramter definition
        return $definition;
    }
}
