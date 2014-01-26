<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_ClassLoader_Tests_DebugClassLoaderTest extends PHPUnit_Framework_TestCase
{
    private $loader;

    protected function setUp()
    {
        $this->loader = new Symfony_Component_ClassLoader_ClassLoader();
        spl_autoload_register(array($this->loader, 'loadClass'));
    }

    protected function tearDown()
    {
        spl_autoload_unregister(array($this->loader, 'loadClass'));
    }

    public function testIdempotence()
    {
        Symfony_Component_ClassLoader_DebugClassLoader::enable();
        Symfony_Component_ClassLoader_DebugClassLoader::enable();

        $functions = spl_autoload_functions();
        foreach ($functions as $function) {
            if (is_array($function) && $function[0] instanceof Symfony_Component_ClassLoader_DebugClassLoader) {
                $this->assertNotInstanceOf('Symfony_Component_ClassLoader_DebugClassLoader', $this->readAttribute($function[0], 'classFinder'));
                return;
            } elseif (is_array($function) && ($function[0] === 'Symfony_Component_ClassLoader_DebugClassLoader' || is_subclass_of($function[0], 'Symfony_Component_ClassLoader_DebugClassLoader'))) {
                $classFinders = $this->readAttribute($function[0], 'classFinders');

                foreach ($classFinders as $classFinder) {
                    $this->assertNotInstanceOf('Symfony_Component_ClassLoader_DebugClassLoader', $classFinder);
                    $this->assertFalse('Symfony_Component_ClassLoader_DebugClassLoader' === $classFinder);
                    $this->assertFalse(is_subclass_of($classFinder, 'Symfony_Component_ClassLoader_DebugClassLoader'));
                }

                return;
            }
        }

        throw new Exception('DebugClassLoader did not register');
    }

    public static function assertNotInstanceOf($expected, $actual, $message = '')
    {
        self::assertThat($actual, self::logicalNot(self::isInstanceOf($expected)), $message);
    }
}
