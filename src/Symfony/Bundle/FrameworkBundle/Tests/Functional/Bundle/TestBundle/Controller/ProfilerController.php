<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Functional_Bundle_TestBundle_Controller_ProfilerController extends Symfony_Component_DependencyInjection_ContainerAware
{
    public function indexAction()
    {
        return new Symfony_Component_HttpFoundation_Response('Hello');
    }
}
