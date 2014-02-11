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
 * Test class for Symfony_Component_HttpFoundation_Session_Storage_MetadataBag.
 */
class Symfony_Component_HttpFoundation_Tests_Session_Storage_MetadataBagTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony_Component_HttpFoundation_Session_Storage_MetadataBag
     */
    protected $bag;

    /**
     * @var array
     */
    protected $array = array();

    protected function setUp()
    {
        $this->bag = new Symfony_Component_HttpFoundation_Session_Storage_MetadataBag();
        $this->array = array(Symfony_Component_HttpFoundation_Session_Storage_MetadataBag::CREATED => 1234567, Symfony_Component_HttpFoundation_Session_Storage_MetadataBag::UPDATED => 12345678, Symfony_Component_HttpFoundation_Session_Storage_MetadataBag::LIFETIME => 0);
        $this->bag->initialize($this->array);
    }

    protected function tearDown()
    {
        $this->array = array();
        $this->bag = null;
    }

    public function testInitialize()
    {
        $bag1 = new Symfony_Component_HttpFoundation_Session_Storage_MetadataBag();
        $array = array();
        $bag1->initialize($array);
        $this->assertGreaterThanOrEqual(time(), $bag1->getCreated());
        $this->assertEquals($bag1->getCreated(), $bag1->getLastUsed());

        sleep(1);
        $bag2 = new Symfony_Component_HttpFoundation_Session_Storage_MetadataBag();
        $array2 = $this->readAttribute($bag1, 'meta');
        $bag2->initialize($array2);
        $this->assertEquals($bag1->getCreated(), $bag2->getCreated());
        $this->assertEquals($bag1->getLastUsed(), $bag2->getLastUsed());
        $this->assertEquals($bag2->getCreated(), $bag2->getLastUsed());

        sleep(1);
        $bag3 = new Symfony_Component_HttpFoundation_Session_Storage_MetadataBag();
        $array3 = $this->readAttribute($bag2, 'meta');
        $bag3->initialize($array3);
        $this->assertEquals($bag1->getCreated(), $bag3->getCreated());
        $this->assertGreaterThan($bag2->getLastUsed(), $bag3->getLastUsed());
        $this->assertNotEquals($bag3->getCreated(), $bag3->getLastUsed());
    }

    public function testGetSetName()
    {
        $this->assertEquals('__metadata', $this->bag->getName());
        $this->bag->setName('foo');
        $this->assertEquals('foo', $this->bag->getName());

    }

    public function testGetStorageKey()
    {
        $this->assertEquals('_sf2_meta', $this->bag->getStorageKey());
    }

    public function testGetLifetime()
    {
        $bag = new Symfony_Component_HttpFoundation_Session_Storage_MetadataBag();
        $array = array(Symfony_Component_HttpFoundation_Session_Storage_MetadataBag::CREATED => 1234567, Symfony_Component_HttpFoundation_Session_Storage_MetadataBag::UPDATED => 12345678, Symfony_Component_HttpFoundation_Session_Storage_MetadataBag::LIFETIME => 1000);
        $bag->initialize($array);
        $this->assertEquals(1000, $bag->getLifetime());
    }

    public function testGetCreated()
    {
        $this->assertEquals(1234567, $this->bag->getCreated());
    }

    public function testGetLastUsed()
    {
        $this->assertLessThanOrEqual(time(), $this->bag->getLastUsed());
    }

    public function testClear()
    {
        $this->bag->clear();
    }
}
