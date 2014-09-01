<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Stopwatch\Tests\Fixtures;

use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * @author Alexandre Quercia <alquerci@email.com>
 */
class TimelessStopwatchEvent extends StopwatchEvent
{
    private $microtime;

    protected function getMicrotime()
    {
        return $this->microtime;
    }

    public function setMicrotime($microtime)
    {
        $this->microtime = $microtime;
    }
}
