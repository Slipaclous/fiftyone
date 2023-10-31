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
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'datepicker',
                    'min' => (new \DateTime())->format('Y-m-d'),
                ], // You can add a datepicker class if needed
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Metting::class,
        ]);
    }
}
