<?php 
// src/Form/MeetingSummaryType.php

namespace App\Form;

use App\Entity\MeetingSummary;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class MeetingSummaryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'label'=>'Date de la réunion',
                'attr' => [
                    'class' => 'datepicker',
                    'min' => (new \DateTime())->format('Y-m-d'),
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
            // Configure your data class here
            'data_class' => MeetingSummary::class,
        ]);
    }
}
