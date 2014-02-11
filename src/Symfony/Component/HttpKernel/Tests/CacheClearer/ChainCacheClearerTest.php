<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_CacheClearer_ChainCacheClearerTest extends PHPUnit_Framework_TestCase
{
    protected static $cacheDir;

    public static function setUpBeforeClass()
    {
        self::$cacheDir = tempnam(sys_get_temp_dir(), 'sf2_cache_clearer_dir');
    }

    public static function tearDownAfterClass()
    {
        @unlink(self::$cacheDir);
    }

    public function testInjectClearersInConstructor()
    {
        $clearer = $this->getMockClearer();
        $clearer
            ->expects($this->once())
            ->method('clear');

        $chainClearer = new Symfony_Component_HttpKernel_CacheClearer_ChainCacheClearer(array($clearer));
        $chainClearer->clear(self::$cacheDir);
    }

    public function testInjectClearerUsingAdd()
    {
        $clearer = $this->getMockClearer();
        $clearer
            ->expects($this->once())
            ->method('clear');

        $chainClearer = new Symfony_Component_HttpKernel_CacheClearer_ChainCacheClearer();
        $chainClearer->add($clearer);
        $chainClearer->clear(self::$cacheDir);
    }

    protected function getMockClearer()
    {
        return $this->getMock('Symfony_Component_HttpKernel_CacheClearer_CacheClearerInterface');
    }
}
