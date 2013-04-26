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
 * Holds read-only parameters.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class Symfony_Component_DependencyInjection_ParameterBag_FrozenParameterBag extends Symfony_Component_DependencyInjection_ParameterBag_ParameterBag
{
    /**
     * Constructor.
     *
     * For performance reasons, the constructor assumes that
     * all keys are already lowercased.
     *
     * This is always the case when used internally.
     *
     * @param array $parameters An array of parameters
     *
     * @api
     */
    public function __construct(array $parameters = array())
    {
        $this->parameters = $parameters;
        $this->resolved = true;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function clear()
    {
        throw new Symfony_Component_DependencyInjection_Exception_LogicException('Impossible to call clear() on a frozen ParameterBag.');
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function add(array $parameters)
    {
        throw new Symfony_Component_DependencyInjection_Exception_LogicException('Impossible to call add() on a frozen ParameterBag.');
    }

    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function set($name, $value)
    {
        throw new Symfony_Component_DependencyInjection_Exception_LogicException('Impossible to call set() on a frozen ParameterBag.');
    }
}
