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
    Nodes\ArgumentArrayNode,
    Nodes\ParameterReferenceNode,
    Nodes\ServiceReferenceNode,
    Nodes\ValueNode
};

class ArgumentArrayParser extends ContainerParser
{
    /**
     * The current arguments node
     * 
     * @param ArgumentArrayNode
     */
    protected $arguments;

    /**
     * Prepare the current parser 
     * 
     * @return void
     */
    protected function prepare() 
    {
        $this->arguments = new ArgumentArrayNode;
    }

    /**
     * Return the current result
     * 
     * @return null|Node
     */
    protected function node() : Node
    {
        return $this->arguments;
    }

    /**
     * Parse the next token
     *
     * @return null|Node
     */
    protected function next()
    { 
        $token = $this->currentToken();

        // the argument might be an array
        if ($this->currentToken()->isType(T::TOKEN_SCOPE_OPEN)) 
        {
            $this->arguments->addArgument($this->parseChild(
                ArrayParser::class, 
                $this->getTokensUntilClosingScope(
                    false, 
                    T::TOKEN_SCOPE_OPEN, 
                    T::TOKEN_SCOPE_CLOSE
                ), 
                false
            ));
        }
        // or a simple scalar value
        elseif ($token->isValue())
        {
            $this->arguments->addArgument(ValueNode::fromToken($token));
        }
        // is it a parameter?
        elseif ($token->isType(T::TOKEN_PARAMETER)) 
        {
            $this->arguments->addArgument($this->parseChild(ReferenceParser::class));
        }
        // is a service reference
        elseif ($token->isType(T::TOKEN_DEPENDENCY)) 
        {
            $this->arguments->addArgument($this->parseChild(ReferenceParser::class));
        }
        // just a linebreak
        elseif ($token->isType(T::TOKEN_LINE)) 
        {
            $this->skipToken(); return;
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
    }
}

