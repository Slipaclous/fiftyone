<?php
// src/Form/ContactType.php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Get the existing message value from the options array
        $message = $options['message'] ?? null;

        $builder
            ->add('email')
            ->add('message', TextareaType::class, [
                'data' => $message, // Set the existing message as the default value
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'message' => null, // Add a custom option to pass the existing message value
        ]);
    }
}
