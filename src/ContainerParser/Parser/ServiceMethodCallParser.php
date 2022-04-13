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
    Nodes\ServiceMethodCallNode
};

class ServiceMethodCallParser extends ContainerParser
{
    /**
     * Parse the next token
     *
     * @return null|Node
     */
    protected function next()
    {
        if (!$this->currentToken()->isType(T::TOKEN_MINUS))
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }

        $this->skipToken();

        if (!$this->currentToken()->isType(T::TOKEN_IDENTIFIER))
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }

        // create the node
        $call = new ServiceMethodCallNode($this->currentToken()->getValue());
        $this->skipToken();

        // we might already be done..
        if ($this->parserIsDone() || $this->currentToken()->isType(T::TOKEN_LINE))
        {
            return $call;
        }

        // otherwise we expect an opening brace
        if (!$this->currentToken()->isType(T::TOKEN_BRACE_OPEN))
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }

        $arguments = $this->parseChild(ArgumentArrayParser::class, $this->getTokensUntilClosingScope(), false);
        $call->setArguments($arguments);

        return $call;
    }
}
