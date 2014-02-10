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
class Symfony_Component_Finder_Tests_FakeAdapter_FailingAdapter extends Symfony_Component_Finder_Adapter_AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    public function searchInDirectory($dir)
    {
        throw new Symfony_Component_Finder_Exception_AdapterFailureException($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'failing';
    }

    /**
     * {@inheritdoc}
     */
    protected function canBeUsed()
    {
        return true;
    }
}
