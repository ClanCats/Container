<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2019 Mario Döring
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

        // if an assign token is present we have a real definition 
        // or alias in front of us. Otherwise it is just an update
        if ($this->nextToken()->isType(T::TOKEN_ASSIGN))
        {
            // at this point we can skip the name and assign character
            $this->skipToken(2);

            // we might have a alias assignment here
            if ($this->currentToken()->isType(T::TOKEN_DEPENDENCY))
            {
                $definition->setIsAlias(true);
            }

            // the next token must therefor be a identifier
            elseif (!$this->currentToken()->isType(T::TOKEN_IDENTIFIER))
            {
                throw $this->errorUnexpectedToken($this->currentToken());
            }

            // assign the class name from the identifiers value
            if ($definition->isAlias()) {
                $definition->setAliasTarget($this->parseChild(ReferenceParser::class));
            } else {
                $definition->setClassName($this->currentToken()->getValue());
            }
            
            $this->skipToken();

            // try to parse service constructor arguments
            if (!$this->parserIsDone() && $this->currentToken()->isType(T::TOKEN_BRACE_OPEN) && (!$definition->isAlias()))
            {
                $arguments = $this->parseChild(ArgumentArrayParser::class, $this->getTokensUntilClosingScope(), false);
                $definition->setArguments($arguments);
            }
        }
        else 
        {
            $definition->setIsUpdate(true);
        }

        // we need at least one linebreak to continue
        while(!$this->parserIsDone() && $this->currentToken()->isType(T::TOKEN_LINE))
        {
            // skip all other linebreak
            $this->skipTokenOfType([T::TOKEN_LINE]);

            // parse servide definiton caller
            if (!$this->parserIsDone() && $this->currentToken()->isType(T::TOKEN_MINUS) && (!$definition->isAlias()))
            {
                $definition->addConstructionAction($this->parseChild(ServiceMethodCallParser::class));
            }
            elseif (!$this->parserIsDone() && $this->currentToken()->isType(T::TOKEN_EQUAL) && (!$definition->isAlias()))
            {
                $definition->addMetaDataAssignemnt($this->parseChild(ServiceMetaDataParser::class));
            }
        }

        return $definition;
    }
}
