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
 * SecurityFactoryInterface is the interface for all security authentication listener.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Symfony_Bundle_SecurityBundle_DependencyInjection_Security_Factory_SecurityFactoryInterface
{
    public function create(Symfony_Component_DependencyInjection_ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint);

    public function getPosition();

    public function getKey();

    public function addConfiguration(Symfony_Component_Config_Definition_Builder_NodeDefinition $builder);
}
