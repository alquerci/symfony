<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// get the autoload file
$dir = dirname(__FILE__);
$lastDir = null;
while ($dir !== $lastDir) {
    $lastDir = $dir;

    if (file_exists($dir.'/autoload.php')) {
        require_once $dir.'/autoload.php';
        break;
    }

    if (file_exists($dir.'/autoload.php.dist')) {
        require_once $dir.'/autoload.php.dist';
        break;
    }

    $dir = dirname($dir);
}

/**
 * App Test Kernel for functional tests.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Bundle_FrameworkBundle_Tests_Functional_AppKernel extends Symfony_Component_HttpKernel_Kernel
{
    private $testCase;
    private $rootConfig;

    public function __construct($testCase, $rootConfig, $environment, $debug)
    {
        if (!is_dir(dirname(__FILE__).'/'.$testCase)) {
            throw new InvalidArgumentException(sprintf('The test case "%s" does not exist.', $testCase));
        }
        $this->testCase = $testCase;

        $fs = new Symfony_Component_Filesystem_Filesystem();
        if (!$fs->isAbsolutePath($rootConfig) && !file_exists($rootConfig = dirname(__FILE__).'/'.$testCase.'/'.$rootConfig)) {
            throw new InvalidArgumentException(sprintf('The root config "%s" does not exist.', $rootConfig));
        }
        $this->rootConfig = $rootConfig;

        parent::__construct($environment, $debug);
    }

    public function registerBundles()
    {
        if (!file_exists($filename = $this->getRootDir().'/'.$this->testCase.'/bundles.php')) {
            throw new RuntimeException(sprintf('The bundles file "%s" does not exist.', $filename));
        }

        return include $filename;
    }

    public function init()
    {
    }

    public function getRootDir()
    {
        return dirname(__FILE__);
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/'.Symfony_Component_HttpKernel_Kernel::VERSION.'/'.$this->testCase.'/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return sys_get_temp_dir().'/'.Symfony_Component_HttpKernel_Kernel::VERSION.'/'.$this->testCase.'/logs';
    }

    public function registerContainerConfiguration(Symfony_Component_Config_Loader_LoaderInterface $loader)
    {
        $loader->load($this->rootConfig);
    }

    public function serialize()
    {
        return serialize(array($this->testCase, $this->rootConfig, $this->getEnvironment(), $this->isDebug()));
    }

    public function unserialize($str)
    {
        call_user_func_array(array($this, '__construct'), unserialize($str));
    }

    protected function getKernelParameters()
    {
        $parameters = parent::getKernelParameters();
        $parameters['kernel.test_case'] = $this->testCase;

        return $parameters;
    }

    protected function buildContainer()
    {
        foreach (array('cache' => $this->getCacheDir(), 'logs' => $this->getLogDir()) as $name => $dir) {
            if (!is_dir($dir)) {
                // on windows cannot create recursive directory with slash before PHP 5.2
                if (false === @mkdir(str_replace('/', DIRECTORY_SEPARATOR, $dir), 0777, true)) {
                    throw new RuntimeException(sprintf("Unable to create the %s directory (%s)\n", $name, $dir));
                }
            } elseif (!is_writable($dir)) {
                throw new RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", $name, $dir));
            }
        }

        return parent::buildContainer();
    }
}
