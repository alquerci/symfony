<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Functional_WebTestCase extends Symfony_Bundle_FrameworkBundle_Test_WebTestCase
{
    public static function assertRedirect($response, $location)
    {
        self::assertTrue($response->isRedirect(), 'Response is not a redirect, got status code: '.$response->getStatusCode());
        self::assertEquals('http://localhost'.$location, $response->headers->get('Location'));
    }

    protected function setUp()
    {
//         if (!class_exists('Twig_Environment')) {
//             $this->markTestSkipped('Twig is not available.');
//         }

        parent::setUp();
    }

    protected function deleteTmpDir($testCase)
    {
        if (!file_exists($dir = sys_get_temp_dir().'/'.Symfony_Component_HttpKernel_Kernel::VERSION.'/'.$testCase)) {
            return;
        }

        $fs = new Symfony_Component_Filesystem_Filesystem();
        $fs->remove($dir);
    }

    protected function getKernelClass()
    {
        require_once dirname(__FILE__).'/app/AppKernel.php';

        return 'Symfony_Bundle_FrameworkBundle_Tests_Functional_AppKernel';
    }

    protected function createKernel(array $options = array())
    {
        $class = $this->getKernelClass();

        if (!isset($options['test_case'])) {
            throw new InvalidArgumentException('The option "test_case" must be set.');
        }

        return new $class(
            $options['test_case'],
            isset($options['root_config']) ? $options['root_config'] : 'config.yml',
            isset($options['environment']) ? $options['environment'] : 'frameworkbundletest'.strtolower($options['test_case']),
            isset($options['debug']) ? $options['debug'] : true
        );
    }
}
