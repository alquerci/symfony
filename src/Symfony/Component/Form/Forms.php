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
 * Entry point of the Form component.
 *
 * Use this class to conveniently create new form factories:
 *
 * <code>
 * use Symfony_Component_Form_Forms;
 *
 * $formFactory = Symfony_Component_Form_Forms::createFormFactory();
 *
 * $form = $formFactory->createBuilder()
 *     ->add('firstName', 'text')
 *     ->add('lastName', 'text')
 *     ->add('age', 'integer')
 *     ->add('gender', 'choice', array(
 *         'choices' => array('m' => 'Male', 'f' => 'Female'),
 *     ))
 *     ->getForm();
 * </code>
 *
 * You can also add custom extensions to the form factory:
 *
 * <code>
 * $formFactory = Symfony_Component_Form_Forms::createFormFactoryBuilder()
 *     ->addExtension(new AcmeExtension())
 *     ->getFormFactory();
 * </code>
 *
 * If you create custom form types or type extensions, it is
 * generally recommended to create your own extensions that lazily
 * load these types and type extensions. In projects where performance
 * does not matter that much, you can also pass them directly to the
 * form factory:
 *
 * <code>
 * $formFactory = Symfony_Component_Form_Forms::createFormFactoryBuilder()
 *     ->addType(new PersonType())
 *     ->addType(new PhoneNumberType())
 *     ->addTypeExtension(new FormTypeHelpTextExtension())
 *     ->getFormFactory();
 * </code>
 *
 * Support for CSRF protection is provided by the CsrfExtension.
 * This extension needs a CSRF provider with a strong secret
 * (e.g. a 20 character long random string). The default
 * implementation for this is DefaultCsrfProvider:
 *
 * <code>
 * use Symfony_Component_Form_Extension_Csrf_CsrfExtension;
 * use Symfony_Component_Form_Extension_Csrf_CsrfProvider_DefaultCsrfProvider;
 *
 * $secret = 'V8a5Z97e...';
 * $formFactory = Symfony_Component_Form_Forms::createFormFactoryBuilder()
 *     ->addExtension(new CsrfExtension(new DefaultCsrfProvider($secret)))
 *     ->getFormFactory();
 * </code>
 *
 * Support for the HttpFoundation is provided by the
 * HttpFoundationExtension. You are also advised to load the CSRF
 * extension with the driver for HttpFoundation's Session class:
 *
 * <code>
 * use Symfony_Component_HttpFoundation_Session_Session;
 * use Symfony_Component_Form_Extension_HttpFoundation_HttpFoundationExtension;
 * use Symfony_Component_Form_Extension_Csrf_CsrfExtension;
 * use Symfony_Component_Form_Extension_Csrf_CsrfProvider_SessionCsrfProvider;
 *
 * $session = new Session();
 * $secret = 'V8a5Z97e...';
 * $formFactory = Symfony_Component_Form_Forms::createFormFactoryBuilder()
 *     ->addExtension(new HttpFoundationExtension())
 *     ->addExtension(new CsrfExtension(new SessionCsrfProvider($session, $secret)))
 *     ->getFormFactory();
 * </code>
 *
 * Support for the Validator component is provided by ValidatorExtension.
 * This extension needs a validator object to function properly:
 *
 * <code>
 * use Symfony_Component_Validator_Validation;
 * use Symfony_Component_Form_Extension_Validator_ValidatorExtension;
 *
 * $validator = Validation::createValidator();
 * $formFactory = Symfony_Component_Form_Forms::createFormFactoryBuilder()
 *     ->addExtension(new ValidatorExtension($validator))
 *     ->getFormFactory();
 * </code>
 *
 * Support for the Templating component is provided by TemplatingExtension.
 * This extension needs a PhpEngine object for rendering forms. As second
 * argument you should pass the names of the default themes. Here is an
 * example for using the default layout with "<div>" tags:
 *
 * <code>
 * use Symfony_Component_Form_Extension_Templating_TemplatingExtension;
 *
 * $formFactory = Symfony_Component_Form_Forms::createFormFactoryBuilder()
 *     ->addExtension(new TemplatingExtension($engine, null, array(
 *         'FrameworkBundle:Form',
 *     )))
 *     ->getFormFactory();
 * </code>
 *
 * The next example shows how to include the "<table>" layout:
 *
 * <code>
 * use Symfony_Component_Form_Extension_Templating_TemplatingExtension;
 *
 * $formFactory = Symfony_Component_Form_Forms::createFormFactoryBuilder()
 *     ->addExtension(new TemplatingExtension($engine, null, array(
 *         'FrameworkBundle:Form',
 *         'FrameworkBundle:FormTable',
 *     )))
 *     ->getFormFactory();
 * </code>
 *
 * If you also loaded the CsrfExtension, you should pass the CSRF provider
 * to the extension so that you can render CSRF tokens in your templates
 * more easily:
 *
 * <code>
 * use Symfony_Component_Form_Extension_Csrf_CsrfExtension;
 * use Symfony_Component_Form_Extension_Csrf_CsrfProvider_DefaultCsrfProvider;
 * use Symfony_Component_Form_Extension_Templating_TemplatingExtension;
 *
 *
 * $secret = 'V8a5Z97e...';
 * $csrfProvider = new DefaultCsrfProvider($secret);
 * $formFactory = Symfony_Component_Form_Forms::createFormFactoryBuilder()
 *     ->addExtension(new CsrfExtension($csrfProvider))
 *     ->addExtension(new TemplatingExtension($engine, $csrfProvider, array(
 *         'FrameworkBundle:Form',
 *     )))
 *     ->getFormFactory();
 * </code>
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
final class Symfony_Component_Form_Forms
{
    /**
     * Creates a form factory with the default configuration.
     *
     * @return Symfony_Component_Form_FormFactoryInterface The form factory.
     */
    public static function createFormFactory()
    {
        return self::createFormFactoryBuilder()->getFormFactory();
    }

    /**
     * Creates a form factory builder with the default configuration.
     *
     * @return Symfony_Component_Form_FormFactoryBuilderInterface The form factory builder.
     */
    public static function createFormFactoryBuilder()
    {
        $builder = new Symfony_Component_Form_FormFactoryBuilder();
        $builder->addExtension(new Symfony_Component_Form_Extension_Core_CoreExtension());

        return $builder;
    }

    /**
     * This class cannot be instantiated.
     */
    private function __construct()
    {
    }
}
