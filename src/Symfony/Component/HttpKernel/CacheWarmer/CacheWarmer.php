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
 * Abstract cache warmer that knows how to write a file to the cache.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Symfony_Component_HttpKernel_CacheWarmer_CacheWarmer implements Symfony_Component_HttpKernel_CacheWarmer_CacheWarmerInterface
{
    protected function writeCacheFile($file, $content)
    {
        $tmpFile = tempnam(dirname($file), basename($file));
        if (false !== @file_put_contents($tmpFile, $content) && @copy($tmpFile, $file)) {
            @unlink($tmpFile);
            $umask = false === umask() ? 0022 : umask();
            @chmod($file, 0666 & ~$umask);

            return;
        }

        throw new RuntimeException(sprintf('Failed to write cache file "%s".', $file));
    }
}
