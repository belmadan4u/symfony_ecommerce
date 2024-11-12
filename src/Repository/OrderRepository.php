<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    //    /**
    //     * @return Order[] Returns an array of Order objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Order
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function MontantOfOrdersForTheLast12Months(): float
    {
        return $this->createQueryBuilder('o')
            ->select('SUM(oi.quantity * oi.productPrice) AS totalRevenue')
            ->join('o.orderItems', 'oi') 
            ->where('o.createdAt > :lastYear')
            ->setParameter('lastYear', new \DateTime('-1 year'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function FiveLastOrder(): array
    {
        return $this->createQueryBuilder('o')
        ->setMaxResults(5)
        ->orderBy('o.id', 'DESC')
        ->getQuery()
        ->getResult();
    }

    public function getOrdersByUser(User $user): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.ofUser = :userId')
            ->orderBy('o.createdAt', 'DESC')
            ->setParameter('userId', $user->getId()->toBinary())
            ->getQuery()
            ->getResult();
    }
    
}
