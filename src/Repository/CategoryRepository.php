<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @return Category[] Returns an array of Category objects
     */

    public function fetchParents($reps = false)
    {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.categories', 'children')
            ->addSelect('children')
            ->where('c.parent is NULL')
            ->orderBy('c.name', 'ASC');
        $representativeCategoryIds = [2, 2002, 2032, 2052, 2062, 2027, 2082];
        if ($reps) {
            $query->andWhere('c.id IN (:ids)')->setParameter('ids', $representativeCategoryIds);
        } else {
            $query->andWhere('c.id NOT IN (:ids)')->setParameter('ids', $representativeCategoryIds);
        }

        return
            $query->getQuery()
            ->getResult();
    }
}
