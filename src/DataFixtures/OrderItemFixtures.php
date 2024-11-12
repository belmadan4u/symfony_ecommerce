<?php

namespace App\DataFixtures;

use App\Entity\OrderItem;
use App\Entity\Order;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OrderItemFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $orderItemsData = [
            [
                'productReference' => ProductFixtures::PRODUCT_1_REFERENCE,
                'orderReference' => OrderFixtures::ORDER_1_REFERENCE,
                'quantity' => 2,
                'productPrice' => 25.99,
            ],
            [
                'productReference' => ProductFixtures::PRODUCT_2_REFERENCE,
                'orderReference' => OrderFixtures::ORDER_1_REFERENCE,
                'quantity' => 1,
                'productPrice' => 89.99,
            ],
            [
                'productReference' => ProductFixtures::PRODUCT_3_REFERENCE,
                'orderReference' => OrderFixtures::ORDER_2_REFERENCE,
                'quantity' => 1,
                'productPrice' => 299.99,
            ],
            [
                'productReference' => ProductFixtures::PRODUCT_5_REFERENCE,
                'orderReference' => OrderFixtures::ORDER_3_REFERENCE,
                'quantity' => 3,
                'productPrice' => 34.99,
            ],
            [
                'productReference' => ProductFixtures::PRODUCT_5_REFERENCE,
                'orderReference' => OrderFixtures::ORDER_4_REFERENCE,
                'quantity' => 1,
                'productPrice' => 49.99,
            ],
        ];

        foreach ($orderItemsData as $itemData) {
            $orderItem = new OrderItem();
            $orderItem->setQuantity($itemData['quantity']);
            $orderItem->setProductPrice($itemData['productPrice']);
            
            $orderItem->setOfOrder($this->getReference($itemData['orderReference']));
            $orderItem->setProduct($this->getReference($itemData['productReference']));

            $manager->persist($orderItem);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            OrderFixtures::class,
            ProductFixtures::class,
        ];
    }
}
