<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_DependencyInjection_PhpFrameworkExtensionTest extends Symfony_Bundle_FrameworkBundle_Tests_DependencyInjection_FrameworkExtensionTest
{
    protected function loadFromFile(Symfony_Component_DependencyInjection_ContainerBuilder $container, $file)
    {
        $loader = new Symfony_Component_DependencyInjection_Loader_PhpFileLoader($container, new Symfony_Component_Config_FileLocator(dirname(__FILE__).'/Fixtures/php'));
        $loader->load($file.'.php');
    }
}
