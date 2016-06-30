<?php

namespace CM\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
        $builder->add('nom');
        $builder->add('prenom');
        $builder->add('adresse');
        $builder->add('telephone');
        $builder->add('webSite');
    }

    public function getName()
    {
        return 'cm_user_registration';
    }
}
