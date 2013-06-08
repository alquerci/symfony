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
 * Manages HTTP cache objects in a Container.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Symfony_Bundle_FrameworkBundle_HttpCache_HttpCache extends Symfony_Component_HttpKernel_HttpCache_HttpCache
{
    protected $cacheDir;
    protected $kernel;

    /**
     * Constructor.
     *
     * @param Symfony_Component_HttpKernel_HttpKernelInterface $kernel   An HttpKernelInterface instance
     * @param string              $cacheDir The cache directory (default used if null)
     */
    public function __construct(Symfony_Component_HttpKernel_HttpKernelInterface $kernel, $cacheDir = null)
    {
        $this->kernel = $kernel;
        $this->cacheDir = $cacheDir;

        parent::__construct($kernel, $this->createStore(), $this->createEsi(), array_merge(array('debug' => $kernel->isDebug()), $this->getOptions()));
    }

    /**
     * Forwards the Request to the backend and returns the Response.
     *
     * @param Symfony_Component_HttpFoundation_Request  $request A Request instance
     * @param Boolean  $raw     Whether to catch exceptions or not
     * @param Symfony_Component_HttpFoundation_Response $entry   A Response instance (the stale entry if present, null otherwise)
     *
     * @return Symfony_Component_HttpFoundation_Response A Response instance
     */
    protected function forward(Symfony_Component_HttpFoundation_Request $request, $raw = false, Symfony_Component_HttpFoundation_Response $entry = null)
    {
        $this->getKernel()->boot();
        $this->getKernel()->getContainer()->set('cache', $this);
        $this->getKernel()->getContainer()->set('esi', $this->getEsi());

        return parent::forward($request, $raw, $entry);
    }

    /**
     * Returns an array of options to customize the Cache configuration.
     *
     * @return array An array of options
     */
    protected function getOptions()
    {
        return array();
    }

    protected function createEsi()
    {
        return new Symfony_Component_HttpKernel_HttpCache_Esi();
    }

    protected function createStore()
    {
        return new Symfony_Component_HttpKernel_HttpCache_Store($this->cacheDir ? $this->cacheDir : $this->kernel->getCacheDir().'/http_cache');
    }
}
