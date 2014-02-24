<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Core_EventListener_MergeCollectionListenerArrayObjectTest extends Symfony_Component_Form_Tests_Extension_Core_EventListener_MergeCollectionListenerTest
{
    protected function getData(array $data)
    {
        return new ArrayObject($data);
    }

    protected function getBuilder($name = 'name')
    {
        return new Symfony_Component_Form_FormBuilder($name, 'ArrayObject', $this->dispatcher, $this->factory);
    }
}
