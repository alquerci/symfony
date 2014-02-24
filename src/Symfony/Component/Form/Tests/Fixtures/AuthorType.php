<?php

class Symfony_Component_Form_Tests_Fixtures_AuthorType extends Symfony_Component_Form_AbstractType
{
    public function buildForm(Symfony_Component_Form_FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
        ;
    }

    public function getName()
    {
        return 'author';
    }

    public function setDefaultOptions(Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Symfony_Component_Form_Tests_Fixtures_Author',
        ));
    }
}
