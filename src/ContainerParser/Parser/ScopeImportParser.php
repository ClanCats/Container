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
    Nodes\ScopeImportNode
};

class ScopeImportParser extends ContainerParser
{
    /**
     * Parse the next token
     *
     * @return null|Node
     */
    protected function next()
    {
        if (!$this->currentToken()->isType(T::TOKEN_IMPORT))
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }

        $this->skipToken(); // skip the import keyword

        // the next token must be an identifier
        if (!$this->currentToken()->isType(T::TOKEN_IDENTIFIER))
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }

        $node = new ScopeImportNode;
        $node->setPath($this->currentToken()->getValue());

        // skip the path
        $this->skipToken();

        return $node;
    }
}

