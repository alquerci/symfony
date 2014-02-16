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
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Util_FormUtil
{
    /**
     * This class should not be instantiated
     */
    private function __construct() {}

    /**
     * Alias for {@link Symfony_Component_PropertyAccess_StringUtil::singularify()}
     *
     * @deprecated Deprecated since version 2.2, to be removed in 2.3. Use
     *             {@link Symfony_Component_PropertyAccess_StringUtil::singularify()} instead.
     */
    public static function singularify($plural)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('Symfony_Component_Form_Util_FormUtil::singularify() is deprecated since version 2.2 and will be removed in 2.3. Use Symfony_Component_PropertyAccess_StringUtil::singularify() in the PropertyAccess component instead.', E_USER_DEPRECATED);

        return Symfony_Component_PropertyAccess_StringUtil::singularify($plural);
    }

    /**
     * Returns whether the given data is empty.
     *
     * This logic is reused multiple times throughout the processing of
     * a form and needs to be consistent. PHP's keyword `empty` cannot
     * be used as it also considers 0 and "0" to be empty.
     *
     * @param  mixed $data
     *
     * @return Boolean
     */
    public static function isEmpty($data)
    {
        // Should not do a check for array() === $data!!!
        // This method is used in occurrences where arrays are
        // not considered to be empty, ever.
        return null === $data || '' === $data;
    }
}
