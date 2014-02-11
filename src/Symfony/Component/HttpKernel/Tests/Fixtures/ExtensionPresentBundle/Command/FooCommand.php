<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_Fixtures_ExtensionPresentBundle_Command_FooCommand extends Symfony_Component_Console_Command_Command
{
    protected function configure()
    {
        $this->setName('foo');
    }

}
