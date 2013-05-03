<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Symfony_Bundle_FrameworkBundle_DependencyInjection_Compiler_CompilerDebugDumpPass implements Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $cache = new Symfony_Component_Config_ConfigCache($this->getCompilerLogFilename($container), false);
        $cache->write(implode("\n", $container->getCompiler()->getLog()));
    }

    public static function getCompilerLogFilename(Symfony_Component_DependencyInjection_ContainerInterface $container)
    {
        $class = $container->getParameter('kernel.container_class');

        return $container->getParameter('kernel.cache_dir').'/'.$class.'Compiler.log';
    }
}
