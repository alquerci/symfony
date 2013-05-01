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
 * Interface that must be implemented by compilation passes
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * @api
 */
interface Symfony_Component_DependencyInjection_Compiler_CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container
     *
     * @api
     */
    public function process(Symfony_Component_DependencyInjection_ContainerBuilder $container);
}
