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
class Symfony_Component_Form_Extension_Core_DataTransformer_ValueToDuplicatesTransformer implements Symfony_Component_Form_DataTransformerInterface
{
    private $keys;

    public function __construct(array $keys)
    {
        $this->keys = $keys;
    }

    /**
     * Duplicates the given value through the array.
     *
     * @param mixed $value The value
     *
     * @return array The array
     */
    public function transform($value)
    {
        $result = array();

        foreach ($this->keys as $key) {
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Extracts the duplicated value from an array.
     *
     * @param array $array
     *
     * @return mixed The value
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if the given value is not an array
     * @throws Symfony_Component_Form_Exception_TransformationFailedException if the given array can not be transformed
     */
    public function reverseTransform($array)
    {
        if (!is_array($array)) {
            throw new Symfony_Component_Form_Exception_UnexpectedTypeException($array, 'array');
        }

        $result = current($array);
        $emptyKeys = array();

        foreach ($this->keys as $key) {
            if (!empty($array[$key])) {
                if ($array[$key] !== $result) {
                    throw new Symfony_Component_Form_Exception_TransformationFailedException(
                        'All values in the array should be the same'
                    );
                }
            } else {
                $emptyKeys[] = $key;
            }
        }

        if (count($emptyKeys) > 0) {
            if (count($emptyKeys) == count($this->keys)) {
                // All keys empty
                return null;
            }

            throw new Symfony_Component_Form_Exception_TransformationFailedException(
                 sprintf('The keys "%s" should not be empty', implode('", "', $emptyKeys)
            ));
        }

        return $result;
    }
}
