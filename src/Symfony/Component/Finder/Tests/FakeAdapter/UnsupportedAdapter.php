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
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class Symfony_Component_Finder_Tests_FakeAdapter_UnsupportedAdapter extends Symfony_Component_Finder_Adapter_AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    public function searchInDirectory($dir)
    {
        return new ArrayIterator(array());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'unsupported';
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeUsed()
    {
        return false;
    }
}
