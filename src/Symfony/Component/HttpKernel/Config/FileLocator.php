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
 * FileLocator uses the KernelInterface to locate resources in bundles.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_HttpKernel_Config_FileLocator extends Symfony_Component_Config_FileLocator
{
    private $kernel;
    private $path;

    /**
     * Constructor.
     *
     * @param Symfony_Component_HttpKernel_KernelInterface $kernel A KernelInterface instance
     * @param string          $path   The path the global resource directory
     * @param string|array    $paths  A path or an array of paths where to look for resources
     */
    public function __construct(Symfony_Component_HttpKernel_KernelInterface $kernel, $path = null, array $paths = array())
    {
        $this->kernel = $kernel;
        $this->path = $path;
        $paths[] = $path;

        parent::__construct($paths);
    }

    /**
     * {@inheritdoc}
     */
    public function locate($file, $currentPath = null, $first = true)
    {
        if ('@' === $file[0]) {
            return $this->kernel->locateResource($file, $this->path, $first);
        }

        return parent::locate($file, $currentPath, $first);
    }
}
