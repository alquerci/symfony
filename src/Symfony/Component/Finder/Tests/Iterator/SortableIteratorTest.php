<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Finder_Tests_Iterator_SortableIteratorTest extends Symfony_Component_Finder_Tests_Iterator_RealIteratorTestCase
{
    public function testConstructor()
    {
        try {
            new Symfony_Component_Finder_Iterator_SortableIterator(new Symfony_Component_Finder_Tests_Iterator_Iterator(array()), 'foobar');
            $this->fail('__construct() throws an InvalidArgumentException exception if the mode is not valid');
        } catch (Exception $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e, '__construct() throws an InvalidArgumentException exception if the mode is not valid');
        }
    }

    /**
     * @dataProvider getAcceptData
     */
    public function testAccept($mode, $expected)
    {
        $inner = new Symfony_Component_Finder_Tests_Iterator_Iterator(self::$files);

        $iterator = new Symfony_Component_Finder_Iterator_SortableIterator($inner, $mode);

        $this->assertOrderedIterator($expected, $iterator);
    }

    public function getAcceptData()
    {

        $sortByName = array(
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            '.git',
            'foo',
            'foo bar',
            'foo/bar.tmp',
            'test.php',
            'test.py',
            'toto',
        );

        $sortByType = array(
            '.foo',
            '.git',
            'foo',
            'toto',
            '.bar',
            '.foo/.bar',
            '.foo/bar',
            'foo bar',
            'foo/bar.tmp',
            'test.php',
            'test.py',
        );

        $customComparison = array(
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            '.git',
            'foo',
            'foo bar',
            'foo/bar.tmp',
            'test.php',
            'test.py',
            'toto',
        );

        return array(
            array(Symfony_Component_Finder_Iterator_SortableIterator::SORT_BY_NAME, $this->toAbsolute($sortByName)),
            array(Symfony_Component_Finder_Iterator_SortableIterator::SORT_BY_TYPE, $this->toAbsolute($sortByType)),
            array(create_function('SplFileInfo $a, SplFileInfo $b', 'return strcmp(realpath($a->getPathname()), realpath($b->getPathname()));'), $this->toAbsolute($customComparison)),
        );
    }
}
