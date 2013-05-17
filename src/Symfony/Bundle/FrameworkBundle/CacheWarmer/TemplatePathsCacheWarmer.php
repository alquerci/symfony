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
 * Computes the association between template names and their paths on the disk.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_CacheWarmer_TemplatePathsCacheWarmer extends Symfony_Component_HttpKernel_CacheWarmer_CacheWarmer
{
    protected $finder;
    protected $locator;

    /**
     * Constructor.
     *
     * @param Symfony_Bundle_FrameworkBundle_CacheWarmer_TemplateFinderInterface $finder  A template finder
     * @param Symfony_Bundle_FrameworkBundle_Templating_Loader_TemplateLocator         $locator The template locator
     */
    public function __construct(Symfony_Bundle_FrameworkBundle_CacheWarmer_TemplateFinderInterface $finder, Symfony_Bundle_FrameworkBundle_Templating_Loader_TemplateLocator $locator)
    {
        $this->finder = $finder;
        $this->locator = $locator;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $templates = array();

        foreach ($this->finder->findAllTemplates() as $template) {
            $templates[$template->getLogicalName()] = $this->locator->locate($template);
        }

        $this->writeCacheFile($cacheDir.'/templates.php', sprintf('<?php return %s;', var_export($templates, true)));
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
