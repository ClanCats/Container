<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2024 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser\Parser;

use ClanCats\Container\ContainerParser\{
    Nodes\BaseNode as Node,
    ContainerParser,
    Token as T,

    // contextual node
    Nodes\ArgumentArrayNode,
    Nodes\ParameterReferenceNode,
    Nodes\ServiceReferenceNode,
    Nodes\ValueNode
};
use ClanCats\Container\ContainerParser\Nodes\AssignableNode;

class ArgumentArrayParser extends ContainerParser
{
    /**
     * The current arguments node
     * 
     * @var ArgumentArrayNode
     */
    protected ArgumentArrayNode $arguments;

    /**
     * Prepare the current parser 
     * 
     * @return void
     */
    protected function prepare() : void
    {
        $this->arguments = new ArgumentArrayNode;
    }

    /**
     * Return the current result
     */
    protected function node() : Node
    {
        return $this->arguments;
    }

    /**
     * Throws an exception when a unassignable node is given to assign
     */
    private function addArgumentSafe(Node $node) : void
    {
        if (!($node instanceof AssignableNode)) {
            throw $this->errorParsing("Trying to assign unassignable to argument vector");
        }

        $this->arguments->addArgument($node);
    }

    /**
     * Parse the next token
     */
    protected function next() : ?Node
    { 
        $token = $this->currentToken();

        // the argument might be an array
        if ($this->currentToken()->isType(T::TOKEN_SCOPE_OPEN)) 
        {
            $node = $this->parseChild(
                ArrayParser::class, 
                $this->getTokensUntilClosingScope(
                    false, 
                    T::TOKEN_SCOPE_OPEN, 
                    T::TOKEN_SCOPE_CLOSE
                ), 
                false
            );

            $this->addArgumentSafe($node);
        }
        // or a simple scalar value
        elseif ($token->isValue())
        {
            $this->arguments->addArgument(ValueNode::fromToken($token));
        }
        // is it a parameter?
        elseif ($token->isType(T::TOKEN_PARAMETER)) 
        {   
            $this->addArgumentSafe($this->parseChild(ReferenceParser::class));
        }
        // is a service reference
        elseif ($token->isType(T::TOKEN_DEPENDENCY)) 
        {
            $this->addArgumentSafe($this->parseChild(ReferenceParser::class));
        }
        // just a linebreak
        elseif ($token->isType(T::TOKEN_LINE)) 
        {
            $this->skipToken(); return null;
        }

        // anything else?
        else 
        {
            throw $this->errorUnexpectedToken($token);
        }

        $this->skipToken();

        // now ther might follow a seperator indicating another argument
        if (!$this->parserIsDone() && $this->currentToken()->isType(T::TOKEN_SEPERATOR)) 
        {
            $this->skipToken();
        }

        return null;
    }
}

