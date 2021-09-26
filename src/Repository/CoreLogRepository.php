<?php

namespace App\Repository;

use DateTime;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class CoreLogRepository extends ServiceEntityRepository
{
    public function findLogs($filter)
    {
        $query = $this->createQueryBuilder('lr')
            ->innerJoin('lr.user', 'user')
            ->addSelect('user');


        $query = $this->addFilters($filter, $query);

        if (!empty($filter['limit']) && is_numeric($filter['limit'])) {
            $query->setMaxResults($filter['limit']);
        }
        if (!empty($filter['offset']) && is_numeric($filter['offset'])) {
            $query->setFirstResult($filter['offset']);
        }

        return $query->getQuery()->getResult();
    }

    public function findLogsCount($filter)
    {
        $query = $this->createQueryBuilder('lr')
            ->innerJoin('lr.user', 'user')
            ->select('count(lr.id)');


        $query = $this->addFilters($filter, $query);

        return $query->getQuery()->getSingleScalarResult();
    }

    public function findSources($unid)
    {
        $query = $this->createQueryBuilder('lr')
            ->innerJoin('lr.user', 'user')
            ->addSelect('user')
            ->andWhere('lr.unid = :unid')
            ->setParameter('unid', $unid)
            ->addOrderBy('lr.date', 'DESC');


        return $query->getQuery()
            ->getResult();
    }

    /**
     * @param $filter
     * @param QueryBuilder $query
     * @return mixed
     */
    protected function addFilters($filter, QueryBuilder $query)
    {
        switch ($filter['field']) {
            case "date":
                $query->andWhere('lr.date BETWEEN :minDate AND :maxDate')
                    ->setParameter('minDate', (new DateTime($filter['search']))->format("Y-m-d 00:00:00"))
                    ->setParameter('maxDate', (new DateTime($filter['search']))->format("Y-m-d 23:59:59"));
                break;
            case "name":
            case "category":
                $filter['search'] = trim($filter['search']);
                if (!empty($filter['search'])) {
                    $query->andWhere($query->expr()->like('lr.new', ':name'))->setParameter('name', "%" . $filter['search'] . "%");
                }
                break;
            case "user":
                $filter['search'] = trim($filter['search']);
                $query->andWhere($query->expr()->like('user.login', ':login'))->setParameter('login', "%" . $filter['search'] . "%");
                break;
        }

        $order = (!empty($filter['order']) && strtolower($filter['order']) == 'asc') ? 'asc' : 'desc';

        if (!empty($filter['user'])) {
            $query->andWhere('user.id = :id')->setParameter('id', $filter['user']);
            $query->addOrderBy('user.id', $order);
        }
        if (!empty($filter['date'])) {
            $query->andWhere('lr.date BETWEEN :minDate AND :maxDate')
                ->setParameter('minDate', $filter['date'] . " 00-00-00")
                ->setParameter('maxDate', $filter['date'] . " 23-59-59");
        }
        if (!empty($filter['from']) && !empty($filter['to'])) {
            $query->andWhere('lr.date BETWEEN :minDate AND :maxDate')
                ->setParameter('minDate', $filter['from'] . " 00-00-00")
                ->setParameter('maxDate', $filter['to'] . " 23-59-59");
        } else if (!empty($filter['from'])) {
            $query->andWhere('lr.date >= :minDate')
                ->setParameter('minDate', $filter['from'] . " 00-00-00");
        } else if (!empty($filter['to'])) {
            $query->andWhere('lr.date <= :maxDate')
                ->setParameter('maxDate', $filter['to'] . " 23-59-59");
        }

        if (!empty($filter['sort'])) {
            switch ($filter['sort']) {
                default:
                case "modified":
                    $query->addOrderBy('lr.date', $order);
                    break;
            }
        } else {
            $query->addOrderBy('lr.date', $order);
        }

        return $query;
    }
}
