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
 * UserProviderFactoryInterface is the interface for all user provider factories.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
interface Symfony_Bundle_SecurityBundle_DependencyInjection_Security_UserProvider_UserProviderFactoryInterface
{
    public function create(Symfony_Component_DependencyInjection_ContainerBuilder $container, $id, $config);

    public function getKey();

    public function addConfiguration(Symfony_Component_Config_Definition_Builder_NodeDefinition $builder);
}
