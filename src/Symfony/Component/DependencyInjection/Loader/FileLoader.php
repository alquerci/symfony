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
 * FileLoader is the abstract class used by all built-in loaders that are file based.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Symfony_Component_DependencyInjection_Loader_FileLoader extends Symfony_Component_Config_Loader_FileLoader
{
    protected $container;

    /**
     * Constructor.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container A ContainerBuilder instance
     * @param Symfony_Component_Config_FileLocatorInterface      $locator   A FileLocator instance
     */
    public function __construct(Symfony_Component_DependencyInjection_ContainerBuilder $container, Symfony_Component_Config_FileLocatorInterface $locator)
    {
        $this->container = $container;

        parent::__construct($locator);
    }
}
