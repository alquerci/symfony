<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Fixtures_FooSubTypeWithParentInstance extends Symfony_Component_Form_AbstractType
{
    public function getName()
    {
        return 'foo_sub_type_parent_instance';
    }

    public function getParent()
    {
        return new Symfony_Component_Form_Tests_Fixtures_FooType();
    }
}
