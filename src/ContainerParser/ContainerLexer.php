<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2024 Mario Döring
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
    protected string $code;

    /**
     * The code lenght to iterate
     *
     * @var int
     */
    protected int $length = 0;

    /**
     * The current string offset in the code
     *
     * @var int
     */
    protected int $offset = 0;

    /**
     * The current line
     *
     * @var int
     */
    protected int $line = 0;

    /**
     * The filename 
     * This is mainly used for error messages. 
     *
     * @var string
     */
    protected string $filename = 'unknown';

    /**
     * Token map
     *
     * @var array<string, int>
     */
    protected array $tokenMap = 
    [
        // numbers
        "/^[+\-]?([0-9]*[.])?[0-9]+/" => T::TOKEN_NUMBER,

        // bool
        "/^(true)/" => T::TOKEN_BOOL_TRUE,
        "/^(false)/" => T::TOKEN_BOOL_FALSE,

        // null
        "/^(null)/" => T::TOKEN_NULL,

        // container objects
        "/^(@[\w\-\/\.]+)/" => T::TOKEN_DEPENDENCY,
        "/^(:[\w\-\/\.]+)/" => T::TOKEN_PARAMETER,

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
        "/^(\=)/" => T::TOKEN_EQUAL,
        "/^(\,)/" => T::TOKEN_SEPERATOR,
        "/^(\?)/" => T::TOKEN_OPTIONAL,

        // ids
        "/^([\w\-\/\\\\.]+)::class/" => T::TOKEN_CLASS_NAME,
        "/^([\w\-\/\\\\.]+)/" => T::TOKEN_IDENTIFIER,
    ];

    /**
     * The constructor
     *
     * @param string         $code The code to be tokenized
     * @param string         $filename The name of the file for error reporting.
     * @return void
     */
    public function __construct(string $code, ?string $filename = null)
    {
        // there is never a need for tabs or multiple whitespaces 
        // so we remove them before assigning the code
        $this->code = trim(preg_replace("/[ \t]+/", ' ', $code) ?? '');

        // we need to know the codes length
        $this->length = strlen($this->code);

        // assign the filename
        if ($filename) {
            $this->filename = $filename;
        }
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
     * @return T|false Returns `false` if everything has been parsed and we are done.
     */
    protected function next()
    {
        if ($this->offset >= $this->length) 
        {
            return false;
        }

        // parse string seperatly
        $char = $this->code[$this->offset];
        if ($char === '"' || $char === "'") 
        {
            $string = $char;
            $this->offset++;

            while ($this->offset < $this->length) 
            {
                if ($this->code[$this->offset] === $char && $this->code[$this->offset - 1] !== "\\") {
                    $string .= $char;
                    break;
                }

                // broperly count linebreaks
                if ($this->code[$this->offset] === "\n") {
                    $this->line++;
                }

                $string .= $this->code[$this->offset];
                $this->offset++;
            }

            $this->offset++;

            return new T($this->line + 1, T::TOKEN_STRING, $string, $this->filename);
        }

        foreach ($this->tokenMap as $regex => $token) 
        {
            if (preg_match($regex, substr($this->code, $this->offset), $matches)) 
            {
                if ($token === T::TOKEN_LINE) {
                    $this->line++;
                }
                
                // make sure to also count multiline comments
                if ($token === T::TOKEN_COMMENT) {
                    $this->line+= substr_count($matches[0], "\n");
                }

                $this->offset += strlen($matches[0]);

                return new T($this->line + 1, $token, $matches[0], $this->filename);
            }
        }

        throw new ContainerLexerException(sprintf('Unexpected character "%s" on line %s in file %s', $this->code[$this->offset], $this->line, $this->filename));
    }

    /**
     * Start the lexer and retrieve all resulting tokens.
     *
     * @return array<Token>
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
