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
class Symfony_Component_Form_Extension_Csrf_Type_FormTypeCsrfExtension extends Symfony_Component_Form_AbstractTypeExtension
{
    private $defaultCsrfProvider;
    private $defaultEnabled;
    private $defaultFieldName;

    public function __construct(Symfony_Component_Form_Extension_Csrf_CsrfProvider_CsrfProviderInterface $defaultCsrfProvider, $defaultEnabled = true, $defaultFieldName = '_token')
    {
        $this->defaultCsrfProvider = $defaultCsrfProvider;
        $this->defaultEnabled = $defaultEnabled;
        $this->defaultFieldName = $defaultFieldName;
    }

    /**
     * Adds a CSRF field to the form when the CSRF protection is enabled.
     *
     * @param Symfony_Component_Form_FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function buildForm(Symfony_Component_Form_FormBuilderInterface $builder, array $options)
    {
        if (!$options['csrf_protection']) {
            return;
        }

        $builder
            ->setAttribute('csrf_factory', $builder->getFormFactory())
            ->addEventSubscriber(new Symfony_Component_Form_Extension_Csrf_EventListener_CsrfValidationListener($options['csrf_field_name'], $options['csrf_provider'], $options['intention']))
        ;
    }

    /**
     * Adds a CSRF field to the root form view.
     *
     * @param Symfony_Component_Form_FormView      $view    The form view
     * @param Symfony_Component_Form_FormInterface $form    The form
     * @param array         $options The options
     */
    public function finishView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options)
    {
        if ($options['csrf_protection'] && !$view->parent && $options['compound']) {
            $factory = $form->getConfig()->getAttribute('csrf_factory');
            $data = $options['csrf_provider']->generateCsrfToken($options['intention']);

            $csrfForm = $factory->createNamed($options['csrf_field_name'], 'hidden', $data, array(
                'mapped' => false,
            ));

            $view->children[$options['csrf_field_name']] = $csrfForm->createView($view);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => $this->defaultEnabled,
            'csrf_field_name'   => $this->defaultFieldName,
            'csrf_provider'     => $this->defaultCsrfProvider,
            'intention'         => 'unknown',
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}
