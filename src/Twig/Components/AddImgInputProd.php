<?php
namespace App\Twig\Components;

use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;
use App\Entity\Product;
use App\Form\ProduitFormType;

#[AsLiveComponent]
class AddImgInputProd extends AbstractController
{
    use DefaultActionTrait;
    use LiveCollectionTrait;

    #[LiveProp(fieldName: 'formData')]
    public ?Product $product = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(
            ProduitFormType::class,
            $this->product
        );
    }
}
