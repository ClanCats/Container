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
    const TOKEN_SEPERATOR = 16;
    const TOKEN_IDENTIFIER = 17;

    /**
     * Token map
     *
     * @var array
     */
    protected $tokenMap = 
    [
        // strings
        '/^"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"/' => self::TOKEN_STRING,
        "/^'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'/" => self::TOKEN_STRING,

        // numbers
        "/^-?(([1-9][0-9]*\.?[0-9]*)|(\.[0-9]+))([Ee][+-]?[0-9]+)?/" => self::TOKEN_NUMBER,

        // bool
        "/^(yes)/" => self::TOKEN_BOOL_TRUE,
        "/^(no)/" => self::TOKEN_BOOL_FALSE,

        // null
        "/^(null)/" => self::TOKEN_NULL,

        // variables
        "/^(@\w+)/" => self::TOKEN_DEPENDENCY,
        "/^(:\w+)/" => self::TOKEN_PARAMETER,

        // comments
        "/^\/\/.*/" => self::TOKEN_COMMENT,

        // markup
        "/^(\r\n|\n|\r)/" => self::TOKEN_LINE,
        "/^(\s)/" => self::TOKEN_SPACE,        

        // keywords
        "/^(use )/" => self::TOKEN_USE,
        "/^(import )/" => self::TOKEN_IMPORT,

        // scope
        "/^(\()/" => self::TOKEN_BRACE_OPEN,
        "/^(\))/" => self::TOKEN_BRACE_CLOSE,
        
        // syntax
        "/^(\:)/" => self::TOKEN_ASSIGN,
        "/^(\-)/" => self::TOKEN_MINUS,
        "/^(\,)/" => self::TOKEN_SEPERATOR,

        // ids
        "/^([\w-\/\.]+)/" => self::TOKEN_IDENTIFIER,
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
                if ($token === self::TOKEN_LINE) {
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
                $token->type === self::TOKEN_LINE && 
                isset($tokens[count($tokens) - 1]) && 
                $tokens[count($tokens) - 1]->type === self::TOKEN_LINE
            ) {
                continue;
            }

            $tokens[] = $token;
        }

        return $tokens;
    }
}
