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
 * This extension protects forms by using a CSRF token.
 */
class Symfony_Component_Form_Extension_Csrf_CsrfExtension extends Symfony_Component_Form_AbstractExtension
{
    private $csrfProvider;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Form_Extension_Csrf_CsrfProvider_CsrfProviderInterface $csrfProvider The CSRF provider
     */
    public function __construct(Symfony_Component_Form_Extension_Csrf_CsrfProvider_CsrfProviderInterface $csrfProvider)
    {
        $this->csrfProvider = $csrfProvider;
    }

    /**
     * {@inheritDoc}
     */
    protected function loadTypeExtensions()
    {
        return array(
            new Symfony_Component_Form_Extension_Csrf_Type_FormTypeCsrfExtension($this->csrfProvider),
        );
    }
}
