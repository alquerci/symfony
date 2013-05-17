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
 * CacheLoader is a loader that caches other loaders responses
 * on the filesystem.
 *
 * This cache only caches on disk to allow PHP accelerators to cache the opcodes.
 * All other mechanism would imply the use of `eval()`.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Templating_Loader_CacheLoader extends Symfony_Component_Templating_Loader_Loader
{
    protected $loader;
    protected $dir;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Templating_Loader_LoaderInterface $loader A Loader instance
     * @param string          $dir    The directory where to store the cache files
     */
    public function __construct(Symfony_Component_Templating_Loader_LoaderInterface $loader, $dir)
    {
        $this->loader = $loader;
        $this->dir = $dir;
    }

    /**
     * Loads a template.
     *
     * @param Symfony_Component_Templating_TemplateReferenceInterface $template A template
     *
     * @return Symfony_Component_Templating_Storage_Storage|Boolean false if the template cannot be loaded, a Storage instance otherwise
     */
    public function load(Symfony_Component_Templating_TemplateReferenceInterface $template)
    {
        $key = md5($template->getLogicalName());
        $dir = $this->dir.DIRECTORY_SEPARATOR.substr($key, 0, 2);
        $file = substr($key, 2).'.tpl';
        $path = $dir.DIRECTORY_SEPARATOR.$file;

        if (is_file($path)) {
            if (null !== $this->debugger) {
                $this->debugger->log(sprintf('Fetching template "%s" from cache', $template->get('name')));
            }

            return new Symfony_Component_Templating_Storage_FileStorage($path);
        }

        if (false === $storage = $this->loader->load($template)) {
            return false;
        }

        $content = $storage->getContent();

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($path, $content);

        if (null !== $this->debugger) {
            $this->debugger->log(sprintf('Storing template "%s" in cache', $template->get('name')));
        }

        return new Symfony_Component_Templating_Storage_FileStorage($path);
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @param Symfony_Component_Templating_TemplateReferenceInterface $template A template
     * @param integer                    $time     The last modification time of the cached template (timestamp)
     *
     * @return Boolean
     */
    public function isFresh(Symfony_Component_Templating_TemplateReferenceInterface $template, $time)
    {
        return $this->loader->isFresh($template, $time);
    }
}
