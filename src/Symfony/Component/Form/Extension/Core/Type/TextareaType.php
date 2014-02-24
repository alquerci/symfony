<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Extension_Core_Type_TextareaType extends Symfony_Component_Form_AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options)
    {
        $view->vars['pattern'] = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'textarea';
    }
}
