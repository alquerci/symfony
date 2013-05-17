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
 * LoaderInterface is the interface all loaders must implement.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface Symfony_Component_Templating_Loader_LoaderInterface
{
    /**
     * Loads a template.
     *
     * @param Symfony_Component_Templating_TemplateReferenceInterface $template A template
     *
     * @return Symfony_Component_Templating_Storage_Storage|Boolean false if the template cannot be loaded, a Storage instance otherwise
     *
     * @api
     */
    public function load(Symfony_Component_Templating_TemplateReferenceInterface $template);

    /**
     * Returns true if the template is still fresh.
     *
     * @param Symfony_Component_Templating_TemplateReferenceInterface $template A template
     * @param integer                    $time     The last modification time of the cached template (timestamp)
     *
     * @return Boolean
     *
     * @api
     */
    public function isFresh(Symfony_Component_Templating_TemplateReferenceInterface $template, $time);
}
