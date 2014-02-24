<?php

class Symfony_Component_Form_Tests_Fixtures_AlternatingRowType extends Symfony_Component_Form_AbstractType
{
    public function buildForm(Symfony_Component_Form_FormBuilderInterface $builder, array $options)
    {
        $formFactory = $builder->getFormFactory();

        $builder->addEventListener(Symfony_Component_Form_FormEvents::PRE_SET_DATA, function (Symfony_Component_Form_FormEvent $event) use ($formFactory) {
            $form = $event->getForm();
            $type = $form->getName() % 2 === 0 ? 'text' : 'textarea';
            $form->add($formFactory->createNamed('title', $type));
        });
    }

    public function getName()
    {
        return 'alternating_row';
    }
}
