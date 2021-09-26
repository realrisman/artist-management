<?php

namespace App\Repository;

use App\Entity\ForbiddenRepresentative;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ForbiddenRepresentative|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForbiddenRepresentative|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForbiddenRepresentative[]    findAll()
 * @method ForbiddenRepresentative[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForbiddenRepresentativeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForbiddenRepresentative::class);
    }
}
