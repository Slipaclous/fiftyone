<?php

namespace App\Form\Type;

use App\Entity\Images;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageUploadType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    $imagePath = 'images/'; // Update this if your path is different

        $builder->add('imageFile', VichImageType::class, [
            "label" => "Images",
            "required" => false,
            "allow_delete" => true,
            "attr" => [
                "url" => $imagePath
            ]
        ]);
            

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Images::class
        ]);
    }
}
