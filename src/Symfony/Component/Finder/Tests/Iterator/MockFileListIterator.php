<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Finder_Tests_Iterator_MockFileListIterator extends ArrayIterator
{
    public function __construct(array $filesArray = array())
    {
        $files = array_map(create_function('$file', 'return new Symfony_Component_Finder_Tests_Iterator_MockSplFileInfo($file);'), $filesArray);
        parent::__construct($files);
    }
}
