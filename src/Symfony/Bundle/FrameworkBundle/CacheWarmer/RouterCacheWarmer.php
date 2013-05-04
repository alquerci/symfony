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
 * Generates the router matcher and generator classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_CacheWarmer_RouterCacheWarmer implements Symfony_Component_HttpKernel_CacheWarmer_CacheWarmerInterface
{
    protected $router;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Routing_RouterInterface $router A Router instance
     */
    public function __construct(Symfony_Component_Routing_RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        if ($this->router instanceof Symfony_Component_HttpKernel_CacheWarmer_WarmableInterface) {
            $this->router->warmUp($cacheDir);
        }
    }

    /**
     * Checks whether this warmer is optional or not.
     *
     * @return Boolean always true
     */
    public function isOptional()
    {
        return true;
    }
}
