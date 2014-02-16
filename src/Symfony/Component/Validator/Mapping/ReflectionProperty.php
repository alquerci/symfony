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
 * @author Alexandre Quercia <alquerci@email.com>
 */
class Symfony_Component_Validator_Mapping_ReflectionProperty extends ReflectionProperty
{
    private $accessible = false;
    private $_class;
    private $_name;
    private $_attribute;

    public function __construct($class, $name)
    {
        $this->_class = $class;
        $this->_name = $name;

        try {
            parent::__construct($class, $name);
            $this->_attribute = true;
        } catch (ReflectionException $e) {
            if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
                throw $e;
            }

            // Workaround for http://bugs.php.net/46064
            if (version_compare(PHP_VERSION, '5.2.7', '<')) {
                $reflector  = new ReflectionClass($class);
                $attributes = $reflector->getProperties();

                foreach ($attributes as $_attribute) {
                    if ($_attribute->getName() == $name) {
                        $this->_attribute = $_attribute;
                        break;
                    }
                }
            }

            $reflector = new ReflectionClass($class);

            while ($reflector = $reflector->getParentClass()) {
                try {
                    $this->_attribute = $reflector->getProperty($name);
                    break;
                } catch(ReflectionException $e) {
                }
            }
        }

        if (null === $this->_attribute) {
            throw new ReflectionException(sprintf('Property %s::$%s does not exist', $class, $name));
        }
    }

    public function setAccessible($boolean)
    {
        $this->accessible = $boolean;

        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            parent::setAccessible($boolean);
        }
    }

    public function getValue($object = null)
    {
        if (true !== $this->accessible) {
            return parent::getValue($object);
        }

        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            return parent::getValue($object);
        }

        $attributeName = $this->_name;
        if (null === $object) {
            $class = new ReflectionClass($this->_class);

            while ($class) {
                $attributes = $class->getStaticProperties();

                if (array_key_exists($attributeName, $attributes)) {
                    return $attributes[$attributeName];
                }

                if (version_compare(PHP_VERSION, '5.3.0', '<')) {
                    // https://bugs.php.net/38132
                    if (version_compare(PHP_VERSION, '5.1.5', '<')) {
                        $protectedName = "\0*\0" . $attributeName;
                    } else {
                        $protectedName = '*' . $attributeName;
                    }

                    if (array_key_exists($protectedName, $attributes)) {
                        return $attributes[$protectedName];
                    }

                    $privateName = "\0" . $class->getName() . "\0" . $attributeName;

                    if (array_key_exists($privateName, $attributes)) {
                        return $attributes[$privateName];
                    }
                }

                $class = $class->getParentClass();
            }

            return;
        }

        if (true === $this->_attribute) {
            $attribute = $this;
        } elseif (null !== $this->_attribute) {
            $attribute = $this->_attribute;
        }

        if (isset($attribute)) {
            if ($attribute == null || $attribute->isPublic()) {
                return $object->$attributeName;
            } else {
                $array         = (array) $object;
                $protectedName = "\0*\0".$attributeName;

                if (array_key_exists($protectedName, $array)) {
                    return $array[$protectedName];
                } else {
                    $classes = array(get_class($object));

                    while (true) {
                        $class = new ReflectionClass($classes[count($classes)-1]);
                        $parent = $class->getParentClass();
                        if ($parent !== false) {
                            $classes[] = $parent->getName();
                        } else {
                            break;
                        }
                    }

                    foreach ($classes as $class) {
                        $privateName = sprintf(
                            "\0%s\0%s",
                            $class,
                            $attributeName
                        );

                        if (array_key_exists($privateName, $array)) {
                            return $array[$privateName];
                        }
                    }
                }
            }
        } else {
            return $object->$attributeName;
        }
    }

    public static function propertyExists($class, $name)
    {
        try {
            new self($class, $name);

            return true;
        } catch (ReflectionException $e) {
            return false;
        }
    }
}
