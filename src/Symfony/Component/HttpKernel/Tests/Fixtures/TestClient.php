<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_Fixtures_TestClient extends Symfony_Component_HttpKernel_Client
{
    protected function getScript($request)
    {
        $script = parent::getScript($request);

        $autoload = file_exists(dirname(__FILE__).'/../../vendor/autoload.php')
            ? dirname(__FILE__).'/../../vendor/autoload.php'
            : dirname(__FILE__).'/../../../../../../vendor/autoload.php'
        ;

        $script = preg_replace('/(\->register\(\);)/', "$0\nrequire_once '$autoload';\n", $script);

        return $script;
    }

    public function filterResponse($response)
    {
        return parent::filterResponse($response);
    }
}
