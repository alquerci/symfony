<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_Fixtures_KernelForTest extends Symfony_Component_HttpKernel_Kernel
{
    public function getBundleMap()
    {
        return $this->bundleMap;
    }

    public function registerBundles()
    {
    }

    public function init()
    {
    }

    public function registerBundleDirs()
    {
    }

    public function registerContainerConfiguration(Symfony_Component_Config_Loader_LoaderInterface $loader)
    {
    }

    public function initializeBundles()
    {
        parent::initializeBundles();
    }

    public function isBooted()
    {
        return $this->booted;
    }

    public function setIsBooted($value)
    {
        $this->booted = (Boolean) $value;
    }
}
