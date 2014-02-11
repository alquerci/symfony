<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_CacheWarmer_CacheWarmerAggregateTest extends PHPUnit_Framework_TestCase
{
    protected static $cacheDir;

    public static function setUpBeforeClass()
    {
        self::$cacheDir = tempnam(sys_get_temp_dir(), 'sf2_cache_warmer_dir');
    }

    public static function tearDownAfterClass()
    {
        @unlink(self::$cacheDir);
    }

    public function testInjectWarmersUsingConstructor()
    {
        $warmer = $this->getCacheWarmerMock();
        $warmer
            ->expects($this->once())
            ->method('warmUp');
        $aggregate = new Symfony_Component_HttpKernel_CacheWarmer_CacheWarmerAggregate(array($warmer));
        $aggregate->warmUp(self::$cacheDir);
    }

    public function testInjectWarmersUsingAdd()
    {
        $warmer = $this->getCacheWarmerMock();
        $warmer
            ->expects($this->once())
            ->method('warmUp');
        $aggregate = new Symfony_Component_HttpKernel_CacheWarmer_CacheWarmerAggregate();
        $aggregate->add($warmer);
        $aggregate->warmUp(self::$cacheDir);
    }

    public function testInjectWarmersUsingSetWarmers()
    {
        $warmer = $this->getCacheWarmerMock();
        $warmer
            ->expects($this->once())
            ->method('warmUp');
        $aggregate = new Symfony_Component_HttpKernel_CacheWarmer_CacheWarmerAggregate();
        $aggregate->setWarmers(array($warmer));
        $aggregate->warmUp(self::$cacheDir);
    }

    public function testWarmupDoesCallWarmupOnOptionalWarmersWhenEnableOptionalWarmersIsEnabled()
    {
        $warmer = $this->getCacheWarmerMock();
        $warmer
            ->expects($this->never())
            ->method('isOptional');
        $warmer
            ->expects($this->once())
            ->method('warmUp');

        $aggregate = new Symfony_Component_HttpKernel_CacheWarmer_CacheWarmerAggregate(array($warmer));
        $aggregate->enableOptionalWarmers();
        $aggregate->warmUp(self::$cacheDir);
    }

    public function testWarmupDoesNotCallWarmupOnOptionalWarmersWhenEnableOptionalWarmersIsNotEnabled()
    {
        $warmer = $this->getCacheWarmerMock();
        $warmer
            ->expects($this->once())
            ->method('isOptional')
            ->will($this->returnValue(true));
        $warmer
            ->expects($this->never())
            ->method('warmUp');

        $aggregate = new Symfony_Component_HttpKernel_CacheWarmer_CacheWarmerAggregate(array($warmer));
        $aggregate->warmUp(self::$cacheDir);
    }

    protected function getCacheWarmerMock()
    {
        $warmer = $this->getMockBuilder('Symfony_Component_HttpKernel_CacheWarmer_CacheWarmerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        return $warmer;
    }
}
