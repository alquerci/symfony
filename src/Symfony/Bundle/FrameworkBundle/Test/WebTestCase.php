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
 * WebTestCase is the base class for functional tests.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Symfony_Bundle_FrameworkBundle_Test_WebTestCase extends PHPUnit_Framework_TestCase
{
    private static $class;
    private static $kernel;

    /**
     * Creates a Client.
     *
     * @param array $options An array of options to pass to the createKernel class
     * @param array $server  An array of server parameters
     *
     * @return Symfony_Bundle_FrameworkBundle_Client A Client instance
     */
    protected function createClient(array $options = array(), array $server = array())
    {
        if (null !== self::$kernel) {
            self::$kernel->shutdown();
        }

        self::$kernel = $this->createKernel($options);
        self::$kernel->boot();

        $client = self::$kernel->getContainer()->get('test.client');
        $client->setServerParameters($server);

        return $client;
    }

    /**
     * Finds the directory where the phpunit.xml(.dist) is stored.
     *
     * If you run tests with the PHPUnit CLI tool, everything will work as expected.
     * If not, override this method in your test classes.
     *
     * @return string The directory where phpunit.xml(.dist) is stored
     *
     * @throws RuntimeException
     */
    protected function getPhpUnitXmlDir()
    {
        if (!isset($_SERVER['argv']) || false === strpos($_SERVER['argv'][0], 'phpunit')) {
            throw new RuntimeException('You must override the WebTestCase::createKernel() method.');
        }

        $dir = $this->getPhpUnitCliConfigArgument();
        if ($dir === null &&
            (is_file(getcwd().DIRECTORY_SEPARATOR.'phpunit.xml') ||
            is_file(getcwd().DIRECTORY_SEPARATOR.'phpunit.xml.dist'))) {
            $dir = getcwd();
        }

        // Can't continue
        if ($dir === null) {
            throw new RuntimeException('Unable to guess the Kernel directory.');
        }

        if (!is_dir($dir)) {
            $dir = dirname($dir);
        }

        return $dir;
    }

    /**
     * Finds the value of the CLI configuration option.
     *
     * PHPUnit will use the last configuration argument on the command line, so this only returns
     * the last configuration argument.
     *
     * @return string The value of the PHPUnit cli configuration option
     */
    private function getPhpUnitCliConfigArgument()
    {
        $dir = null;
        $reversedArgs = array_reverse($_SERVER['argv']);
        foreach ($reversedArgs as $argIndex => $testArg) {
            if (preg_match('/^-[^ \-]*c$/', $testArg) || $testArg === '--configuration') {
                $dir = realpath($reversedArgs[$argIndex - 1]);
                break;
            } elseif (strpos($testArg, '--configuration=') === 0) {
                $argPath = substr($testArg, strlen('--configuration='));
                $dir = realpath($argPath);
                break;
            }
        }

        return $dir;
    }

    /**
     * Attempts to guess the kernel location.
     *
     * When the Kernel is located, the file is required.
     *
     * @return string The Kernel class name
     *
     * @throws RuntimeException
     */
    protected function getKernelClass()
    {
        $dir = isset($_SERVER['KERNEL_DIR']) ? $_SERVER['KERNEL_DIR'] : $this->getPhpUnitXmlDir();

        $finder = new Symfony_Component_Finder_Finder();
        $finder->name('*Kernel.php')->depth(0)->in($dir);
        $results = iterator_to_array($finder);
        if (!count($results)) {
            throw new RuntimeException('Either set KERNEL_DIR in your phpunit.xml according to http://symfony.com/doc/current/book/testing.html#your-first-functional-test or override the WebTestCase::createKernel() method.');
        }

        $file = current($results);
        $class = $file->getBasename('.php');

        require_once $file;

        return $class;
    }

    /**
     * Creates a Kernel.
     *
     * Available options:
     *
     *  * environment
     *  * debug
     *
     * @param array $options An array of options
     *
     * @return Symfony_Component_HttpKernel_HttpKernelInterface A HttpKernelInterface instance
     */
    protected function createKernel(array $options = array())
    {
        if (null === self::$class) {
            self::$class = $this->getKernelClass();
        }

        return new self::$class(
            isset($options['environment']) ? $options['environment'] : 'test',
            isset($options['debug']) ? $options['debug'] : true
        );
    }

    /**
     * Shuts the kernel down if it was used in the test.
     */
    protected function tearDown()
    {
        if (null !== self::$kernel) {
            self::$kernel->shutdown();
        }
    }
}
