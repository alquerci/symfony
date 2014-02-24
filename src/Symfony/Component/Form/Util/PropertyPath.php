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
 * Alias for {@link Symfony_Component_PropertyAccess_PropertyPath}.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @deprecated deprecated since version 2.2, to be removed in 2.3. Use
 *             {@link Symfony_Component_PropertyAccess_PropertyPath}
 *             instead.
 */
class Symfony_Component_Form_Util_PropertyPath extends Symfony_Component_PropertyAccess_PropertyPath
{
    /**
     * {@inheritdoc}
     */
    public function __construct($propertyPath)
    {
        parent::__construct($propertyPath);

        trigger_error('Symfony_Component_Form_Util_PropertyPath is deprecated since version 2.2 and will be removed in 2.3. Use Symfony_Component_PropertyAccess_PropertyPath instead.', E_USER_DEPRECATED);
    }

    /**
     * Alias for {@link PropertyAccessor::getValue()}
     */
    public function getValue($objectOrArray)
    {
        $propertyAccessor = Symfony_Component_PropertyAccess_PropertyAccess::getPropertyAccessor();

        return $propertyAccessor->getValue($objectOrArray, $this);
    }

    /**
     * Alias for {@link PropertyAccessor::setValue()}
     */
    public function setValue(&$objectOrArray, $value)
    {
        $propertyAccessor = Symfony_Component_PropertyAccess_PropertyAccess::getPropertyAccessor();

        return $propertyAccessor->setValue($objectOrArray, $this, $value);
    }
}
