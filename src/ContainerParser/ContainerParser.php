<?php 
namespace ClanCats\Container\ContainerParser;

use ClanCats\Container\{
    Exceptions\ContainerParserException,
    
    // Alias the token as T
    ContainerParser\Token as T,

    ContainerParser\Nodes\BaseNode as Node
};

class ContainerParser
{
    /**
     * The tokens in this code segment
     *
     * @var array[Token]
     */
    protected $tokens = array();

    /**
     * The current index while parsing trough the tokens
     *
     * @var int
     */
    protected $index = 0;

    /**
     * The number of tokens to parse
     *
     * @var int
     */
    protected $tokenCount = 0;

    /**
     * The constructor
     * You have to initialize the Parser with an array of lexed tokens.
     *
     * @var array[T]             $tokens
     * @return void
     */
    public function __construct(array $tokens)
    {
        foreach ($tokens as $key => $token) 
        {
            // remove all comments and whitespaces
            if ($token->getType() === T::TOKEN_COMMENT || $token->getType() === T::TOKEN_SPACE) 
            {
                unset($tokens[$key]);
            }
        }

        // prepare the parser
        $this->prepare();

        // set the initial tokens
        $this->setTokens($tokens);
    }

    /**
     * Set the tokens of the current parser
     * 
     * @param array[T]             $tokens
     * @return void
     */
    public function setTokens(array $tokens)
    {
        // reset the keys
        $this->tokens = array_values($tokens);

        // count the real number of tokens
        $this->tokenCount = count($this->tokens);

        // reset the index
        $this->index = 0;
    }

    /**
     * Returns all curent tokens 
     * 
     * @return array[T]
     */
    public function getTokens() : array
    {
        return $this->tokens;
    }

    /**
     * Get the number of tokens
     * 
     * @return int
     */
    public function getTokenCount() : int
    {
        return $this->tokenCount;
    }

    /**
     * Returns the current index
     * 
     * @return int
     */
    public function getIndex() : int
    {
        return $this->index;
    }

    /**
     * Should be in the most cases identical with the current index
     * But may variant in a parser
     * 
     * @return int
     */
    public function getParsedTokensCount() : int
    {
        return $this->getIndex();
    }

    /**
     * Retrives the current token based on the index
     *
     * @return T
     */
    protected function currentToken() : T
    {
        if (!isset($this->tokens[$this->index]))
        {
            return null;
        }

        return $this->tokens[$this->index];
    }

    /**
     * Get the next token based on the current index
     * If the token does not exist because its off index "false" is returend.
     *
     * @param int             $i
     * @return T|false
     */
    protected function nextToken(int $i = 1) : T
    {
        if (!isset($this->tokens[$this->index + $i])) 
        {
            return false;
        }

        return $this->tokens[$this->index + $i];
    }

    /**
     * Skip the next parser token by updating the index.
     *
     * @param int            $times
     * @return void
     */
    protected function skipToken(int $times = 1)
    {
        $this->index += $times;
    }

    /**
     * Check if all tokens have been parsed trough
     *
     * @return bool
     */
    protected function parserIsDone() : bool
    {
        return $this->index >= $this->tokenCount;
    }

    /**
     * Return all remaining tokens 
     * 
     * @param string                    $skip
     * @return array[T]
     */
    protected function getRemainingTokens($skip = false)
    {
        $tokens = array();

        while (!$this->parserIsDone()) 
        {
            $tokens[] = $this->currentToken(); $this->skipToken();
        }

        if (!$skip)
        {
            $this->index -= count($tokens);
        }

        return $tokens;
    }

    /**
     * Get all tokens until the next token with given type
     *
     * @param string                     $type
     * @return array[T]
     */
    protected function getTokensUntil($type)
    {
        $tokens = array();

        if (!is_array($type))
        {
            $type = array($type);
        }

        while (!$this->parserIsDone() && !in_array($this->currentToken()->type, $type)) 
        {
            $tokens[] = $this->currentToken();
            $this->skipToken();
        }

        return $tokens;
    }

    /**
     * Retruns all tokens until the curren scope is closed again
     * 
     * @return array[T]
     */
    protected function getTokensUntilClosingScope($includeScope = false)
    {
        if ($this->currentToken()->type !== 'scopeOpen')
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }

        $tokens = array();

        // include the opening scope
        if ($includeScope)
        {
            $tokens[] = $this->currentToken();
        }
        
        $this->skipToken();

       
        $currentLevel = 0;

        while($this->currentToken() && !($this->currentToken()->type === 'scopeClose' && $currentLevel === 0))
        {
            if ($this->currentToken()->type === 'scopeOpen')
            {
                $currentLevel++;
            }

            if ($this->currentToken()->type === 'scopeClose')
            {
                $currentLevel--;
            }

            $tokens[] = $this->currentToken();
            $this->skipToken();
        }

        // include the closing scope
        if ($includeScope)
        {
            $tokens[] = $this->currentToken();
        }

        // skip the closing scope
        $this->skipToken();

        return $tokens;
    }

    /**
     * Create new unexpected token exception
     *
     * @param T                 $token
     * @return ContainerParserException
     */
    protected function errorUnexpectedToken($token)
    {
        return new ContainerParserException('unexpected "' . $token->type . '" given at line ' . $token->line);
    }

    /**
     * Starts a new parser with the remaining or given tokens
     * 
     * @param string                $parserClass
     * @param array                 $tokens
     * @param bool                  $skip
     * @return Node
     */
    protected function parseChild(string $parserClassName, $tokens = null, $skip = true)
    {
        if (is_null($tokens))
        {
            $tokens = $this->getRemainingTokens(); 
        }

        $parser = new $parserClass($tokens);
        $node = $parser->parse();

        // Update the current index based on the work of the child parser.
        if ($skip) 
        {
            $this->skipToken($parser->getParsedTokensCount());
        }

        // finally return the parsed node
        return $node;
    }

    /**
     * Start the code parser and return the result
     *
     * @return array
     */
    public function parse()
    {
        // reset the result
        $this->result = array();

        // start parsing trought the tokens
        while (!$this->parserIsDone()) 
        {
            $specialNode = $this->next();

            if ($specialNode instanceof Node) 
            {
                return $specialNode;
            }
        }

        // return the result after the loop is done
        return $this->node();
    }

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
    protected function node() : Node
    {
        throw new ContainerParserException("The container parser acts as base and should not be used on its own.");
    }

    /**
     * Parse the next token
     *
     * @return null|Node
     */
    protected function next() : Node
    {
        throw new ContainerParserException("The container parser acts as base and should not be used on its own.");
    }
}