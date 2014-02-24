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
 * Integrates the HttpFoundation component with the Form library.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Extension_HttpFoundation_HttpFoundationExtension extends Symfony_Component_Form_AbstractExtension
{
    protected function loadTypeExtensions()
    {
        return array(
            new Symfony_Component_Form_Extension_HttpFoundation_Type_FormTypeHttpFoundationExtension(),
        );
    }
}
