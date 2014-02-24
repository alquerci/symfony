<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Fixtures_FooSubType extends Symfony_Component_Form_AbstractType
{
    public function getName()
    {
        return 'foo_sub_type';
    }

    public function getParent()
    {
        return 'foo';
    }
}
