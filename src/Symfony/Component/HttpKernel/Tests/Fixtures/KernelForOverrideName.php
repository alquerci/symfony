<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_Fixtures_KernelForOverrideName extends Symfony_Component_HttpKernel_Kernel
{
    protected $name = 'overridden';

    public function registerBundles()
    {

    }

    public function registerContainerConfiguration(Symfony_Component_Config_Loader_LoaderInterface $loader)
    {

    }

    public function init()
    {

    }
}
