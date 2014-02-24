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
 * Deprecated. You should extend FormType instead.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @deprecated Deprecated since version 2.1, to be removed in 2.3.
 */
class Symfony_Component_Form_Extension_Core_Type_FieldType extends Symfony_Component_Form_AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'field';
    }
}
