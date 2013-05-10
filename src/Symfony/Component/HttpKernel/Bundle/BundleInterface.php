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
 * BundleInterface.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface Symfony_Component_HttpKernel_Bundle_BundleInterface extends Symfony_Component_DependencyInjection_ContainerAwareInterface
{
    /**
     * Boots the Bundle.
     *
     * @api
     */
    public function boot();

    /**
     * Shutdowns the Bundle.
     *
     * @api
     */
    public function shutdown();

    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * @param Symfony_Component_DependencyInjection_ContainerBuilder $container A ContainerBuilder instance
     *
     * @api
     */
    public function build(Symfony_Component_DependencyInjection_ContainerBuilder $container);

    /**
     * Returns the container extension that should be implicitly loaded.
     *
     * @return Symfony_Component_DependencyInjection_Extension_ExtensionInterface|null The default extension or null if there is none
     *
     * @api
     */
    public function getContainerExtension();

    /**
     * Returns the bundle name that this bundle overrides.
     *
     * Despite its name, this method does not imply any parent/child relationship
     * between the bundles, just a way to extend and override an existing
     * bundle.
     *
     * @return string The Bundle name it overrides or null if no parent
     *
     * @api
     */
    public function getParent();

    /**
     * Returns the bundle name (the class short name).
     *
     * @return string The Bundle name
     *
     * @api
     */
    public function getName();

    /**
     * Gets the Bundle namespace.
     *
     * @return string The Bundle namespace
     *
     * @api
     */
    public function getNamespace();

    /**
     * Gets the Bundle directory path.
     *
     * The path should always be returned as a Unix path (with /).
     *
     * @return string The Bundle absolute path
     *
     * @api
     */
    public function getPath();
}
