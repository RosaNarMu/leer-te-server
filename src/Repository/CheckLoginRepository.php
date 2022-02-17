<?php

namespace App\Repository;

use App\Entity\CheckLogin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CheckLogin|null find($id, $lockMode = null, $lockVersion = null)
 * @method CheckLogin|null findOneBy(array $criteria, array $orderBy = null)
 * @method CheckLogin[]    findAll()
 * @method CheckLogin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheckLoginRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CheckLogin::class);
    }

    // /**
    //  * @return CheckLogin[] Returns an array of CheckLogin objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CheckLogin
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
