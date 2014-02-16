<?php

class Symfony_Component_Form_Tests_Fixtures_AlternatingRowType extends Symfony_Component_Form_AbstractType
{
    public function buildForm(Symfony_Component_Form_FormBuilderInterface $builder, array $options)
    {
        $formFactory = $builder->getFormFactory();

        $builder->addEventListener(Symfony_Component_Form_FormEvents::PRE_SET_DATA, array(
            new Symfony_Component_Form_Tests_Fixtures_AlternatingRowTypeClosures($formFactory),
            'onPreSetdate'
        ));
    }

    public function getName()
    {
        return 'alternating_row';
    }
}

class Symfony_Component_Form_Tests_Fixtures_AlternatingRowTypeClosures
{
    private $formFactory;

    public function __construct($formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function onPreSetdate(Symfony_Component_Form_FormEvent $event)
    {
        $form = $event->getForm();
        $type = $form->getName() % 2 === 0 ? 'text' : 'textarea';
        $form->add($this->formFactory->createNamed('title', $type));
    }
}
