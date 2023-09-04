<?php
namespace App\Form;

use App\Form\GuestType;
use App\Entity\EventParticipant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class EventParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('guests', CollectionType::class, [
                'entry_type' => GuestType::class, // Use the form type for the Guest entity
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false, // Set this to false to properly handle the association
                'label' => 'Guests',
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Note',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EventParticipant::class,
        ]);
    }
}

