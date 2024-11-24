<?php

namespace App\Twig\Components;

use Psr\Log\LoggerInterface;
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
        private ProductRepository $productRepository,
         private LoggerInterface $logger
    ) {}

    public function getPackages(): array
    {
        $this->logger->info('Searching for products with query: ' . json_encode($this->productRepository->findBySearchTerm($this->query)));
        switch ($this->query) {
            case '':
                return [];
            default:
                return $this->productRepository->findBySearchTerm($this->query);
        }
        
    }
}
