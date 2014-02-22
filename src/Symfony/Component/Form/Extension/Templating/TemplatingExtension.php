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
 * Integrates the Templating component with the Form library.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Extension_Templating_TemplatingExtension extends Symfony_Component_Form_AbstractExtension
{
    public function __construct(Symfony_Component_Templating_PhpEngine $engine, Symfony_Component_Form_Extension_Csrf_CsrfProvider_CsrfProviderInterface $csrfProvider = null, array $defaultThemes = array())
    {
        $engine->addHelpers(array(
            new Symfony_Bundle_FrameworkBundle_Templating_Helper_FormHelper(new Symfony_Component_Form_FormRenderer(new Symfony_Component_Form_Extension_Templating_TemplatingRendererEngine($engine, $defaultThemes), $csrfProvider))
        ));
    }
}
