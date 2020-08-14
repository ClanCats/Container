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
    Nodes\ArrayNode,
    Nodes\ArrayElementNode,
    Nodes\ParameterReferenceNode,
    Nodes\ServiceReferenceNode,
    Nodes\ValueNode
};

class ArrayParser extends ContainerParser
{
    /**
     * The current arguments node
     * 
     * @param ArgumentArrayNode
     */
    protected $array;

    /**
     * Prepare the current parser 
     * 
     * @return void
     */
    protected function prepare() 
    {
        $this->array = new ArrayNode;
    }

    /**
     * Return the current result
     * 
     * @return null|Node
     */
    protected function node() : Node
    {
        return $this->array;
    }

    /**
     * Parse the next token
     *
     * @return null|Node
     */
    protected function next()
    {
        // is the current element associative?
        $associativeArray = $this->nextToken() && $this->nextToken()->isType(T::TOKEN_ASSIGN);
        $associativeKeyName = null;

        // if yes, we parse the key first
        if ($associativeArray) 
        {    
            $keyToken = $this->currentToken();

            // the key can be an identifier token and will 
            // be used as a simple string
            if ($keyToken->isType(T::TOKEN_IDENTIFIER))
            {
                $associativeKeyName = $keyToken->getValue();
            }
            // string or numbers
            elseif ($keyToken->isType(T::TOKEN_STRING) || $keyToken->isType(T::TOKEN_NUMBER))
            {
                $associativeKeyName = ValueNode::fromToken($keyToken)->getRawValue();
            }

            $this->skipToken(2); // skip the key & assign token

            // there might be a linebreak between the key and value
            $this->skipTokenOfType([T::TOKEN_LINE]);
        }

        // placeholder for the elements value
        $elementValue = null;

        // value token
        $token = $this->currentToken();

        // check for nested array
        if ($token->isType(T::TOKEN_SCOPE_OPEN)) 
        {
            $elementValue = $this->parseChild(
                ArrayParser::class, 
                $this->getTokensUntilClosingScope(
                    false, 
                    T::TOKEN_SCOPE_OPEN, 
                    T::TOKEN_SCOPE_CLOSE
                ), 
                false
            );
        }

        // is a parameter reference 
        elseif ($token->isValue()) 
        {
            $elementValue = ValueNode::fromToken($token);
        }

        elseif ($token->isType(T::TOKEN_PARAMETER)) 
        {
            $elementValue = $this->parseChild(ReferenceParser::class);
        }

        // is a service reference
        elseif ($token->isType(T::TOKEN_DEPENDENCY)) 
        {
            $elementValue = $this->parseChild(ReferenceParser::class);
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

        // update our array node
        if ($associativeArray) {
            $this->array->addElement(new ArrayElementNode($associativeKeyName, $elementValue));
        } else {
            $this->array->push($elementValue);
        }

        // skip the value token
        $this->skipToken();

        // now ther might follow a seperator indicating another argument
        if (!$this->parserIsDone() && $this->currentToken()->isType(T::TOKEN_SEPERATOR)) 
        {
            $this->skipToken();
        }
    }
}

