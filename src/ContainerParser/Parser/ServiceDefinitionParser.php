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
    Nodes\ValueNode,
    Nodes\ConstructionActionNode,
    Nodes\MetaDataAssignmentNode,
    Nodes\ArgumentArrayNode,
    Nodes\ServiceDefinitionNode
};
use ClanCats\Container\ContainerParser\Nodes\ServiceReferenceNode;

class ServiceDefinitionParser extends ContainerParser
{
    /**
     * Parse the next token
     *
     * @return null|Node
     */
    protected function next() : ?Node
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
        
        // skip the service name token
        $this->skipToken();

        // if an assign token is present we have a real definition 
        // or alias in front of us. Otherwise it is just an update
        if (!$this->parserIsDone() && $this->currentToken()->isType(T::TOKEN_ASSIGN))
        {
            // at this point we can skip the assign character
            $this->skipToken();

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
                $ref = $this->parseChild(ReferenceParser::class);

                if (!($ref instanceof ServiceReferenceNode)) {
                    throw $this->errorParsing("Invalid service reference given");
                }

                $definition->setAliasTarget($ref);
            } else {
                $definition->setClassName($this->currentToken()->getValue());
            }
            
            $this->skipToken();

            // try to parse service constructor arguments
            if (!$this->parserIsDone() && $this->currentToken()->isType(T::TOKEN_BRACE_OPEN) && (!$definition->isAlias()))
            {
                $arguments = $this->parseChild(ArgumentArrayParser::class, $this->getTokensUntilClosingScope(), false);

                if (!($arguments instanceof ArgumentArrayNode)) {
                    throw $this->errorParsing("Could not parse constructor arguments for service.");
                }

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
                $node = $this->parseChild(ServiceMethodCallParser::class);
                if (!$node instanceof ConstructionActionNode) {
                    throw $this->errorParsing('Trying to assign a non mehtod call to constructor');
                }

                $definition->addConstructionAction($node);
            }
            elseif (!$this->parserIsDone() && $this->currentToken()->isType(T::TOKEN_EQUAL) && (!$definition->isAlias()))
            {
                $node = $this->parseChild(ServiceMetaDataParser::class);
                if (!$node instanceof MetaDataAssignmentNode) {
                    throw $this->errorParsing('Trying to assign a non meta data assignment to service');
                }

                $definition->addMetaDataAssignemnt($node);
            }
        }

        return $definition;
    }
}
