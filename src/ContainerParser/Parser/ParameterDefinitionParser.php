<?php 
namespace ClanCats\Container\ContainerParser\Parser;

use ClanCats\Container\ContainerParser\{
    Nodes\BaseNode as Node,
    ContainerParser,
    Token as T,

    // contextual node
    Nodes\ValueNode,
    Nodes\ParameterDefinitionNode
};

class ParameterDefinitionParser extends ContainerParser
{
    /**
     * Prepare the current parser 
     * 
     * @return void
     */
    protected function prepare() {}

    /**
     * Return the current result
     * 
     * @return null|Node
     */
    protected function node() : Node {}

    /**
     * Parse the next token
     *
     * @return null|Node
     */
    protected function next()
    {
        if (!$this->currentToken()->isType(T::TOKEN_PARAMETER))
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }

        // get the paramter name
        $parameterName = $this->currentToken()->getValue();

        // remove the ":" prefix
        $parameterName = substr($parameterName, 1);

        // now a assign ":" character must follow
        if (!$this->nextToken()->isType(T::TOKEN_ASSIGN))
        {
            throw $this->errorUnexpectedToken($this->nextToken());
        }

        // at this point we can skip the name and assign character
        $this->skipToken(2);

        // the next token must therefor be the value
        if (!$this->currentToken()->isValue())
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }

        // create the definition node
        $definition = new ParameterDefinitionNode($parameterName, ValueNode::fromToken($this->currentToken()));

        // skip the token
        $this->skipToken();

        // return the paramter definition
        return $definition;
    }
}