<?php 
// src/Form/MeetingType.php

namespace App\Form;

use App\Entity\Metting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, [
                'label' => 'Date de la réunion',
                'html5' => true, // Ensure this is set to true to use the browser's native datepicker
                'widget' => 'single_text', // Use 'single_text' for a text input date picker
                'attr' => [
                    'class' => 'datepicker',
                    'min' => (new \DateTime())->format('Y-m-d'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Metting::class,
        ]);
    }
}
