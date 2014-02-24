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
 * Alias for {@link Symfony_Component_PropertyAccess_PropertyPathIterator}.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @deprecated deprecated since version 2.2, to be removed in 2.3. Use
 *             {@link Symfony_Component_PropertyAccess_PropertyPathIterator}
 *             instead.
 */
class Symfony_Component_Form_Util_PropertyPathIterator extends Symfony_Component_PropertyAccess_PropertyPathIterator
{
    /**
     * {@inheritdoc}
     */
    public function __construct($propertyPath)
    {
        parent::__construct($propertyPath);

        trigger_error('Symfony_Component_Form_Util_PropertyPathIterator is deprecated since version 2.2 and will be removed in 2.3. Use Symfony_Component_PropertyAccess_PropertyPathIterator instead.', E_USER_DEPRECATED);
    }
}
