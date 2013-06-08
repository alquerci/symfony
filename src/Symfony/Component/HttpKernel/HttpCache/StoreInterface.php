<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This code is partially based on the Rack-Cache library by Ryan Tomayko,
 * which is released under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface implemented by HTTP cache stores.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Symfony_Component_HttpKernel_HttpCache_StoreInterface
{
    /**
     * Locates a cached Response for the Request provided.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     *
     * @return Symfony_Component_HttpFoundation_Response|null A Response instance, or null if no cache entry was found
     */
    public function lookup(Symfony_Component_HttpFoundation_Request $request);

    /**
     * Writes a cache entry to the store for the given Request and Response.
     *
     * Existing entries are read and any that match the response are removed. This
     * method calls write with the new list of cache entries.
     *
     * @param Symfony_Component_HttpFoundation_Request  $request  A Request instance
     * @param Symfony_Component_HttpFoundation_Response $response A Response instance
     *
     * @return string The key under which the response is stored
     */
    public function write(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_HttpFoundation_Response $response);

    /**
     * Invalidates all cache entries that match the request.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     */
    public function invalidate(Symfony_Component_HttpFoundation_Request $request);

    /**
     * Locks the cache for a given Request.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     *
     * @return Boolean|string true if the lock is acquired, the path to the current lock otherwise
     */
    public function lock(Symfony_Component_HttpFoundation_Request $request);

    /**
     * Releases the lock for the given Request.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     *
     * @return Boolean False if the lock file does not exist or cannot be unlocked, true otherwise
     */
    public function unlock(Symfony_Component_HttpFoundation_Request $request);

    /**
     * Returns whether or not a lock exists.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     *
     * @return Boolean true if lock exists, false otherwise
     */
    public function isLocked(Symfony_Component_HttpFoundation_Request $request);

    /**
     * Purges data for the given URL.
     *
     * @param string $url A URL
     *
     * @return Boolean true if the URL exists and has been purged, false otherwise
     */
    public function purge($url);

    /**
     * Cleanups storage.
     */
    public function cleanup();
}
