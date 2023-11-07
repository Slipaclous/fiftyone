<?php
// src/Form/MeetingSummaryType.php

namespace App\Form;

use App\Entity\MeetingSummary;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType; // Updated this line

class MeetingSummaryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, [ // Updated this line
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd', // Updated this line
                'label'=>'Date de la réunion',
                'attr' => [
                    'class' => 'datepicker',
                ],
            ])
            ->add('pdf', FileType::class, [
                'mapped' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MeetingSummary::class,
        ]);
    }
}
