<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Product;
use App\Entity\Category;

class ProductCategoryFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $productCategoryData = [
            ['productReference' => ProductFixtures::PRODUCT_1_REFERENCE, 'categoryReference' => CategoryFixtures::CATEGORY_FOOTBALL_REFERENCE], // Ballon de football -> Football
            ['productReference' => ProductFixtures::PRODUCT_2_REFERENCE, 'categoryReference' => CategoryFixtures::CATEGORY_TENNIS_REFERENCE],   // Raquette de tennis -> Tennis
            ['productReference' => ProductFixtures::PRODUCT_3_REFERENCE, 'categoryReference' => CategoryFixtures::CATEGORY_CYCLISME_REFERENCE], // Vélo de montagne -> Cyclisme
            ['productReference' => ProductFixtures::PRODUCT_4_REFERENCE, 'categoryReference' => CategoryFixtures::CATEGORY_YOGA_REFERENCE],      // Tapis de yoga -> Yoga
            ['productReference' => ProductFixtures::PRODUCT_5_REFERENCE, 'categoryReference' => CategoryFixtures::CATEGORY_BOXE_REFERENCE],      // Gants de boxe -> Boxe
            ['productReference' => ProductFixtures::PRODUCT_6_REFERENCE, 'categoryReference' => CategoryFixtures::CATEGORY_BOXE_REFERENCE],      // Sac de frappe -> Boxe
        ];

        foreach ($productCategoryData as $data) {
            $product = $this->getReference($data['productReference']);
            $category = $this->getReference($data['categoryReference']);
            $product->addCategory($category);

            $manager->persist($product);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductFixtures::class,
            CategoryFixtures::class,
        ];
    }
}
