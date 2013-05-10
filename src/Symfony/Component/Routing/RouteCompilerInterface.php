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
 * RouteCompilerInterface is the interface that all RouteCompiler classes must implement.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Symfony_Component_Routing_RouteCompilerInterface
{
    /**
     * Compiles the current route instance.
     *
     * @param Symfony_Component_Routing_Route $route A Route instance
     *
     * @return Symfony_Component_Routing_CompiledRoute A CompiledRoute instance
     *
     * @throws LogicException If the Route cannot be compiled because the
     *                         path or host pattern is invalid
     */
    public static function compile(Symfony_Component_Routing_Route $route);
}
