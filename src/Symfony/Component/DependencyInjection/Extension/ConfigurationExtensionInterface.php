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
 * ConfigurationExtensionInterface is the interface implemented by container extension classes.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Symfony_Component_DependencyInjection_Extension_ConfigurationExtensionInterface
{
    /**
     * Returns extension configuration
     *
     * @param array            $config    $config    An array of configuration values
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container A ContainerBuilder instance
     *
     * @return Symfony_Component_Config_Definition_ConfigurationInterface|null The configuration or null
     */
    public function getConfiguration(array $config, Symfony_Component_DependencyInjection_ContainerBuilder $container);
}
