<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2017 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser;

use ClanCats\Container\{
    Exceptions\ContainerLexerException,
    
    // Alias the token as T
    ContainerParser\Token as T
};

class ContainerLexer
{
    /**
     * The current code we want to iterate trough
     *
     * @var string
     */
    protected $code = null;

    /**
     * The code lenght to iterate
     *
     * @var int
     */
    protected $length = 0;

    /**
     * The current string offset in the code
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * The current line
     *
     * @var int
     */
    protected $line = 0;

    /**
     * Token map
     *
     * @var array
     */
    protected $tokenMap = 
    [
        // strings
        '/^"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"/' => T::TOKEN_STRING,
        "/^'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'/" => T::TOKEN_STRING,

        // numbers
        "/^[+-]?([0-9]*[.])?[0-9]+/" => T::TOKEN_NUMBER,

        // bool
        "/^(true)/" => T::TOKEN_BOOL_TRUE,
        "/^(false)/" => T::TOKEN_BOOL_FALSE,

        // null
        "/^(null)/" => T::TOKEN_NULL,

        // container objects
        "/^(@[\w-\/\.]+)/" => T::TOKEN_DEPENDENCY,
        "/^(:[\w-\/\.]+)/" => T::TOKEN_PARAMETER,

        // comments
        "/^\/\/.*/" => T::TOKEN_COMMENT,
        "/^#.*/" => T::TOKEN_COMMENT,
        "/^\/\*(?:.|[\r\n])*?\*\//" => T::TOKEN_COMMENT,

        // markup
        "/^(\r\n|\n|\r)/" => T::TOKEN_LINE,
        "/^(\s)/" => T::TOKEN_SPACE,        

        // keywords
        "/^(use )/" => T::TOKEN_USE,
        "/^(import )/" => T::TOKEN_IMPORT,
        "/^(override )/" => T::TOKEN_OVERRIDE,

        // scope
        "/^(\()/" => T::TOKEN_BRACE_OPEN,
        "/^(\))/" => T::TOKEN_BRACE_CLOSE,
        "/^(\{)/" => T::TOKEN_SCOPE_OPEN,
        "/^(\})/" => T::TOKEN_SCOPE_CLOSE,
        
        // syntax
        "/^(\:)/" => T::TOKEN_ASSIGN,
        "/^(\-)/" => T::TOKEN_MINUS,
        "/^(\,)/" => T::TOKEN_SEPERATOR,
        "/^(\?)/" => T::TOKEN_PROTOTYPE,

        // ids
        "/^([\w-\/\\\\.]+)/" => T::TOKEN_IDENTIFIER,
    ];

    /**
     * The constructor
     *
     * @var string         $code
     * @return void
     */
    public function __construct(string $code)
    {
        // there is never a need for tabs or multiple whitespaces 
        // so we remove them before assigning the code
        $this->code = trim(preg_replace("/[ \t]+/", ' ', $code));

        // we need to know the codes length
        $this->length = strlen($this->code);
    }

    /**
     * Get the current code
     *
     * @return string
     */
    public function code() : string
    {
        return $this->code;
    }

    /**
     * Get the next token from our code
     *
     * @throws ContainerLexerException
     * 
     * @return string|false Returns `false` if everything has been parsed and we are done.
     */
    protected function next()
    {
        if ($this->offset >= $this->length) 
        {
            return false;
        }

        foreach ($this->tokenMap as $regex => $token) 
        {
            if (preg_match($regex, substr($this->code, $this->offset), $matches)) 
            {
                if ($token === T::TOKEN_LINE) {
                    $this->line++;
                }

                $this->offset += strlen($matches[0]);

                return new T($this->line + 1, $token, $matches[0]);
            }
        }

        throw new ContainerLexerException(sprintf('Unexpected character "%s" on line %s', $this->code[$this->offset], $this->line));
    }

    /**
     * Start the lexer and retrieve all resulting tokens.
     *
     * @return array[Token]
     */
    public function tokens() : array
    {
        $tokens = [];

        while ($token = $this->next()) 
        {
            // skip doublicated linebreaks
            if (
                $token->getType() === T::TOKEN_LINE && 
                isset($tokens[count($tokens) - 1]) && 
                $tokens[count($tokens) - 1]->getType() === T::TOKEN_LINE
            ) {
                continue;
            }

            $tokens[] = $token;
        }

        return $tokens;
    }
}
