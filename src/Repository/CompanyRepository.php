<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    /**
     * @param $filter
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFilterQuery($filter): \Doctrine\ORM\QueryBuilder
    {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.locations', 'locations')
            ->leftJoin('c.categories', 'categories')
            ->addSelect('locations')
            ->addSelect('categories');


        if (!empty($filter['status'])) {
            $query->andWhere('c.status = :status')->setParameter('status', $filter['status']);
        }

        if (!empty($filter['name'])) {
            $filter['name'] = str_replace(" ", "%", trim($filter['name']));
            $query->andWhere($query->expr()->like('c.name', ':name'))->setParameter('name', "%" . $filter['name'] . "%");
        }

        if (!empty($filter['verification']) && true === $filter['verification']) {
            $query->andWhere('c.needsVerifyFlag >= 1');
        }

        $order = (!empty($filter['order']) && strtolower($filter['order']) == 'desc') ? 'desc' : 'asc';
        if (!empty($filter['sort'])) {
            switch ($filter['sort']) {
                case "verify":
                    $query->orderBy('c.needsVerifyFlag', $order);
                    break;
                case "added":
                    $query->orderBy('c.created', $order);
                    break;
                case "name":
                    $query->orderBy('c.name', $order);
                    break;
                case "status":
                    $query->orderBy('c.status', $order);
                    break;
                case "modified":
                default:
                    $query->orderBy('c.lastUpdatedAt', $order);
                    break;
            }
        } else {
            $query->orderBy('c.lastUpdatedAt', $order);
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

    /**
     * @param bool $name
     * @return Company[]|null
     */
    public function findCompaniesByName($name = false)
    {
        $query = $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10);

        if ($name) {
            $originalName = trim($name);
            $name = str_replace(" ", "%", trim($name));
            $query->andWhere($query->expr()->like('c.name', ':name'))
                ->setParameter('name', '%' . $name . '%')
                ->orderBy('FIELD(c.name, :originalName)', 'DESC')
                ->setParameter('originalName',  $originalName);
        }

        return $query->getQuery()
            ->getResult();
    }
}
