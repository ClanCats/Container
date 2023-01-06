<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2023 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
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
     * @var array<Token>
     */
    protected array $tokens = [];

    /**
     * The current index while parsing trough the tokens
     *
     * @var int
     */
    protected int $index = 0;

    /**
     * The number of tokens to parse
     *
     * @var int
     */
    protected int $tokenCount = 0;

    /**
     * The constructor
     * You have to initialize the Parser with an array of lexed tokens.
     *
     * @param array<T>             $tokens
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
     * @param array<T>             $tokens
     * @return void
     */
    public function setTokens(array $tokens) : void
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
     * @return array<T>
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
            throw new ContainerParserException("The current token is out of the available tokens range.");
        }

        return $this->tokens[$this->index];
    }

    /**
     * Get the next token based on the current index
     * If the token does not exist because its off index "false" is returend.
     *
     * @param int             $i
     * @return T|null
     */
    protected function nextToken(int $i = 1) : ?T
    {
        if (!isset($this->tokens[$this->index + $i])) {
            return null;
        }

        return $this->tokens[$this->index + $i];
    }

    /**
     * Skip the next parser token by updating the index.
     *
     * @param int            $times
     * @return void
     */
    protected function skipToken(int $times = 1) : void
    {
        $this->index += $times;
    }

    /**
     * Skip all upcoming tokens of the given type
     *
     * @param array<int>         $types 
     * @return void
     */
    protected function skipTokenOfType(array $types = []) : void
    {
        while ((!$this->parserIsDone()) && in_array($this->currentToken()->getType(), $types))
        {
            $this->skipToken();
        }
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
     * @param bool $skip
     *
     * @return array<T>
     */
    protected function getRemainingTokens(bool $skip = false) : array
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
     * @param int                   $type
     * @param bool                  $ignoreScopes if enabled, if the token is inside a new scope its ignored. 
     * @return array<T>
     */
    protected function getTokensUntil(int $type, bool $ignoreScopes = false) : array
    {
        $tokens = [];
        $type = [$type];

        while (!$this->parserIsDone() && !in_array($this->currentToken()->getType(), $type)) 
        {
            // if scopes should be ignored, we need to check if the current 
            // token opens one an then get all tokens inside the scope
            if ($ignoreScopes && $this->currentToken()->isType(T::TOKEN_SCOPE_OPEN)) 
            {
                foreach($this->getTokensUntilClosingScope(true, T::TOKEN_SCOPE_OPEN, T::TOKEN_SCOPE_CLOSE) as $token) {
                    $tokens[] = $token;
                }
            } else {
                $tokens[] = $this->currentToken();
                $this->skipToken();
            }
        }

        return $tokens;
    }

    /**
     * Retruns all tokens until the opened scope is closed again
     * 
     * @return array<T>
     */
    protected function getTokensUntilClosingScope(bool $includeScope = false, int $openToken = T::TOKEN_BRACE_OPEN, int $closeToken = T::TOKEN_BRACE_CLOSE) : array
    {
        if ($this->currentToken()->getType() !== $openToken)
        {
            throw $this->errorUnexpectedToken($this->currentToken());
        }

        $tokens = array();

        // include the opening scope
        if ($includeScope) {
            $tokens[] = $this->currentToken();
        }
        
        $this->skipToken();

       
        $currentLevel = 0;

        while(!$this->parserIsDone() && !($this->currentToken()->getType() === $closeToken && $currentLevel === 0))
        {
            if ($this->currentToken()->getType() === $openToken)
            {
                $currentLevel++;
            }

            if ($this->currentToken()->getType() === $closeToken)
            {
                $currentLevel--;
            }

            $tokens[] = $this->currentToken();
            $this->skipToken();
        }

        // include the closing scope
        if ($includeScope) {
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
    protected function errorUnexpectedToken($token) : ContainerParserException
    {
        $class = new \ReflectionClass($token);
        $constants = array_flip($class->getConstants());

        return new ContainerParserException('unexpected "' . $constants[$token->getType()] . '" given at line ' . $token->getLine() . ' in file ' . $token->getFilename());
    }

    /**
     * Create new unexpected token exception
     *
     * @param string                 $message
     * @return ContainerParserException
     */
    protected function errorParsing(string $message) : ContainerParserException
    {
        $token = $this->currentToken();
        return new ContainerParserException($message . '" given at line ' . $token->getLine() . ' in file ' . $token->getFilename());
    }

    /**
     * Starts a new parser with the remaining or given tokens
     * 
     * @template CLASS
     * @param class-string<CLASS>   $parserClassName
     * @param array<T>|null         $tokens
     * @param bool                  $skip
     * @return Node
     */
    protected function parseChild(string $parserClassName, ?array $tokens = null, $skip = true) : Node
    {
        if (is_null($tokens))
        {
            $tokens = $this->getRemainingTokens(); 
        }

        $parser = new $parserClassName($tokens);
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
     * @return Node
     */
    public function parse() : Node
    {
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
    protected function next() : ?Node
    {
        throw new ContainerParserException("The container parser acts as base and should not be used on its own.");
    }
}
