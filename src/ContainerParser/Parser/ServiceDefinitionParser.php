<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2026 Mario Döring
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
    Nodes\ServiceDefinitionNode,
    Nodes\GenericDefinitionNode,
    Nodes\GenericMaterializationNode,
    Nodes\TypeParameterPlaceholderNode
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

        // a generic definition is a service name directly followed by an
        // angle bracket type parameter list: @name<T, U>: ...
        if (!$this->parserIsDone() && $this->currentToken()->isType(T::TOKEN_GENERIC_OPEN))
        {
            return $this->parseGenericDefinition($serviceName, $definition->isOverride());
        }

        // if an assign token is present we have a real definition
        // or alias in front of us. Otherwise it is just an update
        if (!$this->parserIsDone() && $this->currentToken()->isType(T::TOKEN_ASSIGN))
        {
            // at this point we can skip the assign character
            $this->skipToken();

            // we might have an alias assignment (or a generic materialization)
            // when the assign is directly followed by a service reference
            if ($this->currentToken()->isType(T::TOKEN_DEPENDENCY))
            {
                $ref = $this->parseChild(ReferenceParser::class);

                if (!($ref instanceof ServiceReferenceNode)) {
                    throw $this->errorParsing("Invalid service reference given");
                }

                // a generic materialization is a reference to a generic
                // directly followed by an angle bracket type argument list:
                // @name: @generic<Arg, ...>
                if (!$this->parserIsDone() && $this->currentToken()->isType(T::TOKEN_GENERIC_OPEN))
                {
                    return $this->parseGenericMaterialization($serviceName, $ref->getName(), $definition->isOverride());
                }

                $definition->setIsAlias(true);
                $definition->setAliasTarget($ref);
            }
            else
            {
                $this->parseClassNameAndArguments($definition);
            }
        }
        else
        {
            $definition->setIsUpdate(true);
        }

        $this->parseConstructionActionsAndMetaData($definition);

        return $definition;
    }

    /**
     * Parse the class name (which may itself be a generic type parameter) and
     * the optional constructor arguments of a service definition. The current
     * token must be the class name identifier.
     *
     * @param ServiceDefinitionNode     $definition
     * @return void
     */
    protected function parseClassNameAndArguments(ServiceDefinitionNode $definition) : void
    {
        if (!$this->currentToken()->isType(T::TOKEN_IDENTIFIER))
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }

        $className = $this->currentToken()->getValue();

        // inside a generic body the class name may be a type parameter
        if ($this->isTypeParameter($className)) {
            $definition->setClassNamePlaceholder(new TypeParameterPlaceholderNode($className, false));
        } else {
            $definition->setClassName($className);
        }

        $this->skipToken();

        // try to parse service constructor arguments
        if (!$this->parserIsDone() && $this->currentToken()->isType(T::TOKEN_BRACE_OPEN))
        {
            $arguments = $this->parseChild(ArgumentArrayParser::class, $this->getTokensUntilClosingScope(), false);

            if (!($arguments instanceof ArgumentArrayNode)) {
                throw $this->errorParsing("Could not parse constructor arguments for service.");
            }

            $definition->setArguments($arguments);
        }
    }

    /**
     * Parse the trailing method calls (`- call(...)`) and meta data
     * assignments (`= key: ...`) of a service definition.
     *
     * @param ServiceDefinitionNode     $definition
     * @return void
     */
    protected function parseConstructionActionsAndMetaData(ServiceDefinitionNode $definition) : void
    {
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
    }

    /**
     * Parse a generic definition template.
     * The service name has already been consumed, the current token is the
     * opening angle bracket.
     *
     * @param string        $name
     * @param bool          $isOverride
     * @return GenericDefinitionNode
     */
    protected function parseGenericDefinition(string $name, bool $isOverride) : GenericDefinitionNode
    {
        $node = new GenericDefinitionNode();
        $node->setName($name);
        $node->setIsOverride($isOverride);

        // read the comma separated type parameter names
        foreach ($this->parseAngleBracketTokenList() as $token)
        {
            if (!$token->isType(T::TOKEN_IDENTIFIER)) {
                throw $this->errorParsing("Generic type parameters must be plain identifiers");
            }

            if (in_array($token->getValue(), $node->getTypeParameters(), true)) {
                throw $this->errorParsing("Duplicate generic type parameter \"{$token->getValue()}\"");
            }

            $node->addTypeParameter($token->getValue());
        }

        if ($node->getTypeParameters() === []) {
            throw $this->errorParsing("A generic must declare at least one type parameter");
        }

        // the type parameter list must be followed by an assign
        if ($this->parserIsDone() || !$this->currentToken()->isType(T::TOKEN_ASSIGN)) {
            throw $this->errorParsing("Expected \":\" after generic type parameters");
        }

        // parse the body once into a real service definition template. The type
        // parameter context makes the leaf parsers emit placeholders wherever a
        // type parameter is used (class name, arguments, `::class`, meta data).
        $this->setTypeParameterContext(array_fill_keys($node->getTypeParameters(), true));

        // skip the assign token, then parse the class definition body
        $this->skipToken();

        $template = new ServiceDefinitionNode($name);
        $this->parseClassNameAndArguments($template);
        $this->parseConstructionActionsAndMetaData($template);

        $node->setTemplate($template);

        return $node;
    }

    /**
     * Parse a generic materialization.
     * The generic reference has already been consumed, the current token is the
     * opening angle bracket.
     *
     * @param string        $name           The concrete service name to create.
     * @param string        $genericName    The referenced generic name.
     * @param bool          $isOverride
     * @return GenericMaterializationNode
     */
    protected function parseGenericMaterialization(string $name, string $genericName, bool $isOverride) : GenericMaterializationNode
    {
        $node = new GenericMaterializationNode();
        $node->setName($name);
        $node->setGenericName($genericName);
        $node->setIsOverride($isOverride);

        // each type argument is a single token: a class name, a scalar value,
        // a service reference (@) or a parameter reference (:)
        foreach ($this->parseAngleBracketTokenList() as $token)
        {
            $node->addTypeArgument($token);
        }

        return $node;
    }

    /**
     * Read a comma separated list of single tokens enclosed in angle brackets.
     * Shared by generic definitions (where the tokens are type parameter names)
     * and materializations (where they are type argument values). The current
     * token must be the opening angle bracket, it is consumed together with the
     * closing bracket.
     *
     * @return array<T>
     */
    protected function parseAngleBracketTokenList() : array
    {
        // consume the opening angle bracket
        $this->skipToken();

        $tokens = [];

        while (!$this->parserIsDone() && !$this->currentToken()->isType(T::TOKEN_GENERIC_CLOSE))
        {
            $token = $this->currentToken();

            // skip separators and line breaks between arguments
            if ($token->isType(T::TOKEN_SEPERATOR) || $token->isType(T::TOKEN_LINE)) {
                $this->skipToken(); continue;
            }

            // arrays and nested scopes are not supported as type arguments
            if ($token->isType(T::TOKEN_SCOPE_OPEN) || $token->isType(T::TOKEN_BRACE_OPEN)) {
                throw $this->errorParsing("Only single value type arguments are supported inside angle brackets");
            }

            $tokens[] = $token;
            $this->skipToken();
        }

        if ($this->parserIsDone()) {
            throw $this->errorParsing("Unterminated generic type argument list, expected \">\"");
        }

        // consume the closing angle bracket
        $this->skipToken();

        return $tokens;
    }
}
