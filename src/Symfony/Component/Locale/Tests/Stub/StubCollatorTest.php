<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Locale_Tests_Stub_StubCollatorTest extends Symfony_Component_Locale_Tests_TestCase
{
    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodArgumentValueNotImplementedException
     */
    public function testConstructorWithUnsupportedLocale()
    {
        $collator = new Symfony_Component_Locale_Stub_StubCollator('pt_BR');
    }

    /**
    * @dataProvider asortProvider
    */
    public function testAsortStub($array, $sortFlag, $expected)
    {
        $collator = new Symfony_Component_Locale_Stub_StubCollator('en');
        $collator->asort($array, $sortFlag);
        $this->assertSame($expected, $array);
    }

    /**
    * @dataProvider asortProvider
    */
    public function testAsortIntl($array, $sortFlag, $expected)
    {
        $this->skipIfIntlExtensionIsNotLoaded();
        $collator = new Collator('en');
        $collator->asort($array, $sortFlag);
        $this->assertSame($expected, $array);
    }

    public function asortProvider()
    {
        return array(
            /* array, sortFlag, expected */
            array(
                array('a', 'b', 'c'),
                Symfony_Component_Locale_Stub_StubCollator::SORT_REGULAR,
                array('a', 'b', 'c'),
            ),
            array(
                array('c', 'b', 'a'),
                Symfony_Component_Locale_Stub_StubCollator::SORT_REGULAR,
                array(2 => 'a', 1 => 'b',  0 => 'c'),
            ),
            array(
                array('b', 'c', 'a'),
                Symfony_Component_Locale_Stub_StubCollator::SORT_REGULAR,
                array(2 => 'a', 0 => 'b', 1 => 'c'),
            ),
        );
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testCompare()
    {
        $collator = $this->createStubCollator();
        $collator->compare('a', 'b');
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testGetAttribute()
    {
        $collator = $this->createStubCollator();
        $collator->getAttribute(Symfony_Component_Locale_Stub_StubCollator::NUMERIC_COLLATION);
    }

    public function testGetErrorCode()
    {
        $collator = $this->createStubCollator();
        $this->assertEquals(Symfony_Component_Locale_Stub_StubIntl::U_ZERO_ERROR, $collator->getErrorCode());
    }

    public function testGetErrorMessage()
    {
        $collator = $this->createStubCollator();
        $this->assertEquals('U_ZERO_ERROR', $collator->getErrorMessage());
    }

    public function testGetLocale()
    {
        $collator = $this->createStubCollator();
        $this->assertEquals('en', $collator->getLocale());
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testGetSortKey()
    {
        $collator = $this->createStubCollator();
        $collator->getSortKey('Hello');
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testGetStrength()
    {
        $collator = $this->createStubCollator();
        $collator->getStrength();
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testSetAttribute()
    {
        $collator = $this->createStubCollator();
        $collator->setAttribute(Symfony_Component_Locale_Stub_StubCollator::NUMERIC_COLLATION, Symfony_Component_Locale_Stub_StubCollator::ON);
    }

    /**
     * @expectedException Symfony_Component_Locale_Exception_MethodNotImplementedException
     */
    public function testSetStrength()
    {
        $collator = $this->createStubCollator();
        $collator->setStrength(Symfony_Component_Locale_Stub_StubCollator::PRIMARY);
    }

    public function testStaticCreate()
    {
        $collator = Symfony_Component_Locale_Stub_StubCollator::create('en');
        $this->assertInstanceOf('Symfony_Component_Locale_Stub_StubCollator', $collator);
    }

    protected function createStubCollator()
    {
        return new Symfony_Component_Locale_Stub_StubCollator('en');
    }
}
