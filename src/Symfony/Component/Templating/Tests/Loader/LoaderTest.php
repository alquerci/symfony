<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Templating_Tests_Loader_LoaderTest extends PHPUnit_Framework_TestCase
{
    public function testGetSetDebugger()
    {
        $loader = new Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader4(new Symfony_Component_Templating_TemplateNameParser());
        $loader->setDebugger($debugger = new Symfony_Component_Templating_Tests_Fixtures_ProjectTemplateDebugger());
        $this->assertTrue($loader->getDebugger() === $debugger, '->setDebugger() sets the debugger instance');
    }
}

class Symfony_Component_Templating_Tests_Loader_ProjectTemplateLoader4 extends Symfony_Component_Templating_Loader_Loader
{
    public function load(Symfony_Component_Templating_TemplateReferenceInterface $template)
    {
    }

    public function getDebugger()
    {
        return $this->debugger;
    }

    public function isFresh(Symfony_Component_Templating_TemplateReferenceInterface $template, $time)
    {
        return false;
    }
}
