<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This class provides a fluent interface for defining a node.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
abstract class Symfony_Component_Config_Definition_Builder_NodeDefinition implements Symfony_Component_Config_Definition_Builder_NodeParentInterface
{
    protected $name;
    protected $normalization;
    protected $validation;
    protected $defaultValue;
    protected $default;
    protected $required;
    protected $merge;
    protected $allowEmptyValue;
    protected $nullEquivalent;
    protected $trueEquivalent;
    protected $falseEquivalent;
    /**
     * @var Symfony_Component_Config_Definition_Builder_NodeParentInterface|Symfony_Component_Config_Definition_NodeInterface
     */
    protected $parent;
    protected $attributes = array();

    /**
     * Constructor
     *
     * @param string              $name   The name of the node
     * @param Symfony_Component_Config_Definition_Builder_NodeParentInterface $parent The parent
     */
    public function __construct($name, Symfony_Component_Config_Definition_Builder_NodeParentInterface $parent = null)
    {
        $this->parent = $parent;
        $this->name = $name;
        $this->default = false;
        $this->required = false;
        $this->trueEquivalent = true;
        $this->falseEquivalent = false;
    }

    /**
     * Sets the parent node.
     *
     * @param Symfony_Component_Config_Definition_Builder_NodeParentInterface $parent The parent
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeDefinition
     */
    public function setParent(Symfony_Component_Config_Definition_Builder_NodeParentInterface $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Sets info message.
     *
     * @param string $info The info text
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeDefinition
     */
    public function info($info)
    {
        return $this->attribute('info', $info);
    }

    /**
     * Sets example configuration.
     *
     * @param string|array $example
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeDefinition
     */
    public function example($example)
    {
        return $this->attribute('example', $example);
    }

    /**
     * Sets an attribute on the node.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeDefinition
     */
    public function attribute($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Returns the parent node.
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeParentInterface The builder of the parent node
     */
    public function end()
    {
        return $this->parent;
    }

    /**
     * Creates the node.
     *
     * @param Boolean $forceRootNode Whether to force this node as the root node
     *
     * @return Symfony_Component_Config_Definition_NodeInterface
     */
    public function getNode($forceRootNode = false)
    {
        if ($forceRootNode) {
            $this->parent = null;
        }

        if (null !== $this->normalization) {
            $this->normalization->before = Symfony_Component_Config_Definition_Builder_ExprBuilder::buildExpressions($this->normalization->before);
        }

        if (null !== $this->validation) {
            $this->validation->rules = Symfony_Component_Config_Definition_Builder_ExprBuilder::buildExpressions($this->validation->rules);
        }

        $node = $this->createNode();
        $node->setAttributes($this->attributes);

        return $node;
    }

    /**
     * Sets the default value.
     *
     * @param mixed $value The default value
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeDefinition
     */
    public function defaultValue($value)
    {
        $this->default = true;
        $this->defaultValue = $value;

        return $this;
    }

    /**
     * Sets the node as required.
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeDefinition
     */
    public function isRequired()
    {
        $this->required = true;

        return $this;
    }

    /**
     * Sets the equivalent value used when the node contains null.
     *
     * @param mixed $value
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeDefinition
     */
    public function treatNullLike($value)
    {
        $this->nullEquivalent = $value;

        return $this;
    }

    /**
     * Sets the equivalent value used when the node contains true.
     *
     * @param mixed $value
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeDefinition
     */
    public function treatTrueLike($value)
    {
        $this->trueEquivalent = $value;

        return $this;
    }

    /**
     * Sets the equivalent value used when the node contains false.
     *
     * @param mixed $value
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeDefinition
     */
    public function treatFalseLike($value)
    {
        $this->falseEquivalent = $value;

        return $this;
    }

    /**
     * Sets null as the default value.
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeDefinition
     */
    public function defaultNull()
    {
        return $this->defaultValue(null);
    }

    /**
     * Sets true as the default value.
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeDefinition
     */
    public function defaultTrue()
    {
        return $this->defaultValue(true);
    }

    /**
     * Sets false as the default value.
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeDefinition
     */
    public function defaultFalse()
    {
        return $this->defaultValue(false);
    }

    /**
     * Sets an expression to run before the normalization.
     *
     * @return ExprBuilder
     */
    public function beforeNormalization()
    {
        return $this->normalization()->before();
    }

    /**
     * Denies the node value being empty.
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeDefinition
     */
    public function cannotBeEmpty()
    {
        $this->allowEmptyValue = false;

        return $this;
    }

    /**
     * Sets an expression to run for the validation.
     *
     * The expression receives the value of the node and must return it. It can
     * modify it.
     * An exception should be thrown when the node is not valid.
     *
     * @return ExprBuilder
     */
    public function validate()
    {
        return $this->validation()->rule();
    }

    /**
     * Sets whether the node can be overwritten.
     *
     * @param Boolean $deny Whether the overwriting is forbidden or not
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeDefinition
     */
    public function cannotBeOverwritten($deny = true)
    {
        $this->merge()->denyOverwrite($deny);

        return $this;
    }

    /**
     * Gets the builder for validation rules.
     *
     * @return Symfony_Component_Config_Definition_Builder_ValidationBuilder
     */
    protected function validation()
    {
        if (null === $this->validation) {
            $this->validation = new Symfony_Component_Config_Definition_Builder_ValidationBuilder($this);
        }

        return $this->validation;
    }

    /**
     * Gets the builder for merging rules.
     *
     * @return Symfony_Component_Config_Definition_Builder_MergeBuilder
     */
    protected function merge()
    {
        if (null === $this->merge) {
            $this->merge = new Symfony_Component_Config_Definition_Builder_MergeBuilder($this);
        }

        return $this->merge;
    }

    /**
     * Gets the builder for normalization rules.
     *
     * @return Symfony_Component_Config_Definition_Builder_NormalizationBuilder
     */
    protected function normalization()
    {
        if (null === $this->normalization) {
            $this->normalization = new Symfony_Component_Config_Definition_Builder_NormalizationBuilder($this);
        }

        return $this->normalization;
    }

    /**
     * Instantiate and configure the node according to this definition
     *
     * @return Symfony_Component_Config_Definition_NodeInterface $node The node instance
     *
     * @throws Symfony_Component_Config_Definition_Exception_InvalidDefinitionException When the definition is invalid
     */
    abstract protected function createNode();

}
