<?php 
namespace ClanCats\Container\ContainerParser;

use ClanCats\Container\{
    Exceptions\ContainerLexerException
};

class Token
{
    /**
     * The type of this token.
     *
     * @var int
     */
    protected $type;

    /**
     * The value of this token.
     *
     * @var mixed
     */
    protected $value;

    /**
     * The line this token has been found in the code.
     *
     * @var int
     */
    protected $line = 0;

    /**
     * The token types
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
     * The constructor
     *
     * @param int       $line The line the token is on.
     * @param int       $type The type of the token represented by an int.
     * @param mixed     $value The Value of the token.
     * @return void
     */
    public function __construct(int $line, int $type, $value)
    {
        $this->line = $line;
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Get the line of the token.
     * 
     * @return int
     */
    public function getLine() : int
    {
        return $this->line;
    }

    /**
     * Get the type of the token represented as int.
     * 
     * @return int
     */
    public function getType() : int
    {
        return $this->line;
    }

    /**
     * Get the tokens value as php native type.
     *
     * @return mixed
     */
    public function getValue()
    {
        $value = $this->value;

        switch ($this->type) 
        {
            case self::TOKEN_BOOL_TRUE:
                $value = true;
                break;

            case self::TOKEN_BOOL_FALSE:
                $value = false;
                break;

            case self::TOKEN_STRING:
                $value = str_replace("\\", "", substr($value, 1, -1));
                break;

            case self::TOKEN_NUMBER:
                $value = $value + 0;
                break;

            case self::TOKEN_NULL:
                $value = null;
                break;
        }

        return $value;
    }

    /**
     * Is this a value token?
     *
     * @return bool
     */
    public function isValue() : bool
    {
        return
        $this->type === self::TOKEN_STRING ||
        $this->type === self::TOKEN_NUMBER ||
        $this->type === self::TOKEN_NULL ||
        $this->type === self::TOKEN_BOOL_TRUE ||
        $this->type === self::TOKEN_BOOL_FALSE;
    }
}
