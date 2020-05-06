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
    ContainerParser,
    Token as T,

    // contextual node
    Nodes\ParameterReferenceNode,
    Nodes\ServiceReferenceNode,
    Nodes\ConcatNode,
};

class ExpressionParser extends ContainerParser
{
    /**
     * Parse the next token
     *
     * @return null|Node
     */
    protected function next()
    { 
        $token = $this->currentToken();

        $isConcat = false;
        if ($nextToken = $this->nextToken()) {
            $isConcat = $nextToken->isType(T::TOKEN_PLUS);
        }

        $node;

        // is parameter reference
        if ($token->isType(T::TOKEN_PARAMETER)) 
        {
            $node = $this->parseChild(ReferenceParser::class);
        }

        // is dependency reference
        elseif ($token->isType(T::TOKEN_DEPENDENCY)) 
        {
            return new ServiceReferenceNode($name);
        }

        // anything else?
        else 
        {
            throw $this->errorUnexpectedToken($token);
        }

        if ($isConcat) {
            $node = new ConcatNode($node, $this->parseChild(ExpressionParser::class));
        }
    }
}

