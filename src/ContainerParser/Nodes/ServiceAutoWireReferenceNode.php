<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2023 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\Token;

class ServiceAutoWireReferenceNode extends BaseNode implements AssignableNode
{
    private Token $referencingToken;

    /**
     * @param Token $referencingToken The token that produced this node, used for error messages
     * @return void 
     */
    public function __construct(Token $referencingToken)
    {
        $this->referencingToken = $referencingToken;
    }

    /**
     * Returns the token that produced this node, used for error messages
     * 
     * @return Token
     */
    public function getReferencingToken() : Token
    {
        return $this->referencingToken;
    }
}

