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
    ContainerParser,
    Token as T,

    // contextual node
    Nodes\ParameterReferenceNode,
    Nodes\ServiceReferenceNode
};

class ReferenceParser extends ContainerParser
{
    /**
     * Parse the next token
     *
     * @return null|Node
     */
    protected function next()
    { 
        $token = $this->currentToken();

        // get the current value and remove the prefix
        $name = $token->getValue();
        $name = substr($name, 1);

        // is parameter reference
        if ($token->isType(T::TOKEN_PARAMETER)) 
        {
            return new ParameterReferenceNode($name);
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
    }
}

