<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Enum\ProductStatus;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Entity\Image;
use App\Form\ImageFormType; // N'oubliez pas d'importer le nouveau type

class ProduitFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('price')
            ->add('description')
            ->add('stock')
            ->add('status', ChoiceType::class, [
                'choices' => array_combine(
                    array_map(fn(ProductStatus $status) => $status->name, ProductStatus::cases()),
                    ProductStatus::cases()
                ),
                'choice_label' => fn($choice) => $choice->name, // Affiche le nom de chaque case
                'choice_value' => fn($choice) => $choice?->value, // Utilise la valeur de chaque case
                'label' => 'Status'
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('images', CollectionType::class, [
                'entry_type' => ImageFormType::class, // Utilisez ImageType ici
                'allow_add' => true,
                'allow_delete' => true,
                'data_class' => null,  // Optionnel si vous n'utilisez pas d'objets ici
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Modifier'
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
