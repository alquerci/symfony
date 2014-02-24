<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Validator_Tests_Mapping_Cache_ApcCacheTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!extension_loaded('apc') || !ini_get('apc.enable_cli')) {
            $this->markTestSkipped('APC is not loaded.');
        }
    }

    public function testWrite()
    {
        $meta = $this->getMockBuilder('Symfony_Component_Validator_Mapping_ClassMetadata')
            ->disableOriginalConstructor()
            ->setMethods(array('getClassName'))
            ->getMock();

        $meta->expects($this->once())
            ->method('getClassName')
            ->will($this->returnValue('bar'));

        $cache = new Symfony_Component_Validator_Mapping_Cache_ApcCache('foo');
        $cache->write($meta);

        $this->assertInstanceOf('Symfony_Component_Validator_Mapping_ClassMetadata', apc_fetch('foobar'), '->write() stores metadata in APC');
    }

    public function testHas()
    {
        $meta = $this->getMockBuilder('Symfony_Component_Validator_Mapping_ClassMetadata')
            ->disableOriginalConstructor()
            ->setMethods(array('getClassName'))
            ->getMock();

        $meta->expects($this->once())
            ->method('getClassName')
            ->will($this->returnValue('bar'));

        apc_delete('foobar');

        $cache = new Symfony_Component_Validator_Mapping_Cache_ApcCache('foo');
        $this->assertFalse($cache->has('bar'), '->has() returns false when there is no entry');

        $cache->write($meta);
        $this->assertTrue($cache->has('bar'), '->has() returns true when the is an entry');
    }

    public function testRead()
    {
        $meta = $this->getMockBuilder('Symfony_Component_Validator_Mapping_ClassMetadata')
            ->disableOriginalConstructor()
            ->setMethods(array('getClassName'))
            ->getMock();

        $meta->expects($this->once())
            ->method('getClassName')
            ->will($this->returnValue('bar'));

        $cache = new Symfony_Component_Validator_Mapping_Cache_ApcCache('foo');
        $cache->write($meta);

        $this->assertInstanceOf('Symfony_Component_Validator_Mapping_ClassMetadata', $cache->read('bar'), '->read() returns metadata');
    }
}
