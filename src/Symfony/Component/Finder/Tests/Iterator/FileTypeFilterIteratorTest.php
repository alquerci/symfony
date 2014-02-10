<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Finder_Tests_Iterator_FileTypeFilterIteratorTest extends Symfony_Component_Finder_Tests_Iterator_RealIteratorTestCase
{
    /**
     * @dataProvider getAcceptData
     */
    public function testAccept($mode, $expected)
    {
        $inner = new Symfony_Component_Finder_Tests_Iterator_InnerTypeIterator(self::$files);

        $iterator = new Symfony_Component_Finder_Iterator_FileTypeFilterIterator($inner, $mode);

        $this->assertIterator($expected, $iterator);
    }

    public function getAcceptData()
    {
        $onlyFiles = array(
            'test.py',
            'foo/bar.tmp',
            'test.php',
            '.bar',
            '.foo/.bar',
            '.foo/bar',
            'foo bar',
        );

        $onlyDirectories = array(
            '.git',
            'foo',
            'toto',
            '.foo',
        );

        return array(
            array(Symfony_Component_Finder_Iterator_FileTypeFilterIterator::ONLY_FILES, $this->toAbsolute($onlyFiles)),
            array(Symfony_Component_Finder_Iterator_FileTypeFilterIterator::ONLY_DIRECTORIES, $this->toAbsolute($onlyDirectories)),
        );
    }
}

class Symfony_Component_Finder_Tests_Iterator_InnerTypeIterator extends ArrayIterator
{
   public function current()
    {
        return new Symfony_Component_Finder_SplFileInfo(parent::current(), null, null);
    }

    public function isFile()
    {
        return $this->current()->isFile();
    }

    public function isDir()
    {
        return $this->current()->isDir();
    }
}
