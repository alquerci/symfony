<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Finder_Tests_Iterator_FilenameFilterIteratorTest extends Symfony_Component_Finder_Tests_Iterator_IteratorTestCase
{
    /**
     * @dataProvider getAcceptData
     */
    public function testAccept($matchPatterns, $noMatchPatterns, $expected)
    {
        $inner = new Symfony_Component_Finder_Tests_Iterator_InnerNameIterator(array('test.php', 'test.py', 'foo.php'));

        $iterator = new Symfony_Component_Finder_Iterator_FilenameFilterIterator($inner, $matchPatterns, $noMatchPatterns);

        $this->assertIterator($expected, $iterator);
    }

    public function getAcceptData()
    {
        return array(
            array(array('test.*'), array(), array('test.php', 'test.py')),
            array(array(), array('test.*'), array('foo.php')),
            array(array('*.php'), array('test.*'), array('foo.php')),
            array(array('*.php', '*.py'), array('foo.*'), array('test.php', 'test.py')),
            array(array('/\.php$/'), array(), array('test.php', 'foo.php')),
            array(array(), array('/\.php$/'), array('test.py')),
        );
    }
}

class Symfony_Component_Finder_Tests_Iterator_InnerNameIterator extends ArrayIterator
{
    public function current()
    {
        return new SplFileInfo(parent::current());
    }

    public function getFilename()
    {
        return parent::current();
    }
}
