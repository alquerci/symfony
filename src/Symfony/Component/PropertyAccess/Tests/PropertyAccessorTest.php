<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_PropertyAccess_Tests_PropertyAccessorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony_Component_PropertyAccess_PropertyAccessor
     */
    private $propertyAccessor;

    protected function setUp()
    {
        $this->propertyAccessor = new Symfony_Component_PropertyAccess_PropertyAccessor();
    }

    public function testGetValueReadsArray()
    {
        $array = array('firstName' => 'Bernhard');

        $this->assertEquals('Bernhard', $this->propertyAccessor->getValue($array, '[firstName]'));
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_NoSuchPropertyException
     */
    public function testGetValueThrowsExceptionIfIndexNotationExpected()
    {
        $array = array('firstName' => 'Bernhard');

        $this->propertyAccessor->getValue($array, 'firstName');
    }

    public function testGetValueReadsZeroIndex()
    {
        $array = array('Bernhard');

        $this->assertEquals('Bernhard', $this->propertyAccessor->getValue($array, '[0]'));
    }

    public function testGetValueReadsIndexWithSpecialChars()
    {
        $array = array('%!@$§.' => 'Bernhard');

        $this->assertEquals('Bernhard', $this->propertyAccessor->getValue($array, '[%!@$§.]'));
    }

    public function testGetValueReadsNestedIndexWithSpecialChars()
    {
        $array = array('root' => array('%!@$§.' => 'Bernhard'));

        $this->assertEquals('Bernhard', $this->propertyAccessor->getValue($array, '[root][%!@$§.]'));
    }

    public function testGetValueReadsArrayWithCustomPropertyPath()
    {
        $array = array('child' => array('index' => array('firstName' => 'Bernhard')));

        $this->assertEquals('Bernhard', $this->propertyAccessor->getValue($array, '[child][index][firstName]'));
    }

    public function testGetValueReadsArrayWithMissingIndexForCustomPropertyPath()
    {
        $array = array('child' => array('index' => array()));

        $this->assertNull($this->propertyAccessor->getValue($array, '[child][index][firstName]'));
    }

    public function testGetValueReadsProperty()
    {
        $object = new Symfony_Component_PropertyAccess_Tests_Fixtures_Author();
        $object->firstName = 'Bernhard';

        $this->assertEquals('Bernhard', $this->propertyAccessor->getValue($object, 'firstName'));
    }

    public function testGetValueIgnoresSingular()
    {
        $this->markTestSkipped('This feature is temporarily disabled as of 2.1');

        $object = (object) array('children' => 'Many');

        $this->assertEquals('Many', $this->propertyAccessor->getValue($object, 'children|child'));
    }

    public function testGetValueReadsPropertyWithSpecialCharsExceptDot()
    {
        $array = (object) array('%!@$§' => 'Bernhard');

        $this->assertEquals('Bernhard', $this->propertyAccessor->getValue($array, '%!@$§'));
    }

    public function testGetValueReadsPropertyWithCustomPropertyPath()
    {
        $object = new Symfony_Component_PropertyAccess_Tests_Fixtures_Author();
        $object->child = array();
        $object->child['index'] = new Symfony_Component_PropertyAccess_Tests_Fixtures_Author();
        $object->child['index']->firstName = 'Bernhard';

        $this->assertEquals('Bernhard', $this->propertyAccessor->getValue($object, 'child[index].firstName'));
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_PropertyAccessDeniedException
     */
    public function testGetValueThrowsExceptionIfPropertyIsNotPublic()
    {
        $this->propertyAccessor->getValue(new Symfony_Component_PropertyAccess_Tests_Fixtures_Author(), 'privateProperty');
    }

    public function testGetValueReadsGetters()
    {
        $object = new Symfony_Component_PropertyAccess_Tests_Fixtures_Author();
        $object->setLastName('Schussek');

        $this->assertEquals('Schussek', $this->propertyAccessor->getValue($object, 'lastName'));
    }

    public function testGetValueCamelizesGetterNames()
    {
        $object = new Symfony_Component_PropertyAccess_Tests_Fixtures_Author();
        $object->setLastName('Schussek');

        $this->assertEquals('Schussek', $this->propertyAccessor->getValue($object, 'last_name'));
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_PropertyAccessDeniedException
     */
    public function testGetValueThrowsExceptionIfGetterIsNotPublic()
    {
        $this->propertyAccessor->getValue(new Symfony_Component_PropertyAccess_Tests_Fixtures_Author(), 'privateGetter');
    }

    public function testGetValueReadsIssers()
    {
        $object = new Symfony_Component_PropertyAccess_Tests_Fixtures_Author();
        $object->setAustralian(false);

        $this->assertFalse($this->propertyAccessor->getValue($object, 'australian'));
    }

    public function testGetValueReadHassers()
    {
        $object = new Symfony_Component_PropertyAccess_Tests_Fixtures_Author();
        $object->setReadPermissions(true);

        $this->assertTrue($this->propertyAccessor->getValue($object, 'read_permissions'));
    }

    public function testGetValueReadsMagicGet()
    {
        $object = new Symfony_Component_PropertyAccess_Tests_Fixtures_Magician();
        $object->__set('magicProperty', 'foobar');

        $this->assertSame('foobar', $this->propertyAccessor->getValue($object, 'magicProperty'));
    }

    /*
     * https://github.com/symfony/symfony/pull/4450
     */
    public function testGetValueReadsMagicGetThatReturnsConstant()
    {
        $object = new Symfony_Component_PropertyAccess_Tests_Fixtures_Magician();

        $this->assertNull($this->propertyAccessor->getValue($object, 'magicProperty'));
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_PropertyAccessDeniedException
     */
    public function testGetValueThrowsExceptionIfIsserIsNotPublic()
    {
        $this->propertyAccessor->getValue(new Symfony_Component_PropertyAccess_Tests_Fixtures_Author(), 'privateIsser');
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_NoSuchPropertyException
     */
    public function testGetValueThrowsExceptionIfPropertyDoesNotExist()
    {
        $this->propertyAccessor->getValue(new Symfony_Component_PropertyAccess_Tests_Fixtures_Author(), 'foobar');
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_UnexpectedTypeException
     */
    public function testGetValueThrowsExceptionIfNotObjectOrArray()
    {
        $this->propertyAccessor->getValue('baz', 'foobar');
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_UnexpectedTypeException
     */
    public function testGetValueThrowsExceptionIfNull()
    {
        $this->propertyAccessor->getValue(null, 'foobar');
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_UnexpectedTypeException
     */
    public function testGetValueThrowsExceptionIfEmpty()
    {
        $this->propertyAccessor->getValue('', 'foobar');
    }

    public function testSetValueUpdatesArrays()
    {
        $array = array();

        $this->propertyAccessor->setValue($array, '[firstName]', 'Bernhard');

        $this->assertEquals(array('firstName' => 'Bernhard'), $array);
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_NoSuchPropertyException
     */
    public function testSetValueThrowsExceptionIfIndexNotationExpected()
    {
        $array = array();

        $this->propertyAccessor->setValue($array, 'firstName', 'Bernhard');
    }

    public function testSetValueUpdatesArraysWithCustomPropertyPath()
    {
        $array = array();

        $this->propertyAccessor->setValue($array, '[child][index][firstName]', 'Bernhard');

        $this->assertEquals(array('child' => array('index' => array('firstName' => 'Bernhard'))), $array);
    }

    public function testSetValueUpdatesProperties()
    {
        $object = new Symfony_Component_PropertyAccess_Tests_Fixtures_Author();

        $this->propertyAccessor->setValue($object, 'firstName', 'Bernhard');

        $this->assertEquals('Bernhard', $object->firstName);
    }

    public function testSetValueUpdatesPropertiesWithCustomPropertyPath()
    {
        $object = new Symfony_Component_PropertyAccess_Tests_Fixtures_Author();
        $object->child = array();
        $object->child['index'] = new Symfony_Component_PropertyAccess_Tests_Fixtures_Author();

        $this->propertyAccessor->setValue($object, 'child[index].firstName', 'Bernhard');

        $this->assertEquals('Bernhard', $object->child['index']->firstName);
    }

    public function testSetValueUpdateMagicSet()
    {
        $object = new Symfony_Component_PropertyAccess_Tests_Fixtures_Magician();

        $this->propertyAccessor->setValue($object, 'magicProperty', 'foobar');

        $this->assertEquals('foobar', $object->__get('magicProperty'));
    }

    public function testSetValueUpdatesSetters()
    {
        $object = new Symfony_Component_PropertyAccess_Tests_Fixtures_Author();

        $this->propertyAccessor->setValue($object, 'lastName', 'Schussek');

        $this->assertEquals('Schussek', $object->getLastName());
    }

    public function testSetValueCamelizesSetterNames()
    {
        $object = new Symfony_Component_PropertyAccess_Tests_Fixtures_Author();

        $this->propertyAccessor->setValue($object, 'last_name', 'Schussek');

        $this->assertEquals('Schussek', $object->getLastName());
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_PropertyAccessDeniedException
     */
    public function testSetValueThrowsExceptionIfGetterIsNotPublic()
    {
        $this->propertyAccessor->setValue(new Symfony_Component_PropertyAccess_Tests_Fixtures_Author(), 'privateSetter', 'foobar');
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_UnexpectedTypeException
     */
    public function testSetValueThrowsExceptionIfNotObjectOrArray()
    {
        $value = 'baz';

        $this->propertyAccessor->setValue($value, 'foobar', 'bam');
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_UnexpectedTypeException
     */
    public function testSetValueThrowsExceptionIfNull()
    {
        $value = null;

        $this->propertyAccessor->setValue($value, 'foobar', 'bam');
    }

    /**
     * @expectedException Symfony_Component_PropertyAccess_Exception_UnexpectedTypeException
     */
    public function testSetValueThrowsExceptionIfEmpty()
    {
        $value = '';

        $this->propertyAccessor->setValue($value, 'foobar', 'bam');
    }
}
