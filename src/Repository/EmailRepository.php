<?php

namespace App\Repository;

use App\Entity\Email;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Email|null find($id, $lockMode = null, $lockVersion = null)
 * @method Email|null findOneBy(array $criteria, array $orderBy = null)
 * @method Email[]    findAll()
 * @method Email[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Email::class);
    }

    public function findEmails()
    {
        $checkDate = new \DateTime();
        $checkDate->modify("-1 day");
        $finalDate = $checkDate->format("Y-m-d H:i:s");
        $query = $this->createQueryBuilder('e');
        $query->Where('e.lastUpdatedAt <= :minDatetime OR e.lastUpdatedAt is NULL')
            ->setParameter('minDatetime', $finalDate);
        $query->setMaxResults(10);
        return $query->getQuery()->getResult();
    }
}
