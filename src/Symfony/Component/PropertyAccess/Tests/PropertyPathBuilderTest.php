<?php

/*
 * This file is new3 of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_PropertyAccess_Tests_PropertyPathBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    const PREFIX = 'old1[old2].old3[old4][old5].old6';

    /**
     * @var Symfony_Component_PropertyAccess_PropertyPathBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->builder = new Symfony_Component_PropertyAccess_PropertyPathBuilder(new Symfony_Component_PropertyAccess_PropertyPath(self::PREFIX));
    }

    public function testCreateEmpty()
    {
        $builder = new Symfony_Component_PropertyAccess_PropertyPathBuilder();

        $this->assertNull($builder->getPropertyPath());
    }

    public function testCreateCopyPath()
    {
        $this->assertEquals(new Symfony_Component_PropertyAccess_PropertyPath(self::PREFIX), $this->builder->getPropertyPath());
    }

    public function testAppendIndex()
    {
        $this->builder->appendIndex('new1');

        $path = new Symfony_Component_PropertyAccess_PropertyPath(self::PREFIX . '[new1]');

        $this->assertEquals($path, $this->builder->getPropertyPath());
    }

    public function testAppendProperty()
    {
        $this->builder->appendProperty('new1');

        $path = new Symfony_Component_PropertyAccess_PropertyPath(self::PREFIX . '.new1');

        $this->assertEquals($path, $this->builder->getPropertyPath());
    }

    public function testAppend()
    {
        $this->builder->append(new Symfony_Component_PropertyAccess_PropertyPath('new1[new2]'));

        $path = new Symfony_Component_PropertyAccess_PropertyPath(self::PREFIX . '.new1[new2]');

        $this->assertEquals($path, $this->builder->getPropertyPath());
    }

    public function testAppendWithOffset()
    {
        $this->builder->append(new Symfony_Component_PropertyAccess_PropertyPath('new1[new2].new3'), 1);

        $path = new Symfony_Component_PropertyAccess_PropertyPath(self::PREFIX . '[new2].new3');

        $this->assertEquals($path, $this->builder->getPropertyPath());
    }

    public function testAppendWithOffsetAndLength()
    {
        $this->builder->append(new Symfony_Component_PropertyAccess_PropertyPath('new1[new2].new3'), 1, 1);

        $path = new Symfony_Component_PropertyAccess_PropertyPath(self::PREFIX . '[new2]');

        $this->assertEquals($path, $this->builder->getPropertyPath());
    }

    public function testReplaceByIndex()
    {
        $this->builder->replaceByIndex(1, 'new1');

        $path = new Symfony_Component_PropertyAccess_PropertyPath('old1[new1].old3[old4][old5].old6');

        $this->assertEquals($path, $this->builder->getPropertyPath());
    }

    public function testReplaceByIndexWithoutName()
    {
        $this->builder->replaceByIndex(0);

        $path = new Symfony_Component_PropertyAccess_PropertyPath('[old1][old2].old3[old4][old5].old6');

        $this->assertEquals($path, $this->builder->getPropertyPath());
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testReplaceByIndexDoesNotAllowInvalidOffsets()
    {
        $this->builder->replaceByIndex(6, 'new1');
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testReplaceByIndexDoesNotAllowNegativeOffsets()
    {
        $this->builder->replaceByIndex(-1, 'new1');
    }

    public function testReplaceByProperty()
    {
        $this->builder->replaceByProperty(1, 'new1');

        $path = new Symfony_Component_PropertyAccess_PropertyPath('old1.new1.old3[old4][old5].old6');

        $this->assertEquals($path, $this->builder->getPropertyPath());
    }

    public function testReplaceByPropertyWithoutName()
    {
        $this->builder->replaceByProperty(1);

        $path = new Symfony_Component_PropertyAccess_PropertyPath('old1.old2.old3[old4][old5].old6');

        $this->assertEquals($path, $this->builder->getPropertyPath());
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testReplaceByPropertyDoesNotAllowInvalidOffsets()
    {
        $this->builder->replaceByProperty(6, 'new1');
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testReplaceByPropertyDoesNotAllowNegativeOffsets()
    {
        $this->builder->replaceByProperty(-1, 'new1');
    }

    public function testReplace()
    {
        $this->builder->replace(1, 1, new Symfony_Component_PropertyAccess_PropertyPath('new1[new2].new3'));

        $path = new Symfony_Component_PropertyAccess_PropertyPath('old1.new1[new2].new3.old3[old4][old5].old6');

        $this->assertEquals($path, $this->builder->getPropertyPath());
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testReplaceDoesNotAllowInvalidOffsets()
    {
        $this->builder->replace(6, 1, new Symfony_Component_PropertyAccess_PropertyPath('new1[new2].new3'));
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testReplaceDoesNotAllowNegativeOffsets()
    {
        $this->builder->replace(-1, 1, new Symfony_Component_PropertyAccess_PropertyPath('new1[new2].new3'));
    }

    public function testReplaceWithLengthGreaterOne()
    {
        $this->builder->replace(0, 2, new Symfony_Component_PropertyAccess_PropertyPath('new1[new2].new3'));

        $path = new Symfony_Component_PropertyAccess_PropertyPath('new1[new2].new3.old3[old4][old5].old6');

        $this->assertEquals($path, $this->builder->getPropertyPath());
    }

    public function testReplaceSubstring()
    {
        $this->builder->replace(1, 1, new Symfony_Component_PropertyAccess_PropertyPath('new1[new2].new3.new4[new5]'), 1, 3);

        $path = new Symfony_Component_PropertyAccess_PropertyPath('old1[new2].new3.new4.old3[old4][old5].old6');

        $this->assertEquals($path, $this->builder->getPropertyPath());
    }

    public function testReplaceSubstringWithLengthGreaterOne()
    {
        $this->builder->replace(1, 2, new Symfony_Component_PropertyAccess_PropertyPath('new1[new2].new3.new4[new5]'), 1, 3);

        $path = new Symfony_Component_PropertyAccess_PropertyPath('old1[new2].new3.new4[old4][old5].old6');

        $this->assertEquals($path, $this->builder->getPropertyPath());
    }

    // https://github.com/symfony/symfony/issues/5605
    public function testReplaceWithLongerPath()
    {
        // error occurs when path contains at least two more elements
        // than the builder
        $path = new Symfony_Component_PropertyAccess_PropertyPath('new1.new2.new3');

        $builder = new Symfony_Component_PropertyAccess_PropertyPathBuilder(new Symfony_Component_PropertyAccess_PropertyPath('old1'));
        $builder->replace(0, 1, $path);

        $this->assertEquals($path, $builder->getPropertyPath());
    }

    public function testRemove()
    {
        $this->builder->remove(3);

        $path = new Symfony_Component_PropertyAccess_PropertyPath('old1[old2].old3[old5].old6');

        $this->assertEquals($path, $this->builder->getPropertyPath());
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testRemoveDoesNotAllowInvalidOffsets()
    {
        $this->builder->remove(6);
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testRemoveDoesNotAllowNegativeOffsets()
    {
        $this->builder->remove(-1);
    }
}
