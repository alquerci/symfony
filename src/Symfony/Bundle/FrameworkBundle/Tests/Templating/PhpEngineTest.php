<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Templating_PhpEngineTest extends Symfony_Bundle_FrameworkBundle_Tests_TestCase
{
    public function testEvaluateAddsAppGlobal()
    {
        $container = $this->getContainer();
        $loader = $this->getMockForAbstractClass('Symfony_Component_Templating_Loader_Loader');
        $engine = new Symfony_Bundle_FrameworkBundle_Templating_PhpEngine(new Symfony_Component_Templating_TemplateNameParser(), $container, $loader, $app = new Symfony_Bundle_FrameworkBundle_Templating_GlobalVariables($container));
        $globals = $engine->getGlobals();
        $this->assertSame($app, $globals['app']);
    }

    public function testEvaluateWithoutAvailableRequest()
    {
        $container = new Symfony_Component_DependencyInjection_Container();
        $loader = $this->getMockForAbstractClass('Symfony_Component_Templating_Loader_Loader');
        $engine = new Symfony_Bundle_FrameworkBundle_Templating_PhpEngine(new Symfony_Component_Templating_TemplateNameParser(), $container, $loader, new Symfony_Bundle_FrameworkBundle_Templating_GlobalVariables($container));

        $container->set('request', null);

        $globals = $engine->getGlobals();
        $this->assertEmpty($globals['app']->getRequest());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetInvalidHelper()
    {
        $container = $this->getContainer();
        $loader = $this->getMockForAbstractClass('Symfony_Component_Templating_Loader_Loader');
        $engine = new Symfony_Bundle_FrameworkBundle_Templating_PhpEngine(new Symfony_Component_Templating_TemplateNameParser(), $container, $loader);

        $engine->get('non-existing-helper');
    }

    /**
     * Creates a Container with a Session-containing Request service.
     *
     * @return Symfony_Component_DependencyInjection_Container
     */
    protected function getContainer()
    {
        $container = new Symfony_Component_DependencyInjection_Container();
        $request = new Symfony_Component_HttpFoundation_Request();
        $session = new Symfony_Component_HttpFoundation_Session_Session(new Symfony_Component_HttpFoundation_Session_Storage_MockArraySessionStorage());

        $request->setSession($session);
        $container->set('request', $request);

        return $container;
    }
}
