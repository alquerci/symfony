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
 * Writes and reads values to/from an object/array graph.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Symfony_Component_PropertyAccess_PropertyAccessorInterface
{
    /**
     * Sets the value at the end of the property path of the object
     *
     * Example:
     *
     *     $propertyAccessor = Symfony_Component_PropertyAccess_PropertyAccess::getPropertyAccessor();
     *
     *     echo $propertyAccessor->setValue($object, 'child.name', 'Fabien');
     *     // equals echo $object->getChild()->setName('Fabien');
     *
     * This method first tries to find a public setter for each property in the
     * path. The name of the setter must be the camel-cased property name
     * prefixed with "set".
     *
     * If the setter does not exist, this method tries to find a public
     * property. The value of the property is then changed.
     *
     * If neither is found, an exception is thrown.
     *
     * @param object|array                 $objectOrArray The object or array to modify
     * @param string|Symfony_Component_PropertyAccess_PropertyPathInterface $propertyPath  The property path to modify
     * @param mixed                        $value         The value to set at the end of the property path
     *
     * @throws Symfony_Component_PropertyAccess_Exception_NoSuchPropertyException       If a property does not exist
     * @throws Symfony_Component_PropertyAccess_Exception_PropertyAccessDeniedException If a property cannot be accessed due to
     *                                                 access restrictions (private or protected)
     * @throws Symfony_Component_PropertyAccess_Exception_UnexpectedTypeException       If a value within the path is neither object
     *                                                 nor array
     */
    public function setValue(&$objectOrArray, $propertyPath, $value);

    /**
     * Returns the value at the end of the property path of the object
     *
     * Example:
     *
     *     $propertyAccessor = Symfony_Component_PropertyAccess_PropertyAccess::getPropertyAccessor();
     *
     *     echo $propertyAccessor->getValue($object, 'child.name);
     *     // equals echo $object->getChild()->getName();
     *
     * This method first tries to find a public getter for each property in the
     * path. The name of the getter must be the camel-cased property name
     * prefixed with "get", "is", or "has".
     *
     * If the getter does not exist, this method tries to find a public
     * property. The value of the property is then returned.
     *
     * If none of them are found, an exception is thrown.
     *
     * @param object|array                 $objectOrArray The object or array to traverse
     * @param string|Symfony_Component_PropertyAccess_PropertyPathInterface $propertyPath  The property path to read
     *
     * @return mixed The value at the end of the property path
     *
     * @throws Symfony_Component_PropertyAccess_Exception_NoSuchPropertyException       If the property/getter does not exist
     * @throws Symfony_Component_PropertyAccess_Exception_PropertyAccessDeniedException If the property/getter exists but is not public
     */
    public function getValue($objectOrArray, $propertyPath);
}
