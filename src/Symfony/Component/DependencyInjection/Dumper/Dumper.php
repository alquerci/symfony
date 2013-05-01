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
 * Dumper is the abstract class for all built-in dumpers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
abstract class Symfony_Component_DependencyInjection_Dumper_Dumper implements Symfony_Component_DependencyInjection_Dumper_DumperInterface
{
    protected $container;

    /**
     * Constructor.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container The service container to dump
     *
     * @api
     */
    public function __construct(Symfony_Component_DependencyInjection_ContainerBuilder $container)
    {
        $this->container = $container;
    }
}
