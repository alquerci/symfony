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
 * FilesystemLoader is a loader that read templates from the filesystem.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Templating_Loader_FilesystemLoader implements Symfony_Component_Templating_Loader_LoaderInterface
{
    protected $locator;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Config_FileLocatorInterface $locator A FileLocatorInterface instance
     */
    public function __construct(Symfony_Component_Config_FileLocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * Loads a template.
     *
     * @param Symfony_Component_Templating_TemplateReferenceInterface $template A template
     *
     * @return Symfony_Component_Templating_Storage_FileStorage|Boolean false if the template cannot be loaded, a Storage instance otherwise
     */
    public function load(Symfony_Component_Templating_TemplateReferenceInterface $template)
    {
        try {
            $file = $this->locator->locate($template);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        return new Symfony_Component_Templating_Storage_FileStorage($file);
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @param Symfony_Component_Templating_TemplateReferenceInterface $template The template name as an array
     * @param integer                    $time     The last modification time of the cached template (timestamp)
     *
     * @return Boolean
     */
    public function isFresh(Symfony_Component_Templating_TemplateReferenceInterface $template, $time)
    {
        if (false === $storage = $this->load($template)) {
            return false;
        }

        if (!is_readable((string) $storage->__toString())) {
            return false;
        }

        return filemtime((string) $storage->__toString()) < $time;
    }
}
