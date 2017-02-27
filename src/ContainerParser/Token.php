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
     * Get the tokens value as php native type.
     *
     * @return mixed
     */
    public function getValue()
    {
        $value = $this->value;

        switch ($this->type) 
        {
            case ContainerLexer::TOKEN_BOOL_TRUE:
                $value = true;
                break;

            case ContainerLexer::TOKEN_BOOL_FALSE:
                $value = false;
                break;

            case ContainerLexer::TOKEN_STRING:
                $value = str_replace("\\", "", substr($value, 1, -1));
                break;

            case ContainerLexer::TOKEN_NUMBER:
                $value = $value + 0;
                break;

            case ContainerLexer::TOKEN_NULL:
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
        $this->type === ContainerLexer::TOKEN_STRING ||
        $this->type === ContainerLexer::TOKEN_NUMBER ||
        $this->type === ContainerLexer::TOKEN_NULL ||
        $this->type === ContainerLexer::TOKEN_BOOL_TRUE ||
        $this->type === ContainerLexer::TOKEN_BOOL_FALSE;
    }
}

