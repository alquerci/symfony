<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Alex Bogomazov
 */
class Symfony_Component_Finder_Tests_Iterator_FilterIteratorTest extends Symfony_Component_Finder_Tests_Iterator_RealIteratorTestCase
{
    public function testFilterFilesystemIterators()
    {
        $i = new FilesystemIterator($this->toAbsolute());

        // it is expected that there are test.py test.php in the tmpDir
        $i = $this->getMockForAbstractClass('Symfony_Component_Finder_Iterator_FilterIterator', array($i));
        $i->expects($this->any())
            ->method('accept')
            ->will($this->returnCallback(array(new Symfony_Component_Finder_Tests_Iterator_FilterIteratorTestClosure($i), '__invoke'))
        );

        $c = 0;
        foreach ($i as $item) {
            $c++;
        }

        $this->assertEquals(1, $c);

        $i->rewind();

        $c = 0;
        foreach ($i as $item) {
            $c++;
        }

        // This would fail with FilterIterator but works with Symfony_Component_Finder_Iterator_FilterIterator
        // see https://bugs.php.net/bug.php?id=49104
        $this->assertEquals(1, $c);
    }
}

class Symfony_Component_Finder_Tests_Iterator_FilterIteratorTestClosure
{
    private $i;

    public function __construct($i)
    {
        $this->i = $i;
    }

    public function __invoke()
    {
        return (bool) preg_match('/\.php/', (string) $this->i->current());
    }
}
