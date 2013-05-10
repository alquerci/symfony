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
 * Aggregates several cache warmers into a single one.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_HttpKernel_CacheWarmer_CacheWarmerAggregate implements Symfony_Component_HttpKernel_CacheWarmer_CacheWarmerInterface
{
    protected $warmers;
    protected $optionalsEnabled;

    public function __construct(array $warmers = array())
    {
        $this->setWarmers($warmers);
        $this->optionalsEnabled = false;
    }

    public function enableOptionalWarmers()
    {
        $this->optionalsEnabled = true;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        foreach ($this->warmers as $warmer) {
            if (!$this->optionalsEnabled && $warmer->isOptional()) {
                continue;
            }

            $warmer->warmUp($cacheDir);
        }
    }

    /**
     * Checks whether this warmer is optional or not.
     *
     * @return Boolean always true
     */
    public function isOptional()
    {
        return false;
    }

    public function setWarmers(array $warmers)
    {
        $this->warmers = array();
        foreach ($warmers as $warmer) {
            $this->add($warmer);
        }
    }

    public function add(Symfony_Component_HttpKernel_CacheWarmer_CacheWarmerInterface $warmer)
    {
        $this->warmers[] = $warmer;
    }
}
