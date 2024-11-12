<?php
namespace App\Form;

use App\Entity\CreditCard;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\OptionsResolver\OptionsResolver;
class CreditCardFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Card number cannot be blank']),
                    new Regex([
                        'pattern' => '/^\d{16}$/'
                    ])
                ],
                'label' => 'Card Number',
            ])
            ->add('expirationDate', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Expiration date cannot be blank']),
                    new Regex([
                        'pattern' => '/^(0[1-9]|1[0-2])\/\d{2}$/'
                    ])
                ],
                'label' => 'Expiration Date (MM/YY)',
            ])
            ->add('cvv', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'CVV cannot be blank']),
                    new Regex([
                        'pattern' => '/^\d{3}$/'
                    ])
                ],
                'label' => 'CVV',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreditCard::class,
        ]);
    }
}
