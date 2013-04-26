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
 * A simple implementation of ContainerAwareInterface.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
abstract class Symfony_Component_DependencyInjection_ContainerAware implements Symfony_Component_DependencyInjection_ContainerAwareInterface
{
    /**
     * @var Symfony_Component_DependencyInjection_ContainerInterface
     *
     * @api
     */
    protected $container;

    /**
     * Sets the Container associated with this Controller.
     *
     * @param Symfony_Component_DependencyInjection_ContainerInterface $container A ContainerInterface instance
     *
     * @api
     */
    public function setContainer(Symfony_Component_DependencyInjection_ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
