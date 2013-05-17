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
 * ChainLoader is a loader that calls other loaders to load templates.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Templating_Loader_ChainLoader extends Symfony_Component_Templating_Loader_Loader
{
    protected $loaders;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Templating_Loader_LoaderInterface[] $loaders An array of loader instances
     */
    public function __construct(array $loaders = array())
    {
        $this->loaders = array();
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    /**
     * Adds a loader instance.
     *
     * @param Symfony_Component_Templating_Loader_LoaderInterface $loader A Loader instance
     */
    public function addLoader(Symfony_Component_Templating_Loader_LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
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
        foreach ($this->loaders as $loader) {
            if (false !== $storage = $loader->load($template)) {
                return $storage;
            }
        }

        return false;
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
        foreach ($this->loaders as $loader) {
            return $loader->isFresh($template);
        }

        return false;
    }
}
