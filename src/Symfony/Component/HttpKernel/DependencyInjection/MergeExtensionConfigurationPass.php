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
 * Ensures certain extensions are always loaded.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class Symfony_Component_HttpKernel_DependencyInjection_MergeExtensionConfigurationPass extends Symfony_Component_DependencyInjection_Compiler_MergeExtensionConfigurationPass
{
    private $extensions;

    public function __construct(array $extensions)
    {
        $this->extensions = $extensions;
    }

    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        foreach ($this->extensions as $extension) {
            if (!count($container->getExtensionConfig($extension))) {
                $container->loadFromExtension($extension, array());
            }
        }

        parent::process($container);
    }
}
