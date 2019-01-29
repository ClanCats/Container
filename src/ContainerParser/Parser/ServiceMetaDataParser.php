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
    Nodes\MetaDataAssignmentNode
};

class ServiceMetaDataParser extends ContainerParser
{
    /**
     * Parse the next token
     *
     * @return null|Node
     */
    protected function next()
    {
        if (!$this->currentToken()->isType(T::TOKEN_EQUAL))
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }

        $this->skipToken();

        if (!$this->currentToken()->isType(T::TOKEN_IDENTIFIER))
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }

        // create the node
        $meta = new MetaDataAssignmentNode($this->currentToken()->getValue());
        $this->skipToken();

        // we might already be done..
        if ($this->parserIsDone() || $this->currentToken()->isType(T::TOKEN_LINE))
        {
            return $meta;
        }

        // otherwise we expect an double point `:`
        if (!$this->currentToken()->isType(T::TOKEN_ASSIGN))
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }
        $this->skipToken();

        // parse the data
        $data = $this->parseChild(
            ArrayParser::class, 
            $this->getTokensUntil(T::TOKEN_LINE, true), 
            false
        );
        $meta->setData($data);

        return $meta;
    }
}
