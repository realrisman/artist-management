<?php

namespace App\Repository;

use App\Entity\UniqueLinkCelebrity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UniqueLinkCelebrity|null find($id, $lockMode = null, $lockVersion = null)
 * @method UniqueLinkCelebrity|null findOneBy(array $criteria, array $orderBy = null)
 * @method UniqueLinkCelebrity[]    findAll()
 * @method UniqueLinkCelebrity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UniqueLinkCelebrityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UniqueLinkCelebrity::class);
    }
    /**
     * @param $filter
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFilterQuery($filter): \Doctrine\ORM\QueryBuilder
    {
        $query = $this->createQueryBuilder('ul')
            ->leftJoin('ul.celebrity', 'celebrity')
            ->leftJoin('ul.user', 'user')
            ->addSelect('celebrity')
            ->addSelect('user');

        $order = (!empty($filter['order']) && strtolower($filter['order']) == 'desc') ? 'desc' : 'asc';
        if (!empty($filter['sort'])) {
            switch ($filter['sort']) {
                default:
                    $query->orderBy('ul.updated_at', $order);
                    break;
            }
        } else {
            $query->orderBy('ul.updated_at', $order);
        }

        if (!empty($filter['limit']) && is_numeric($filter['limit'])) {
            $query->setMaxResults($filter['limit']);
        }
        if (!empty($filter['offset']) && is_numeric($filter['offset'])) {
            $query->setFirstResult($filter['offset']);
        }

        return $query;
    }

    public function findByFilter($filter)
    {
        $query = $this->getFilterQuery($filter);

        $paginator = new Paginator($query, $fetchJoinCollection = true);

        return $paginator;
    }
}
