<?php
namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use App\Repository\ProductRepository;

#[AsLiveComponent]
class SearchProducts
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(
        private ProductRepository $productRepository
    ) {}

    public function getPackages(): array
    {
        return match ($this->query) {
            '' => [],
            default => $this->productRepository->findBySearchTerm($this->query),
        };
        
    }
}
