<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2026 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser\Nodes;

use ClanCats\Container\ContainerParser\Token;

/**
 * The materialization of a generic into a concrete service.
 *
 * Holds the concrete service name to create, the referenced generic name and
 * the ordered type argument tokens supplied between the angle brackets. At
 * interpret time the `GenericTemplateExpander` zips these arguments with the
 * generics type parameters and substitutes them into a deep clone of the
 * generics parsed template.
 */
class GenericMaterializationNode extends BaseNode
{
    /**
     * The concrete service name to create
     *
     * @var string
     */
    protected string $name;

    /**
     * The name of the generic being materialized
     *
     * @var string
     */
    protected string $genericName;

    /**
     * The ordered type argument tokens
     *
     * @var array<Token>
     */
    protected array $typeArguments = [];

    /**
     * Does the resulting service override an existing one?
     *
     * @var bool
     */
    protected bool $isOverride = false;

    /**
     * Get the concrete service name
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Set the concrete service name
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    /**
     * Get the referenced generic name
     */
    public function getGenericName() : string
    {
        return $this->genericName;
    }

    /**
     * Set the referenced generic name
     */
    public function setGenericName(string $genericName) : void
    {
        $this->genericName = $genericName;
    }

    /**
     * Add a type argument token
     */
    public function addTypeArgument(Token $token) : void
    {
        $this->typeArguments[] = $token;
    }

    /**
     * Get all type argument tokens in order
     *
     * @return array<Token>
     */
    public function getTypeArguments() : array
    {
        return $this->typeArguments;
    }

    /**
     * Get if the resulting service overrides an existing one
     */
    public function isOverride() : bool
    {
        return $this->isOverride;
    }

    /**
     * Set if the resulting service overrides an existing one
     */
    public function setIsOverride(bool $isOverride) : void
    {
        $this->isOverride = $isOverride;
    }
}
