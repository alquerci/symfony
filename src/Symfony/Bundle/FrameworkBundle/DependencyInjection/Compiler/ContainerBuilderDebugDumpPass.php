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
 * Dumps the ContainerBuilder to a cache file so that it can be used by
 * debugging tools such as the container:debug console command.
 *
 * @author Ryan Weaver <ryan@thatsquality.com>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_ContainerBuilderDebugDumpPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $dumper = new Symfony_Component_DependencyInjection_Dumper_XmlDumper($container);
        $cache = new Symfony_Component_Config_ConfigCache($container->getParameter('debug.container.dump'), false);
        $cache->write($dumper->dump());
    }
}
