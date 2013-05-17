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
 * TemplateLocator locates templates in bundles.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Templating_Loader_TemplateLocator implements Symfony_Component_Config_FileLocatorInterface
{
    protected $locator;
    protected $cache;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Config_FileLocatorInterface $locator  A FileLocatorInterface instance
     * @param string               $cacheDir The cache path
     */
    public function __construct(Symfony_Component_Config_FileLocatorInterface $locator, $cacheDir = null)
    {
        if (null !== $cacheDir && is_file($cache = $cacheDir.'/templates.php')) {
            $this->cache = require $cache;
        }

        $this->locator = $locator;
    }

    /**
     * Returns a full path for a given file.
     *
     * @param Symfony_Component_Templating_TemplateReferenceInterface $template A template
     *
     * @return string The full path for the file
     */
    protected function getCacheKey($template)
    {
        return $template->getLogicalName();
    }

    /**
     * Returns a full path for a given file.
     *
     * @param Symfony_Component_Templating_TemplateReferenceInterface $template    A template
     * @param string                     $currentPath Unused
     * @param Boolean                    $first       Unused
     *
     * @return string The full path for the file
     *
     * @throws InvalidArgumentException When the template is not an instance of TemplateReferenceInterface
     * @throws InvalidArgumentException When the template file can not be found
     */
    public function locate($template, $currentPath = null, $first = true)
    {
        if (!$template instanceof Symfony_Component_Templating_TemplateReferenceInterface) {
            throw new InvalidArgumentException("The template must be an instance of TemplateReferenceInterface.");
        }

        $key = $this->getCacheKey($template);

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        try {
            return $this->cache[$key] = $this->locator->locate($template->getPath(), $currentPath);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException(sprintf('Unable to find template "%s" : "%s".', $template, $e->getMessage()), 0/* , $e */);
        }
    }
}
