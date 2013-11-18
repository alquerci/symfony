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
 * Autoloader checking if the class is really defined in the file found.
 *
 * The DebugClassLoader will wrap all registered autoloaders providing a
 * findFile method and will throw an exception if a file is found but does
 * not declare the class.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Christophe Coevoet <stof@notk.org>
 *
 * @api
 */
class Symfony_Component_ClassLoader_DebugClassLoader
{
    private static $classFinders = array();
    private static $registered = false;

    private $classFinder;

    /**
     * Constructor.
     *
     * @param object $classFinder
     *
     * @api
     */
    public function __construct($classFinder)
    {
        $this->classFinder = $classFinder;
    }

    /**
     * Replaces all autoloaders implementing a findFile method by a DebugClassLoader wrapper.
     */
    public static function enable()
    {
        if (!is_array($functions = spl_autoload_functions())) {
            return;
        }

        foreach ($functions as $key => $function) {
            if (version_compare(phpversion(), '5.2.11', '<')) {
                // http://bugs.php.net/44144
                if (is_array($function) && is_object($function[0])) {
                    $functions[$key] = null;

                    continue;
                }
            }

            spl_autoload_unregister($function);
        }

        foreach ($functions as $function) {
            if (null === $function) {
                continue;
            }

            if (is_array($function) && !$function[0] instanceof self && $function[0] !== 'Symfony_Component_ClassLoader_DebugClassLoader' && is_callable(array($function[0], 'findFile'))) {
                $r = new ReflectionMethod($function[0], 'findFile');
                if (1 === $r->getNumberOfRequiredParameters()) {
                    $parameters = $r->getParameters();
                    $param = $parameters[0];
                    if (!$param->getClass() && !$param->isArray()) {
                        // http://bugs.php.net/40091 and http://bugs.php.net/44144
                        if (version_compare(phpversion(), '5.2.11', '<')) {
                            self::$classFinders[] = $function[0];

                            if (true === self::$registered) {
                                continue;
                            }
                            self::$registered = true;

                            $function = array(__CLASS__, 'staticLoadClass');
                        } else {
                            $function = array(new self($function[0]), 'loadClass');
                        }
                    }
                }
            }

            spl_autoload_register($function);
        }
    }

    /**
     * Unregisters this instance as an autoloader.
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * Finds a file by class name
     *
     * @param string $class A class name to resolve to file
     *
     * @return string|null
     */
    public function findFile($class)
    {
        return call_user_func(array($this->classFinder, 'findFile'), $class);
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $class The name of the class
     *
     * @return Boolean|null True, if loaded
     *
     * @throws RuntimeException
     */
    public function loadClass($class)
    {
        if ($file = $this->findFile($class)) {
            require $file;

            if (!class_exists($class, false) && !interface_exists($class, false) && (!function_exists('trait_exists') || !trait_exists($class, false))) {
                if (false !== strpos($class, '/')) {
                    throw new RuntimeException(sprintf('Trying to autoload a class with an invalid name "%s". Be careful that the namespace separator is "\" in PHP, not "/".', $class));
                }

                throw new RuntimeException(sprintf('The autoloader expected class "%s" to be defined in file "%s". The file was found but the class was not in it, the class name or namespace probably has a typo.', $class, $file));
            }

            return true;
        }
    }

    /**
     * Finds a file by class name
     *
     * @param string $class A class name to resolve to file
     *
     * @return string|null
     */
    public static function staticfindFile($class)
    {
        foreach (self::$classFinders as $classFinder) {
            if ($file = call_user_func(array($classFinder, 'findFile'), $class)) {
                return $file;
            }
        }
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $class The name of the class
     *
     * @return Boolean|null True, if loaded
     *
     * @throws RuntimeException
     */
    public static function staticLoadClass($class)
    {
        if ($file = self::staticfindFile($class)) {
            require $file;

            if (!class_exists($class, false) && !interface_exists($class, false) && (!function_exists('trait_exists') || !trait_exists($class, false))) {
                if (false !== strpos($class, '/')) {
                    throw new RuntimeException(sprintf('Trying to autoload a class with an invalid name "%s". Be careful that the namespace separator is "\" in PHP, not "/".', $class));
                }

                throw new RuntimeException(sprintf('The autoloader expected class "%s" to be defined in file "%s". The file was found but the class was not in it, the class name or namespace probably has a typo.', $class, $file));
            }

            return true;
        }
    }
}
