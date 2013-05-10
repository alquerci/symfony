<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface Symfony_Component_DependencyInjection_Extension_PrependExtensionInterface
{
    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container
     */
    public function prepend(Symfony_Component_DependencyInjection_ContainerBuilder $container);
}
