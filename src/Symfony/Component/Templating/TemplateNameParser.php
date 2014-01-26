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
 * TemplateNameParser is the default implementation of TemplateNameParserInterface.
 *
 * This implementation takes everything as the template name
 * and the extension for the engine.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class Symfony_Component_Templating_TemplateNameParser implements Symfony_Component_Templating_TemplateNameParserInterface
{
    /**
     * Parses a template to an array of parameters.
     *
     * @param string $name A template name
     *
     * @return Symfony_Component_Templating_TemplateReferenceInterface A template
     *
     * @api
     */
    public function parse($name)
    {
        if ($name instanceof Symfony_Component_Templating_TemplateReferenceInterface) {
            return $name;
        }

        $engine = null;
        if (false !== $pos = strrpos($name, '.')) {
            $engine = substr($name, $pos + 1);
        }

        return new Symfony_Component_Templating_TemplateReference($name, $engine);
    }
}
