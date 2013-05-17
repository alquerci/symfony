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
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
interface Symfony_Component_Finder_Adapter_AdapterInterface
{
    /**
     * @param Boolean $followLinks
     *
     * @return Symfony_Component_Finder_Adapter_AdapterInterface Current instance
     */
    public function setFollowLinks($followLinks);

    /**
     * @param integer $mode
     *
     * @return Symfony_Component_Finder_Adapter_AdapterInterface Current instance
     */
    public function setMode($mode);

    /**
     * @param array $exclude
     *
     * @return Symfony_Component_Finder_Adapter_AdapterInterface Current instance
     */
    public function setExclude(array $exclude);

    /**
     * @param array $depths
     *
     * @return Symfony_Component_Finder_Adapter_AdapterInterface Current instance
     */
    public function setDepths(array $depths);

    /**
     * @param array $names
     *
     * @return Symfony_Component_Finder_Adapter_AdapterInterface Current instance
     */
    public function setNames(array $names);

    /**
     * @param array $notNames
     *
     * @return Symfony_Component_Finder_Adapter_AdapterInterface Current instance
     */
    public function setNotNames(array $notNames);

    /**
     * @param array $contains
     *
     * @return Symfony_Component_Finder_Adapter_AdapterInterface Current instance
     */
    public function setContains(array $contains);

    /**
     * @param array $notContains
     *
     * @return Symfony_Component_Finder_Adapter_AdapterInterface Current instance
     */
    public function setNotContains(array $notContains);

    /**
     * @param array $sizes
     *
     * @return Symfony_Component_Finder_Adapter_AdapterInterface Current instance
     */
    public function setSizes(array $sizes);

    /**
     * @param array $dates
     *
     * @return Symfony_Component_Finder_Adapter_AdapterInterface Current instance
     */
    public function setDates(array $dates);

    /**
     * @param array $filters
     *
     * @return Symfony_Component_Finder_Adapter_AdapterInterface Current instance
     */
    public function setFilters(array $filters);

    /**
     * @param callable|integer $sort
     *
     * @return Symfony_Component_Finder_Adapter_AdapterInterface Current instance
     */
    public function setSort($sort);

    /**
     * @param array $paths
     *
     * @return Symfony_Component_Finder_Adapter_AdapterInterface Current instance
     */
    public function setPath(array $paths);

    /**
     * @param array $notPaths
     *
     * @return Symfony_Component_Finder_Adapter_AdapterInterface Current instance
     */
    public function setNotPath(array $notPaths);

    /**
     * @param string $dir
     *
     * @return Iterator Result iterator
     */
    public function searchInDirectory($dir);

    /**
     * Tests adapter support for current platform.
     *
     * @return Boolean
     */
    public function isSupported();

    /**
     * Returns adapter name.
     *
     * @return string
     */
    public function getName();
}
