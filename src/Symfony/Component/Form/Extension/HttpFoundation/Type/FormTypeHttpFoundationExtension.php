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
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Extension_HttpFoundation_Type_FormTypeHttpFoundationExtension extends Symfony_Component_Form_AbstractTypeExtension
{
    /**
     * @var Symfony_Component_Form_Extension_HttpFoundation_EventListener_BindRequestListener
     */
    private $listener;

    public function __construct()
    {
        $this->listener = new Symfony_Component_Form_Extension_HttpFoundation_EventListener_BindRequestListener();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(Symfony_Component_Form_FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->listener);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
