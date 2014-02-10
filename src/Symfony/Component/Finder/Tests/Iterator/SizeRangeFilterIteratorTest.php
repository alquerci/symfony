<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Finder_Tests_Iterator_SizeRangeFilterIteratorTest extends Symfony_Component_Finder_Tests_Iterator_RealIteratorTestCase
{
    /**
     * @dataProvider getAcceptData
     */
    public function testAccept($size, $expected)
    {
        $inner = new Symfony_Component_Finder_Tests_Iterator_InnerSizeIterator(self::$files);

        $iterator = new Symfony_Component_Finder_Iterator_SizeRangeFilterIterator($inner, $size);

        $this->assertIterator($expected, $iterator);
    }

    public function getAcceptData()
    {
        $lessThan1KGreaterThan05K = array(
            '.foo',
            '.git',
            'foo',
            'test.php',
            'toto',
        );

        return array(
            array(array(new Symfony_Component_Finder_Comparator_NumberComparator('< 1K'), new Symfony_Component_Finder_Comparator_NumberComparator('> 0.5K')), $this->toAbsolute($lessThan1KGreaterThan05K)),
        );
    }
}

class Symfony_Component_Finder_Tests_Iterator_InnerSizeIterator extends ArrayIterator
{
   public function current()
    {
        return new Symfony_Component_Finder_SplFileInfo(parent::current(), null, null);
    }

    public function getFilename()
    {
        return parent::current();
    }

    public function isFile()
    {
        return $this->current()->isFile();
    }

    public function getSize()
    {
        return $this->current()->getSize();
    }
}
