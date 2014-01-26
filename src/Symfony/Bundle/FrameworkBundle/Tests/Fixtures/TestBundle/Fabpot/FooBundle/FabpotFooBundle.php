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
 * Bundle.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TestBundle_Fabpot_FooBundle_FabpotFooBundle extends Symfony_Component_HttpKernel_Bundle_Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'SensioFooBundle';
    }
}
