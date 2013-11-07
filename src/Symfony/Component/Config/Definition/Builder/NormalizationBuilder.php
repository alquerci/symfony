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
 * This class builds normalization conditions.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Config_Definition_Builder_NormalizationBuilder
{
    protected $node;
    public $before;
    public $remappings;

    /**
     * Constructor
     *
     * @param Symfony_Component_Config_Definition_Builder_NodeDefinition $node The related node
     */
    public function __construct(Symfony_Component_Config_Definition_Builder_NodeDefinition $node)
    {
        $this->node = $node;
        $this->keys = false;
        $this->remappings = array();
        $this->before = array();
    }

    /**
     * Registers a key to remap to its plural form.
     *
     * @param string $key    The key to remap
     * @param string $plural The plural of the key in case of irregular plural
     *
     * @return Symfony_Component_Config_Definition_Builder_NormalizationBuilder
     */
    public function remap($key, $plural = null)
    {
        $this->remappings[] = array($key, null === $plural ? $key.'s' : $plural);

        return $this;
    }

    /**
     * Registers a closure to run before the normalization or an expression builder to build it if null is provided.
     *
     * @param callable $closure
     *
     * @return Symfony_Component_Config_Definition_Builder_ExprBuilder|Symfony_Component_Config_Definition_Builder_NormalizationBuilder
     */
    public function before($closure = null)
    {
        if (null !== $closure) {
            if (!is_callable($closure)) {
                $e = new Exception();
                $trace = $e->getTrace();
                trigger_error(sprintf('Argument 1 passed to %s() must be callable, %s given, called in %s on line %s',
                    __METHOD__,
                    gettype($closure),
                    $trace[0]['file'],
                    $trace[0]['line']
                ), E_USER_ERROR);
            }
        }

        if (null !== $closure) {
            $this->before[] = $closure;

            return $this;
        }

        return $this->before[] = new Symfony_Component_Config_Definition_Builder_ExprBuilder($this->node);
    }
}
