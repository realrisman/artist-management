<?php

namespace App\Repository;

use App\Entity\Unids;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Unids|null find($id, $lockMode = null, $lockVersion = null)
 * @method Unids|null findOneBy(array $criteria, array $orderBy = null)
 * @method Unids[]    findAll()
 * @method Unids[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnidsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Unids::class);
    }
}
