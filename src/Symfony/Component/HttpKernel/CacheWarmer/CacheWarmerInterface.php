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
 * Interface for classes able to warm up the cache.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Symfony_Component_HttpKernel_CacheWarmer_CacheWarmerInterface extends Symfony_Component_HttpKernel_CacheWarmer_WarmableInterface
{
    /**
     * Checks whether this warmer is optional or not.
     *
     * Optional warmers can be ignored on certain conditions.
     *
     * A warmer should return true if the cache can be
     * generated incrementally and on-demand.
     *
     * @return Boolean true if the warmer is optional, false otherwise
     */
    public function isOptional();
}
