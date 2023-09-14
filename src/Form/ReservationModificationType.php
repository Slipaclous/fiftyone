<?php 
namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ReservationModificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reservationNumber', TextType::class, [
                'label' => 'Reservation Number',
                'required' => true, // You can change this based on your validation logic
            ])
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('numberOfPeople', IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}

