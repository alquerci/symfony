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
 * This interface is deprecated. You should use a FormEvents::POST_BIND event
 * listener instead.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @deprecated Deprecated since version 2.1, to be removed in 2.3.
 */
interface Symfony_Component_Form_FormValidatorInterface
{
    /**
     * @deprecated Deprecated since version 2.1, to be removed in 2.3.
     */
    public function validate(Symfony_Component_Form_FormInterface $form);
}
