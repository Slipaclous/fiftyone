<?php

// src/Form/EventType.php

namespace App\Form;

use App\Entity\MemberEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType; // Corrected type declaration

class MemberEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class)
            ->add('date', DateType::class) // Corrected type declaration
            ->add('description', TextareaType::class)
            ->add('places', IntegerType::class)
            ->add('cover', FileType::class, [
                'required' => false,
                'label' => 'Event Cover Image',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MemberEvents::class,
        ]);
    }
}
