<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Finder_Tests_Iterator_CustomFilterIteratorTest extends Symfony_Component_Finder_Tests_Iterator_IteratorTestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithInvalidFilter()
    {
        new Symfony_Component_Finder_Iterator_CustomFilterIterator(new Symfony_Component_Finder_Tests_Iterator_Iterator(), array('foo'));
    }

    /**
     * @dataProvider getAcceptData
     */
    public function testAccept($filters, $expected)
    {
        $inner = new Symfony_Component_Finder_Tests_Iterator_Iterator(array('test.php', 'test.py', 'foo.php'));

        $iterator = new Symfony_Component_Finder_Iterator_CustomFilterIterator($inner, $filters);

        $this->assertIterator($expected, $iterator);
    }

    public function getAcceptData()
    {
        return array(
            array(array(create_function('SplFileInfo $fileinfo', 'return false;')), array()),
            array(array(create_function('SplFileInfo $fileinfo', 'return preg_match("/^test/", $fileinfo) > 0;')), array('test.php', 'test.py')),
            array(array('is_dir'), array()),
        );
    }
}
