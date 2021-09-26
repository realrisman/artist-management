<?php

namespace App\Repository;

use App\Entity\RepresentativeConnection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RepresentativeConnection|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepresentativeConnection|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepresentativeConnection[]    findAll()
 * @method RepresentativeConnection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepresentativeConnectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepresentativeConnection::class);
    }

    /**
     * @return RepresentativeConnection[] Returns an array of Representative objects
     */

    public function findRepsByNameAndType($type = '', $name = false)
    {
        $query = $this->createQueryBuilder('rc')
            ->leftJoin('rc.representative', 'r')
            ->leftJoin('rc.company', 'c')
            ->addSelect('r')
            ->addSelect('c')
            ->andWhere('rc.type = :type')
            ->setParameter('type', $type)
            ->addGroupBy('c.name')
            ->addGroupBy('r.name')
            ->orderBy('rc.id', 'ASC')
            ->setMaxResults(10);

        if ($name) {
            $name = str_replace(" ", "%", trim($name));
            $query->andWhere(
                $query->expr()->orX(
                    $query->expr()->like('r.name', ':rname'),
                    $query->expr()->like('c.name', ':cname')
                )
            )
                ->setParameter('rname', '%' . $name . '%')
                ->setParameter('cname', '%' . $name . '%')
                ->orderBy('FIELD(r.name,:originalNameR)', 'DESC')
                ->setParameter('originalNameR', $name);
        }

        return $query->getQuery()
            ->getResult();
    }
    /**
     * @return RepresentativeConnection[] Returns an array of Representative objects
     */

    public function findAgentsByName($name = false)
    {
        return $this->findRepsByNameAndType('agent', $name);
    }

    /**
     * @return RepresentativeConnection[] Returns an array of Representative objects
     */

    public function findManagersByName($name = false)
    {
        return $this->findRepsByNameAndType('manager', $name);
    }

    /**
     * @return RepresentativeConnection[] Returns an array of Representative objects
     */

    public function findPublicistsByName($name = false)
    {
        return $this->findRepsByNameAndType('publicist', $name);
    }
}
