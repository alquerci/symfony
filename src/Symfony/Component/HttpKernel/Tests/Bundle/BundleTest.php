<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_Bundle_BundleTest extends PHPUnit_Framework_TestCase
{
    public function testRegisterCommands()
    {
        if (!class_exists('Symfony_Component_Console_Application')) {
            $this->markTestSkipped('The "Console" component is not available');
        }

        if (!interface_exists('Symfony_Component_DependencyInjection_ContainerAwareInterface')) {
            $this->markTestSkipped('The "DependencyInjection" component is not available');
        }

        if (!class_exists('Symfony_Component_Finder_Finder')) {
            $this->markTestSkipped('The "Finder" component is not available');
        }

        $cmd = new Symfony_Component_HttpKernel_Tests_Fixtures_ExtensionPresentBundle_Command_FooCommand();
        $app = $this->getMock('Symfony_Component_Console_Application');
        $app->expects($this->once())->method('add')->with($this->equalTo($cmd));

        $bundle = new Symfony_Component_HttpKernel_Tests_Fixtures_ExtensionPresentBundle_ExtensionPresentBundle();
        $bundle->registerCommands($app);

        $bundle2 = new Symfony_Component_HttpKernel_Tests_Fixtures_ExtensionAbsentBundle_ExtensionAbsentBundle();

        $this->assertNull($bundle2->registerCommands($app));

    }
}
