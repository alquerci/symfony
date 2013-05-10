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
 * This class builds an if expression.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class Symfony_Component_Config_Definition_Builder_ExprBuilder
{
    protected $node;
    public $ifPart;
    public $thenPart;

    /**
     * Constructor
     *
     * @param Symfony_Component_Config_Definition_Builder_NodeDefinition $node The related node
     */
    public function __construct(Symfony_Component_Config_Definition_Builder_NodeDefinition $node)
    {
        $this->node = $node;
    }

    /**
     * Marks the expression as being always used.
     *
     * @param callable $then
     *
     * @return Symfony_Component_Config_Definition_Builder_ExprBuilder
     */
    public function always($then = null)
    {
        if (null !== $then) {
            assert(is_callable($then));
        }

        $this->ifPart = create_function('$v', 'return true;');

        if (null !== $then) {
            $this->thenPart = $then;
        }

        return $this;
    }

    /**
     * Sets a closure to use as tests.
     *
     * The default one tests if the value is true.
     *
     * @param callable $closure
     *
     * @return Symfony_Component_Config_Definition_Builder_ExprBuilder
     */
    public function ifTrue($closure = null)
    {
        if (null !== $closure) {
            assert(is_callable($closure));
        }

        if (null === $closure) {
            $closure = create_function('$v', 'return true === $v;');
        }

        $this->ifPart = $closure;

        return $this;
    }

    /**
     * Tests if the value is a string.
     *
     * @return Symfony_Component_Config_Definition_Builder_ExprBuilder
     */
    public function ifString()
    {
        $this->ifPart = create_function('$v', 'return is_string($v);');

        return $this;
    }

    /**
     * Tests if the value is null.
     *
     * @return Symfony_Component_Config_Definition_Builder_ExprBuilder
     */
    public function ifNull()
    {
        $this->ifPart = create_function('$v', 'return null === $v;');

        return $this;
    }

    /**
     * Tests if the value is an array.
     *
     * @return Symfony_Component_Config_Definition_Builder_ExprBuilder
     */
    public function ifArray()
    {
        $this->ifPart = create_function('$v', 'return is_array($v);');

        return $this;
    }

    /**
     * Tests if the value is in an array.
     *
     * @param array $array
     *
     * @return Symfony_Component_Config_Definition_Builder_ExprBuilder
     */
    public function ifInArray(array $array)
    {
        throw new LogicException('Not implemeted');

        // $this->ifPart = function($v) use ($array) { return in_array($v, $array, true); };

        return $this;
    }

    /**
     * Tests if the value is not in an array.
     *
     * @param array $array
     *
     * @return Symfony_Component_Config_Definition_Builder_ExprBuilder
     */
    public function ifNotInArray(array $array)
    {
        throw new LogicException('Not implemeted');

        //$this->ifPart = function($v) use ($array) { return !in_array($v, $array, true); };

        return $this;
    }

    /**
     * Sets the closure to run if the test pass.
     *
     * @param callable $closure
     *
     * @return Symfony_Component_Config_Definition_Builder_ExprBuilder
     */
    public function then($closure)
    {
        assert(is_callable($closure));

        $this->thenPart = $closure;

        return $this;
    }

    /**
     * Sets a closure returning an empty array.
     *
     * @return Symfony_Component_Config_Definition_Builder_ExprBuilder
     */
    public function thenEmptyArray()
    {
        $this->thenPart = create_function('$v', 'return array();');

        return $this;
    }

    /**
     * Sets a closure marking the value as invalid at validation time.
     *
     * if you want to add the value of the node in your message just use a %s placeholder.
     *
     * @param string $message
     *
     * @return Symfony_Component_Config_Definition_Builder_ExprBuilder
     *
     * @throws InvalidArgumentException
     */
    public function thenInvalid($message)
    {
        $this->thenPart = create_function('$v', 'throw new InvalidArgumentException(sprintf("%s is invalid", json_encode($v)));');

        return $this;
    }

    /**
     * Sets a closure unsetting this key of the array at validation time.
     *
     * @return Symfony_Component_Config_Definition_Builder_ExprBuilder
     *
     * @throws Symfony_Component_Config_Definition_Exception_UnsetKeyException
     */
    public function thenUnset()
    {
        $this->thenPart = create_function ('$v', 'throw new Symfony_Component_Config_Definition_Exception_UnsetKeyException(\'Unsetting key\');');

        return $this;
    }

    /**
     * Returns the related node
     *
     * @return Symfony_Component_Config_Definition_Builder_NodeDefinition
     *
     * @throws RuntimeException
     */
    public function end()
    {
        if (null === $this->ifPart) {
            throw new RuntimeException('You must specify an if part.');
        }
        if (null === $this->thenPart) {
            throw new RuntimeException('You must specify a then part.');
        }

        return $this->node;
    }

    /**
     * Builds the expressions.
     *
     * @param Symfony_Component_Config_Definition_Builder_ExprBuilder[] $expressions An array of ExprBuilder instances to build
     *
     * @return array
     */
    public static function buildExpressions(array $expressions)
    {
        foreach ($expressions as $k => $expr) {
            if ($expr instanceof Symfony_Component_Config_Definition_Builder_ExprBuilder) {
                self::$_buildExpressionsCB_expr = $expr;
                $expressions[$k] = array('Symfony_Component_Config_Definition_Builder_ExprBuilder', '_buildExpressionsCB');
            }
        }

        return $expressions;
    }
    private static $_buildExpressionsCB_expr;
    public static function _buildExpressionsCB($v)
    {
        return call_user_func(self::$_buildExpressionsCB_expr->ifPart, $v) ? call_user_func(self::$_buildExpressionsCB_expr->thenPart, $v) : $v;
    }
}
