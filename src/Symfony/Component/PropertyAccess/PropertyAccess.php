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
 * Entry point of the Symfony_Component_PropertyAccess_PropertyAccess component.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
final class Symfony_Component_PropertyAccess_PropertyAccess
{
    /**
     * Creates a property accessor with the default configuration.
     *
     * @return PropertyAccessor The new property accessor
     */
    public static function getPropertyAccessor()
    {
        return new Symfony_Component_PropertyAccess_PropertyAccessor();
    }

    /**
     * This class cannot be instantiated.
     */
    private function __construct()
    {
    }
}
