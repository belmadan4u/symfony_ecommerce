<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Entity\Image;
use App\Form\ImageFormType;

class ProduitFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('price')
            ->add('description')
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'attr' => ['class' => 'input-field', 'min' => 0],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le stock ne peut pas être vide.',
                    ]),
                    new PositiveOrZero([
                        'message' => 'Le stock doit être un entier positif ou zéro.',
                    ]),
                ],
            ])
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
            ->add('images', FileType::class, [
                'label' => 'Ajouter des images',
                'mapped' => false, // Les images ne sont pas liées directement à l'entité Product
                'required' => false,
                'multiple' => true,
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize' => '2M',
                                'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                                'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG ou PNG)',
                            ])
                        ],
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
