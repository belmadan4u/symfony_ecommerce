<?php
namespace App\DataFixtures;

use App\Entity\Order;
use App\Enum\OrderStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public const ORDER_1_REFERENCE = 'order_1';
    public const ORDER_2_REFERENCE = 'order_2';
    public const ORDER_3_REFERENCE = 'order_3';
    public const ORDER_4_REFERENCE = 'order_4';
    public const ORDER_5_REFERENCE = 'order_5';

    public function load(ObjectManager $manager): void
    {
        $ordersData = [
            [
                'reference' => self::ORDER_1_REFERENCE,
                'userReference' => UserFixtures::USER_ALICE_REFERENCE,
                'createdAt' => new \DateTimeImmutable(),
                'status' => OrderStatus::enPreparation,
            ],
            [
                'reference' => self::ORDER_2_REFERENCE,
                'userReference' => UserFixtures::USER_BOB_REFERENCE,
                'createdAt' => new \DateTimeImmutable(),
                'status' => OrderStatus::livree,
            ],
            [
                'reference' => self::ORDER_3_REFERENCE,
                'userReference' => UserFixtures::USER_CHARLIE_REFERENCE,
                'createdAt' => new \DateTimeImmutable(),
                'status' => OrderStatus::expediee,
            ],
            [
                'reference' => self::ORDER_4_REFERENCE,
                'userReference' => UserFixtures::USER_DAVID_REFERENCE,
                'createdAt' => new \DateTimeImmutable(),
                'status' => OrderStatus::annulee,
            ],
            [
                'reference' => self::ORDER_5_REFERENCE,
                'userReference' => UserFixtures::USER_EVE_REFERENCE,
                'createdAt' => new \DateTimeImmutable(),
                'status' => OrderStatus::enPreparation,
            ],
        ];

        foreach ($ordersData as $orderData) {
            $order = new Order();
            $order->setCreatedAt($orderData['createdAt'])
                  ->setStatus($orderData['status'])
                  ->setOfUser($this->getReference($orderData['userReference']));

            $manager->persist($order);

            // Ajoute une référence nommée
            $this->addReference($orderData['reference'], $order);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
