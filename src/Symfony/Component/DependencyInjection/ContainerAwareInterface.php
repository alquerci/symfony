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
 * ContainerAwareInterface should be implemented by classes that depends on a Container.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface Symfony_Component_DependencyInjection_ContainerAwareInterface
{
    /**
     * Sets the Container.
     *
     * @param Symfony_Component_DependencyInjection_ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(Symfony_Component_DependencyInjection_ContainerInterface $container = null);
}
