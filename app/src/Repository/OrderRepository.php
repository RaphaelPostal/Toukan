<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Order $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Order $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function getPreviousOrders($order)
    {
        return $this->createQueryBuilder('o')
            ->select('o')
            ->where('o.status = :status')
            ->setParameter('status', Order::STATUS_IN_PROGRESS)
            ->andWhere('o.createdAt < :order')
            ->setParameter('order', $order->getCreatedAt())
            ->getQuery()
            ->getResult();
    }

    public function isTableOrdered($table)
    {
        $qb = $this->createQueryBuilder('o')
            ->select('o')
            ->orWhere('o.status = :ordering')
            ->setParameter('ordering', Order::STATUS_ORDERING)
            ->orWhere('o.status = :in_progress')
            ->setParameter('in_progress', Order::STATUS_IN_PROGRESS)
            ->orWhere('o.status = :delivered')
            ->setParameter('delivered', Order::STATUS_DELIVERED)
            ->andWhere('o.establishmentTable = :table')
            ->setParameter('table', $table)
            ->getQuery()
            ->getResult();
            if (count($qb) > 0){
                return true;
            } else {
                return false;
            }
    }

    public function getLastOrderByTable($table){
        return $this->createQueryBuilder('o')
            ->select('o')
            ->orWhere('o.status = :ordering')
            ->setParameter('ordering', Order::STATUS_ORDERING)
            ->orWhere('o.status = :in_progress')
            ->setParameter('in_progress', Order::STATUS_IN_PROGRESS)
            ->orWhere('o.status = :delivered')
            ->setParameter('delivered', Order::STATUS_DELIVERED)
            ->andWhere('o.establishmentTable = :table')
            ->setParameter('table', $table)
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }


    // /**
    //  * @return Order[] Returns an array of Order objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
