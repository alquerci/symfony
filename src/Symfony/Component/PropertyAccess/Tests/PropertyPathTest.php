<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_PropertyAccess_Tests_PropertyPathTest extends PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $path = new Symfony_Component_PropertyAccess_PropertyPath('reference.traversable[index].property');

        $this->assertEquals('reference.traversable[index].property', $path->__toString());
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_InvalidPropertyPathException
     */
    public function testDotIsRequiredBeforeProperty()
    {
        new Symfony_Component_PropertyAccess_PropertyPath('[index]property');
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_InvalidPropertyPathException
     */
    public function testDotCannotBePresentAtTheBeginning()
    {
        new Symfony_Component_PropertyAccess_PropertyPath('.property');
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_InvalidPropertyPathException
     */
    public function testUnexpectedCharacters()
    {
        new Symfony_Component_PropertyAccess_PropertyPath('property.$foo');
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_InvalidPropertyPathException
     */
    public function testPathCannotBeEmpty()
    {
        new Symfony_Component_PropertyAccess_PropertyPath('');
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_UnexpectedTypeException
     */
    public function testPathCannotBeNull()
    {
        new Symfony_Component_PropertyAccess_PropertyPath(null);
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_UnexpectedTypeException
     */
    public function testPathCannotBeFalse()
    {
        new Symfony_Component_PropertyAccess_PropertyPath(false);
    }

    public function testZeroIsValidPropertyPath()
    {
        new Symfony_Component_PropertyAccess_PropertyPath('0');
    }

    public function testGetParentWithDot()
    {
        $propertyPath = new Symfony_Component_PropertyAccess_PropertyPath('grandpa.parent.child');

        $this->assertEquals(new Symfony_Component_PropertyAccess_PropertyPath('grandpa.parent'), $propertyPath->getParent());
    }

    public function testGetParentWithIndex()
    {
        $propertyPath = new Symfony_Component_PropertyAccess_PropertyPath('grandpa.parent[child]');

        $this->assertEquals(new Symfony_Component_PropertyAccess_PropertyPath('grandpa.parent'), $propertyPath->getParent());
    }

    public function testGetParentWhenThereIsNoParent()
    {
        $propertyPath = new Symfony_Component_PropertyAccess_PropertyPath('path');

        $this->assertNull($propertyPath->getParent());
    }

    public function testCopyConstructor()
    {
        $propertyPath = new Symfony_Component_PropertyAccess_PropertyPath('grandpa.parent[child]');
        $copy = new Symfony_Component_PropertyAccess_PropertyPath($propertyPath);

        $this->assertEquals($propertyPath, $copy);
    }

    public function testGetElement()
    {
        $propertyPath = new Symfony_Component_PropertyAccess_PropertyPath('grandpa.parent[child]');

        $this->assertEquals('child', $propertyPath->getElement(2));
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testGetElementDoesNotAcceptInvalidIndices()
    {
        $propertyPath = new Symfony_Component_PropertyAccess_PropertyPath('grandpa.parent[child]');

        $propertyPath->getElement(3);
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testGetElementDoesNotAcceptNegativeIndices()
    {
        $propertyPath = new Symfony_Component_PropertyAccess_PropertyPath('grandpa.parent[child]');

        $propertyPath->getElement(-1);
    }

    public function testIsProperty()
    {
        $propertyPath = new Symfony_Component_PropertyAccess_PropertyPath('grandpa.parent[child]');

        $this->assertTrue($propertyPath->isProperty(1));
        $this->assertFalse($propertyPath->isProperty(2));
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testIsPropertyDoesNotAcceptInvalidIndices()
    {
        $propertyPath = new Symfony_Component_PropertyAccess_PropertyPath('grandpa.parent[child]');

        $propertyPath->isProperty(3);
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testIsPropertyDoesNotAcceptNegativeIndices()
    {
        $propertyPath = new Symfony_Component_PropertyAccess_PropertyPath('grandpa.parent[child]');

        $propertyPath->isProperty(-1);
    }

    public function testIsIndex()
    {
        $propertyPath = new Symfony_Component_PropertyAccess_PropertyPath('grandpa.parent[child]');

        $this->assertFalse($propertyPath->isIndex(1));
        $this->assertTrue($propertyPath->isIndex(2));
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testIsIndexDoesNotAcceptInvalidIndices()
    {
        $propertyPath = new Symfony_Component_PropertyAccess_PropertyPath('grandpa.parent[child]');

        $propertyPath->isIndex(3);
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testIsIndexDoesNotAcceptNegativeIndices()
    {
        $propertyPath = new Symfony_Component_PropertyAccess_PropertyPath('grandpa.parent[child]');

        $propertyPath->isIndex(-1);
    }
}
