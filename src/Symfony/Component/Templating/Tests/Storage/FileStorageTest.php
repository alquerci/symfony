<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Templating_Tests_Storage_FileStorageTest extends PHPUnit_Framework_TestCase
{
    public function testGetContent()
    {
        $storage = new Symfony_Component_Templating_Storage_FileStorage('foo');
        $this->assertInstanceOf('Symfony_Component_Templating_Storage_Storage', $storage, 'Symfony_Component_Templating_Storage_FileStorage is an instance of Symfony_Component_Templating_Storage_Storage');
        $storage = new Symfony_Component_Templating_Storage_FileStorage(dirname(__FILE__).'/../Fixtures/templates/foo.php');
        $this->assertEquals('<?php echo $foo ?>'."\n", $storage->getContent(), '->getContent() returns the content of the template');
    }
}
