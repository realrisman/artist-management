<?php

namespace App\Repository;

use App\Entity\Celebrity;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;

/**
 * @method Celebrity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Celebrity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Celebrity[]    findAll()
 * @method Celebrity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CelebrityRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Celebrity::class);
    }

    public function getMaxUnid(): ?int
    {
        $query = $this->createQueryBuilder('c');
        $query->select('MAX(c.unid) AS max_unid');
        $query->setMaxResults(1);

        try {
            $res = $query->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
        }

        return $res;
    }

    /**
     * @return Paginator
     */

    public function findByFilter($filter)
    {
        $query = $this->getFilterQuery($filter);

        $paginator = new Paginator($query, $fetchJoinCollection = true);

        return $paginator;
    }


    public function findOneByUnid($unid): ?Celebrity
    {
        try {
            return $this->createQueryBuilder('c')
                ->leftJoin('c.links', 'links', Expr\Join::WITH, 'links.deleted = 0')
                ->leftJoin('c.representativeConnections', 'rc')
                ->leftJoin('rc.representative', 'r')
                ->leftJoin('r.emails', 'emails')
                ->leftJoin('r.phones', 'phones')
                ->leftJoin('c.category', 'category')
                ->leftJoin('r.categories', 'categories')
                ->leftJoin('c.primaryCategory', 'primaryCategory')
                ->addSelect('links')
                ->addSelect('rc')
                ->addSelect('r')
                ->addSelect('emails')
                ->addSelect('phones')
                ->addSelect('category')
                ->addSelect('categories')
                ->addSelect('primaryCategory')
                ->andWhere('c.valid_till > CURRENT_TIMESTAMP()')
                ->andWhere('c.unid = :unid')
                ->setParameter('unid', $unid)
                ->orderBy('rc.position', 'ASC')
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
    public function findOneByWpId($wp_id): ?Celebrity
    {
        try {
            return $this->createQueryBuilder('c')
                ->andWhere('c.valid_till > CURRENT_TIMESTAMP()')
                ->setParameter('unid', $wp_id)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findOneByName($name): ?Celebrity
    {
        try {
            return $this->createQueryBuilder('c')
                ->andWhere('c.valid_till > CURRENT_TIMESTAMP()')
                ->andWhere('c.name = :name')
                ->setParameter('name', $name)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findLogsByUnid($unid)
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.user', 'u')
            ->leftJoin('c.links', 'links')
            ->leftJoin('c.representativeConnections', 'rc')
            ->leftJoin('rc.representative', 'r')
            ->leftJoin('r.phones', 'phones')
            ->leftJoin('r.emails', 'emails')
            ->leftJoin('c.category', 'category')
            ->addSelect('links')
            ->addSelect('rc')
            ->addSelect('r')
            ->addSelect('emails')
            ->addSelect('phones')
            ->addSelect('category')
            ->addSelect('u')
            ->andWhere('c.unid = :unid')
            ->setParameter('unid', $unid)
            ->orderBy('c.valid_from', 'DESC')
            ->addOrderBy('r.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Paginator
     */

    public function findLogs($filter)
    {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.category', 'category')
            ->innerJoin('c.user', 'user')
            ->addSelect('category')
            ->addSelect('user');


        switch ($filter['field']) {
            case "date":
                $query->andWhere('c.valid_till BETWEEN :minDate AND :maxDate')
                    ->setParameter('minDate', (new DateTime($filter['search']))->format("Y-m-d 00:00:00"))
                    ->setParameter('maxDate', (new DateTime($filter['search']))->format("Y-m-d 23:59:59"));
                break;
            case "name":
                $filter['search'] = trim($filter['search']);
                $query->andWhere($query->expr()->like('c.name', ':name'))->setParameter('name', "%" . $filter['search'] . "%");
                break;
            case "type":
                //                $query->andWhere('c. = :name')->setParameter('name',$filter['search']);
                break;
            case "category":
                $filter['search'] = trim($filter['search']);
                $query->andWhere($query->expr()->like('category.name', ':name'))->setParameter('name', "%" . $filter['search'] . "%");
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
            $query->andWhere('c.valid_till BETWEEN :minDate AND :maxDate')
                ->setParameter('minDate', $filter['date'] . " 00-00-00")
                ->setParameter('maxDate', $filter['date'] . " 23-59-59");
        }

        if (!empty($filter['sort'])) {
            switch ($filter['sort']) {
                case "added":
                    $query->addOrderBy('c.created', $order);
                    break;
                case "modified":
                    $query->addOrderBy('c.valid_from', $order);
                    break;
                case "name":
                    $query->addOrderBy('c.name', $order);
                    break;
                case "status":
                    $query->addOrderBy('c.status', $order);
                    break;
                default:
                    $query->addOrderBy('c.unid', $order);
                    break;
            }
        } else {
            $query->addOrderBy('c.valid_from', $order);
        }

        //        $query->andWhere('c.valid_till < CURRENT_TIMESTAMP()');

        $paginator = new Paginator($query, $fetchJoinCollection = true);

        if (!empty($filter['limit']) && is_numeric($filter['limit'])) {
            $paginator->getQuery()->setMaxResults($filter['limit']);
        }
        if (!empty($filter['offset']) && is_numeric($filter['offset'])) {
            $paginator->getQuery()->setFirstResult($filter['offset']);
        }

        return $paginator;
    }

    /**
     * finds previous celebrity version
     * @param Celebrity $celebrity
     * @return Celebrity|null
     */
    public function findPreviousVersion(Celebrity $celebrity)
    {
        $data = $this->createQueryBuilder('celebrity')
            ->andWhere('celebrity.unid = :unid')
            ->andWhere('celebrity.valid_till < :valid')
            ->orderBy('celebrity.valid_till', 'DESC')
            ->setParameter('unid', $celebrity->getUnid())
            ->setParameter('valid', $celebrity->getValidTill())
            ->getQuery()
            ->getResult();

        if (is_array($data) && !empty($data)) {

            return $data[0];
        } else {
            return null;
        }
    }

    /**
     * @param $limit
     * @return Celebrity[]
     */
    public function findRepresentativesWithOldLogs($limit)
    {
        return $this->createQueryBuilder('celebrity')
            ->groupBy('celebrity.unid')
            ->having('COUNT(celebrity.id) > :limit')
            ->setParameter('limit', $limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $limit
     * @param $unid
     * @return Celebrity[]
     */
    public function findOldLogsForUnid($limit, $unid)
    {
        //do not join entities cause limit is working incorrectly with them
        return $this->createQueryBuilder('celebrity')
            ->where('celebrity.unid = :unid')
            ->orderBy('celebrity.valid_till', 'DESC')
            ->setParameter('unid', $unid)
            ->setFirstResult($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $unid
     * @return Celebrity[]
     */
    public function findSources($unid)
    {

        $query = $this->createQueryBuilder('celebrity')
            ->innerJoin('celebrity.user', 'user')
            ->addSelect('user')
            ->andWhere('celebrity.unid = :unid');
        $query->andWhere(
            $query->expr()->not(
                $query->expr()->orX(
                    $query->expr()->eq('celebrity.source', ':source'),
                    $query->expr()->isNull('celebrity.source')
                )
            )
        );

        return $query->orderBy('celebrity.valid_till', 'DESC')
            ->setParameter('unid', $unid)
            ->setParameter('source', '')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getTotalCelebritiesForVerification()
    {
        $qb = $this->createQueryBuilder('celebrity');
        $qb->select('count(celebrity.unid)');
        $qb->andWhere('celebrity.needsVerifyFlag >= 1');
        $qb->andWhere('celebrity.valid_till > CURRENT_TIMESTAMP()');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $filter
     * @return Celebrity[]
     */
    public function getCelebritiesForVerification($filter)
    {

        $qb = $this->createQueryBuilder('celebrity');
        $qb->select('celebrity');
        $qb->andWhere('celebrity.needsVerifyFlag >= 1');
        $qb->andWhere('celebrity.valid_till > CURRENT_TIMESTAMP()');
        $qb->orderBy('celebrity.needsVerifyFlag', 'DESC');

        if (!empty($filter['limit']) && is_numeric($filter['limit'])) {
            $qb->setMaxResults($filter['limit']);
        }
        if (!empty($filter['offset']) && is_numeric($filter['offset'])) {
            $qb->setFirstResult($filter['offset']);
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * @param $filter
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFilterQuery($filter): \Doctrine\ORM\QueryBuilder
    {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.representativeConnections', 'con')
            ->leftJoin('c.category', 'category')
            ->addSelect('con')
            ->addSelect('category')
            ->leftJoin('con.representative', 'r')
            ->leftJoin('r.phones', 'phones')
            ->leftJoin('r.emails', 'emails')
            ->addSelect('r')
            ->addSelect('phones')
            ->addSelect('emails');

        foreach (['manager', 'agent', 'publicist'] as $role) {
            if (!empty($filter[$role])) {
                $query
                    ->leftJoin('r.type', 'rtype')
                    ->andWhere('r.id = :rid')
                    ->andWhere('con.type = :role')
                    ->setParameter('rid', $filter[$role])
                    ->setParameter('role', $role);
            }
        }

        if (!empty($filter['status'])) {
            $query->andWhere('c.status = :status')->setParameter('status', $filter['status']);
        }

        if (!empty($filter['noreps']) && "true" === $filter['noreps']) {
            $query->andWhere('con is NULL');
        }

        if (!empty($filter['name'])) {
            $filter['name'] = str_replace(" ", "%", trim($filter['name']));
            $query->andWhere($query->expr()->like('c.name', ':name'))->setParameter('name', "%" . $filter['name'] . "%");
        }

        if (!empty($filter['companies'])) {
            if (is_array($filter['companies'])) {
                $query->innerJoin('c.representativeConnections', 'con2')
                    ->leftJoin('con2.representative', 'r2')
                    ->leftJoin('con2.company', 'companyRep')
                    ->leftJoin('r2.companies', 'company');
                $query->andWhere(
                    $query->expr()->orX(
                        $query->expr()->in('company.name', ':companies'),
                        $query->expr()->in('companyRep.name', ':companies')
                    )
                )
                    ->setParameter('companies', $filter['companies']);
            }
        }

        if (!empty($filter['verification']) && true === $filter['verification']) {
            $query->andWhere('c.needsVerifyFlag >= 1');
            $query->andWhere('c.unableToVerify = 0');
        }
        if (!empty($filter['unable_to_verify']) && "true" === $filter['unable_to_verify']) {
            $query->andWhere('c.unableToVerify = 1');
        }
        if (!empty($filter['alive']) && true === $filter['alive']) {
            $query->andWhere('c.deceased = 0');
        }

        if (isset($filter['deleted'])) {
            $query->setParameter('deleted', 'deleted');
            if ($filter['deleted']) {
                $query->andWhere('c.status = :deleted');
            } else {
                $query->andWhere('c.status != :deleted');
            }
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
                case "modified":
                    $query->orderBy('c.valid_from', $order);
                    break;
                case "name":
                    $query->orderBy('c.name', $order);
                    break;
                case "status":
                    $query->orderBy('c.status', $order);
                    break;
                case "company":
                    if (!empty($filter['companies'])) {
                        $query->orderBy('company.name', $order);
                    }
                    break;
                default:
                    $query->orderBy('c.valid_from', $order);
                    break;
            }
        } else {
            $query->orderBy('c.valid_from', $order);
        }

        $query->andWhere('c.valid_till > CURRENT_TIMESTAMP()');

        if (!empty($filter['limit']) && is_numeric($filter['limit'])) {
            $query->setMaxResults($filter['limit']);
        }
        if (!empty($filter['offset']) && is_numeric($filter['offset'])) {
            $query->setFirstResult($filter['offset']);
        }

        return $query;
    }

    /**
     * @param $name
     * @return Celebrity[]
     */
    public function findByName($name)
    {
        $query = $this->createQueryBuilder('c')
            ->setMaxResults(10);

        if ($name) {
            $name = str_replace(" ", "%", trim($name));
            $query->andWhere(
                $query->expr()->orX(
                    $query->expr()->like('c.name', ':cname')
                )
            )
                ->setParameter('cname', '%' . $name . '%')
                ->orderBy('FIELD(c.name,:originalNameR)', 'DESC')
                ->setParameter('originalNameR', $name);
        }

        return $query->getQuery()
            ->getResult();
    }
}
