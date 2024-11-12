<?php
namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;
use App\Enum\ProductStatus;

class ProductFixtures extends Fixture
{
    public const PRODUCT_1_REFERENCE = 'product_1';
    public const PRODUCT_2_REFERENCE = 'product_2';
    public const PRODUCT_3_REFERENCE = 'product_3';
    public const PRODUCT_4_REFERENCE = 'product_4';
    public const PRODUCT_5_REFERENCE = 'product_5';

    public function load(ObjectManager $manager): void
    {
        $products = [
            [
                'reference' => self::PRODUCT_1_REFERENCE,
                'name' => 'Ballon de football',
                'price' => 25.99,
                'description' => 'Ballon de football de haute qualité',
                'stock' => 100,
                'status' => ProductStatus::disponible,
            ],
            [
                'reference' => self::PRODUCT_2_REFERENCE,
                'name' => 'Raquette de tennis',
                'price' => 89.99,
                'description' => 'Raquette de tennis légère',
                'stock' => 50,
                'status' => ProductStatus::disponible,
            ],
            [
                'reference' => self::PRODUCT_3_REFERENCE,
                'name' => 'Vélo de montagne',
                'price' => 299.99,
                'description' => 'Vélo de montagne robuste',
                'stock' => 20,
                'status' => ProductStatus::disponible,
            ],
            [
                'reference' => self::PRODUCT_4_REFERENCE,
                'name' => 'Tapis de yoga',
                'price' => 34.99,
                'description' => 'Tapis de yoga antidérapant',
                'stock' => 75,
                'status' => ProductStatus::rupture,
            ],
            [
                'reference' => self::PRODUCT_5_REFERENCE,
                'name' => 'Gants de boxe',
                'price' => 49.99,
                'description' => 'Gants de boxe confortables',
                'stock' => 30,
                'status' => ProductStatus::disponible,
            ],
        ];

        foreach ($products as $productData) {
            $product = new Product();
            $product->setName($productData['name'])
                    ->setPrice($productData['price'])
                    ->setDescription($productData['description'])
                    ->setStock($productData['stock'])
                    ->setStatus($productData['status']);

            $manager->persist($product);

            // Ajoute une référence nommée
            $this->addReference($productData['reference'], $product);
        }

        $manager->flush();
    }
}
