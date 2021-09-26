<?php

namespace App\Repository;

use App\Entity\RepresentativeType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RepresentativeType|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepresentativeType|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepresentativeType[]    findAll()
 * @method RepresentativeType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepresentativeTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepresentativeType::class);
    }
}
