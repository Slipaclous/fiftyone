<?php 

// src/Form/MessageType.php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextareaType::class, [
                'attr' => ['rows' => 5], // Adjust as needed for the display
            ])
            ->add('receiver', null, [
                'label' => 'Destinataire',
                'choice_label' => 'email', // Adjust based on how you want to display the receiver
            ]);
            // ->add('created_at', DateTimeType::class, [
            //     'data' => new \DateTime('now', new \DateTimeZone('Europe/Paris')), // Set the default value to the current datetime in your time zone

            //     'widget' => 'single_text',
            //     'attr' => ['class' => 'js-datetimepicker'], // Optional: you can add a CSS class for a datetime picker
                
            // ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
