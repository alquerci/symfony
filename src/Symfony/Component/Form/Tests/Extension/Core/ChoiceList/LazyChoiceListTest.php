<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_ChoiceList_LazyChoiceListTest extends PHPUnit_Framework_TestCase
{
    private $list;

    protected function setUp()
    {
        parent::setUp();

        $this->list = new Symfony_Component_Form_Tests_Extension_Core_ChoiceList_LazyChoiceListTest_Impl(new Symfony_Component_Form_Extension_Core_ChoiceList_SimpleChoiceList(array(
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
        ), array('b')));
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->list = null;
    }

    public function testGetChoices()
    {
        $this->assertSame(array(0 => 'a', 1 => 'b', 2 => 'c'), $this->list->getChoices());
    }

    public function testGetValues()
    {
        $this->assertSame(array(0 => 'a', 1 => 'b', 2 => 'c'), $this->list->getValues());
    }

    public function testGetPreferredViews()
    {
        $this->assertEquals(array(1 => new Symfony_Component_Form_Extension_Core_View_ChoiceView('b', 'b', 'B')), $this->list->getPreferredViews());
    }

    public function testGetRemainingViews()
    {
        $this->assertEquals(array(0 => new Symfony_Component_Form_Extension_Core_View_ChoiceView('a', 'a', 'A'), 2 => new Symfony_Component_Form_Extension_Core_View_ChoiceView('c', 'c', 'C')), $this->list->getRemainingViews());
    }

    public function testGetIndicesForChoices()
    {
        $choices = array('b', 'c');
        $this->assertSame(array(1, 2), $this->list->getIndicesForChoices($choices));
    }

    public function testGetIndicesForValues()
    {
        $values = array('b', 'c');
        $this->assertSame(array(1, 2), $this->list->getIndicesForValues($values));
    }

    public function testGetChoicesForValues()
    {
        $values = array('b', 'c');
        $this->assertSame(array('b', 'c'), $this->list->getChoicesForValues($values));
    }

    public function testGetValuesForChoices()
    {
        $choices = array('b', 'c');
        $this->assertSame(array('b', 'c'), $this->list->getValuesForChoices($choices));
    }

    /**
     * @expectedException Symfony_Component_Form_Exception_Exception
     */
    public function testLoadChoiceListShouldReturnChoiceList()
    {
        $list = new Symfony_Component_Form_Tests_Extension_Core_ChoiceList_LazyChoiceListTest_InvalidImpl();

        $list->getChoices();
    }
}

class Symfony_Component_Form_Tests_Extension_Core_ChoiceList_LazyChoiceListTest_Impl extends Symfony_Component_Form_Extension_Core_ChoiceList_LazyChoiceList
{
    private $choiceList;

    public function __construct($choiceList)
    {
        $this->choiceList = $choiceList;
    }

    protected function loadChoiceList()
    {
        return $this->choiceList;
    }
}

class Symfony_Component_Form_Tests_Extension_Core_ChoiceList_LazyChoiceListTest_InvalidImpl extends Symfony_Component_Form_Extension_Core_ChoiceList_LazyChoiceList
{
    protected function loadChoiceList()
    {
        return new stdClass();
    }
}
