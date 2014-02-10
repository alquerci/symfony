<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Finder_Tests_Iterator_ExcludeDirectoryFilterIteratorTest extends Symfony_Component_Finder_Tests_Iterator_RealIteratorTestCase
{
    /**
     * @dataProvider getAcceptData
     */
    public function testAccept($directories, $expected)
    {
        $inner = new RecursiveIteratorIterator(new Symfony_Component_Finder_Iterator_RecursiveDirectoryIterator($this->toAbsolute(), FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);

        $iterator = new Symfony_Component_Finder_Iterator_ExcludeDirectoryFilterIterator($inner, $directories);

        $this->assertIterator($expected, $iterator);
    }

    public function getAcceptData()
    {
        $foo = array(
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            '.git',
            'test.py',
            'test.php',
            'toto',
            'foo bar'
        );

        $fo = array(
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            '.git',
            'test.py',
            'foo',
            'foo/bar.tmp',
            'test.php',
            'toto',
            'foo bar'
        );

        return array(
            array(array('foo'), $this->toAbsolute($foo)),
            array(array('fo'), $this->toAbsolute($fo)),
        );
    }

}
