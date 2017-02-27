<?php 
namespace ClanCats\Container\ContainerParser;

use ClanCats\Container\{
    Exceptions\ContainerLexerException
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
     * The lexer token types
     */
    const TOKEN_STRING = 0;
    const TOKEN_NUMBER = 1;
    const TOKEN_BOOL_TRUE = 2;
    const TOKEN_BOOL_FALSE = 3;
    const TOKEN_NULL = 4;
    const TOKEN_DEPENDENCY = 5;
    const TOKEN_PARAMETER = 6;
    const TOKEN_COMMENT = 7;
    const TOKEN_LINE = 8;
    const TOKEN_SPACE = 9;
    const TOKEN_ASSIGN = 10;
    const TOKEN_IMPORT = 11;
    const TOKEN_USE = 12;
    const TOKEN_BRACE_OPEN = 13;
    const TOKEN_BRACE_CLOSE = 14;
    const TOKEN_MINUS = 15;
    const TOKEN_IDENTIFIER = 16;

    /**
     * Token map
     *
     * @var array
     */
    protected $tokenMap = 
    [
        // strings
        '/^"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"/' => static::TOKEN_STRING,
        "/^'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'/" => static::TOKEN_STRING,

        // numbers
        "/^-?(([1-9][0-9]*\.?[0-9]*)|(\.[0-9]+))([Ee][+-]?[0-9]+)?/" => static::TOKEN_NUMBER,

        // bool
        "/^(yes)/" => static::TOKEN_BOOL_TRUE,
        "/^(no)/" => static::TOKEN_BOOL_FALSE,

        // null
        "/^(null)/" => static::TOKEN_NULL,

        // variables
        "/^(@\w+)/" => static::TOKEN_DEPENDENCY,
        "/^(:\w+)/" => static::TOKEN_PARAMETER,

        // comments
        "/^\/\/.*/" => static::TOKEN_COMMENT,

        // markup
        "/^(\r\n|\n|\r)/" => static::TOKEN_LINE,
        "/^(\s)/" => static::TOKEN_SPACE,        

        // keywords
        "/^(use )/" => static::TOKEN_USE,
        "/^(import )/" => static::TOKEN_IMPORT,

        // scope
        "/^(\()/" => static::TOKEN_BRACE_OPEN,
        "/^(\))/" => static::TOKEN_BRACE_CLOSE,
        
        // syntax
        "/^(\:)/" => static::TOKEN_ASSIGN,
        "/^(\-)/" => static::TOKEN_MINUS,
        "/^(\,)/" => static::TOKEN_SEPERATOR,

        // ids
        "/^([\w-\/\.]+)/" => static::TOKEN_IDENTIFIER,
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
        $this->code = trim(preg_replace('/\s+/', ' ', $code));

        // we need to know the codes length
        $this->length = strlen($this->code);
    }

    /**
     * Get the codes lenght
     *
     * @return int
     */
    public function length() : int
    {
        return $this->length;
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
                if ($token === static::TOKEN_LINE) {
                    $this->line++;
                }

                $this->offset += strlen($matches[0]);

                return new Token($token, $matches[0], $this->line + 1);
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
                $token->type === static::TOKEN_LINE && 
                isset($tokens[count($tokens) - 1]) && 
                $tokens[count($tokens) - 1]->type === static::TOKEN_LINE
            ) {
                continue;
            }

            $tokens[] = $token;
        }

        return $tokens;
    }
}
