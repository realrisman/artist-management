<?php

namespace App\Repository;

use App\Entity\Representative;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;

/**
 * @method Representative|null find($id, $lockMode = null, $lockVersion = null)
 * @method Representative|null findOneBy(array $criteria, array $orderBy = null)
 * @method Representative[]    findAll()
 * @method Representative[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepresentativeRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Representative::class);
    }

    /**
     * @return Representative[] Returns an array of Representative objects
     */

    public function findRepresentativesByName($name = false)
    {
        $query = $this->createQueryBuilder('r')
            ->andWhere("r.status != 'deleted'")
            ->andWhere('r.valid_till > CURRENT_TIMESTAMP()')
            ->orderBy('r.name', 'ASC')
            ->setMaxResults(10);

        if ($name) {
            $originalName = trim($name);
            $name = str_replace(" ", "%", trim($name));
            $query->andWhere($query->expr()->like('r.name', ':name'))
                ->setParameter('name', '%' . $name . '%')
                ->orderBy('FIELD(r.name,:originalname)', 'DESC')
                ->setParameter('originalname', $originalName);
        }

        return $query->getQuery()
            ->getResult();
    }

    /**
     * @return Paginator|Representative[]
     */

    public function findRepresentativesByFilter($filter)
    {
        $query = $this->getFilterQuery($filter);

        $paginator = new Paginator($query, $fetchJoinCollection = true);

        return $paginator;
    }

    /**
     * @return Paginator
     */

    public function findRepresentativesByFilterForExport($filter)
    {
        $query = $this->getFilterQuery($filter);
        $query->leftJoin('r.representativeConnections', 'con')
            ->leftJoin('con.celebrity', 'celebrity')
            ->addSelect('con')
            ->addSelect('celebrity');

        $paginator = new Paginator($query, $fetchJoinCollection = true);

        return $paginator;
    }

    /**
     * @return Representative[] Returns an array of Representative objects
     */

    public function findCompaniesByName($name = false)
    {
        $query = $this->createQueryBuilder('r')
            ->orderBy('r.id', 'ASC')
            ->groupBy('r.companyName')
            ->setMaxResults(10);

        if ($name) {
            $name = str_replace(" ", "%", trim($name));
            $query->andWhere($query->expr()->like('r.companyName', ':name'))
                ->setParameter('name', '%' . $name . '%');
        }

        return $query->getQuery()
            ->getResult();
    }


    public function getMaxUnid(): ?int
    {
        $query = $this->createQueryBuilder('a');
        $query->select('MAX(a.unid) AS max_unid');
        $query->setMaxResults(1);

        try {
            $res = $query->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
        }

        return $res;
    }

    public function findOneByUnid($unid): ?Representative
    {
        try {
            return $this->createQueryBuilder('r')
                ->andWhere('r.valid_till > CURRENT_TIMESTAMP()')
                ->andWhere('r.unid = :unid')
                ->leftJoin('r.phones', 'phones', Expr\Join::WITH, 'phones.deleted = 0')
                ->leftJoin('r.emails', 'emails', Expr\Join::WITH, 'emails.deleted = 0')
                ->leftJoin('r.categories', 'categories')
                ->leftJoin('r.companies', 'company')
                ->addSelect('phones')
                ->addSelect('emails')
                ->addSelect('categories')
                ->addSelect('company')
                ->setParameter('unid', $unid)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findOneByName($name): ?Representative
    {
        try {
            return $this->createQueryBuilder('r')
                ->andWhere('r.valid_till > CURRENT_TIMESTAMP()')
                ->andWhere('r.name = :name')
                ->setParameter('name', $name)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }


    public function findLogsByUnid($unid)
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.user', 'u')
            ->leftJoin('r.emails', 'emails')
            ->leftJoin('r.phones', 'phones')
            ->addSelect('u')
            ->addSelect('phones')
            ->addSelect('emails')
            ->andWhere('r.unid = :unid')
            ->setParameter('unid', $unid)
            ->orderBy('r.valid_from', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLogs($filter)
    {
        $query = $this->createQueryBuilder('r')
            ->leftJoin('r.categories', 'category')
            ->innerJoin('r.user', 'user')
            ->addSelect('category')
            ->addSelect('user');


        switch ($filter['field']) {
            case "date":
                $query->andWhere('r.valid_till BETWEEN :minDate AND :maxDate')
                    ->setParameter('minDate', (new DateTime($filter['search']))->format("Y-m-d 00:00:00"))
                    ->setParameter('maxDate', (new DateTime($filter['search']))->format("Y-m-d 23:59:59"));
                break;
            case "name":
                $filter['search'] = trim($filter['search']);
                $query->andWhere($query->expr()->like('r.name', ':name'))->setParameter('name', "%" . $filter['search'] . "%");
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
            $query->andWhere('r.valid_till BETWEEN :minDate AND :maxDate')
                ->setParameter('minDate', $filter['date'] . " 00-00-00")
                ->setParameter('maxDate', $filter['date'] . " 23-59-59");
        }

        if (!empty($filter['sort'])) {
            switch ($filter['sort']) {
                case "added":
                    $query->addOrderBy('r.created', $order);
                    break;
                case "modified":
                    $query->addOrderBy('r.valid_from', $order);
                    break;
                case "name":
                    $query->addOrderBy('r.name', $order);
                    break;
                default:
                    $query->addOrderBy('r.unid', $order);
                    break;
            }
        } else {
            $query->addOrderBy('r.valid_from', $order);
        }

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
     * @param $limit
     * @return Representative[]
     */
    public function findRepresentativesWithOldLogs($limit)
    {
        return $this->createQueryBuilder('r')
            ->groupBy('r.unid')
            ->having('COUNT(r.id) > :limit')
            ->setParameter('limit', $limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $limit
     * @param $unid
     * @return Representative[]
     */
    public function findOldLogsForUnid($limit, $unid)
    {
        return $this->createQueryBuilder('r')
            ->where('r.unid = :unid')
            ->setParameter('unid', $unid)
            ->orderBy('r.valid_till', 'DESC')
            ->setFirstResult($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Representative $representative
     * @return Representative|null
     */
    public function findPreviousVersion(Representative $representative)
    {
        $data = $this->createQueryBuilder('representative')
            ->andWhere('representative.unid = :unid')
            ->andWhere('representative.valid_till < :valid')
            ->orderBy('representative.valid_till', 'DESC')
            ->setParameter('unid', $representative->getUnid())
            ->setParameter('valid', $representative->getValidTill())
            ->getQuery()
            ->getResult();

        if (is_array($data) && !empty($data)) {

            return $data[0];
        } else {
            return null;
        }
    }

    /**
     * @param $unid
     * @return Representative[]
     */
    public function findSources($unid)
    {

        $query = $this->createQueryBuilder('representative')
            ->innerJoin('representative.user', 'user')
            ->addSelect('user')
            ->andWhere('representative.unid = :unid');
        $query->andWhere(
            $query->expr()->not(
                $query->expr()->orX(
                    $query->expr()->eq('representative.source', ':source'),
                    $query->expr()->isNull('representative.source')
                )
            )
        );

        return $query->orderBy('representative.valid_till', 'DESC')
            ->setParameter('unid', $unid)
            ->setParameter('source', '')
            ->getQuery()
            ->getResult();
    }

    public function getTotalRepresentativesForVerification()
    {
        $qb = $this->createQueryBuilder('representative');
        $qb->select('count(representative.unid)');
        $qb->andWhere('representative.needsVerifyFlag >= 1');
        $qb->andWhere('representative.valid_till > CURRENT_TIMESTAMP()');


        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $filter
     * @return Representative[]
     */
    public function getCelebritiesForVerification($filter)
    {
        $qb = $this->createQueryBuilder('representative');
        $qb->select('representative');
        $qb->andWhere('representative.needsVerifyFlag >= 1');
        $qb->andWhere('representative.valid_till > CURRENT_TIMESTAMP()');
        $qb->orderBy('representative.needsVerifyFlag', 'DESC');

        if (!empty($filter['limit']) && is_numeric($filter['limit'])) {
            $qb->setMaxResults($filter['limit']);
        }
        if (!empty($filter['offset']) && is_numeric($filter['offset'])) {
            $qb->setFirstResult($filter['offset']);
        }
        return $qb->getQuery()->getResult();
    }

    public function findOneByWpId($wp_id): ?Representative
    {
        try {
            return $this->createQueryBuilder('r')
                ->andWhere('r.valid_till > CURRENT_TIMESTAMP()')
                ->andWhere('r.wp_id =: unid')
                ->setParameter('unid', $wp_id)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @param $filter
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getFilterQuery($filter): \Doctrine\ORM\QueryBuilder
    {
        $query = $this->createQueryBuilder('r')
            ->andWhere('r.valid_till > CURRENT_TIMESTAMP()')
            ->innerJoin('r.type', 'rc')
            ->leftJoin('r.categories', 'categories')
            ->leftJoin('r.categories', 'all_categories')
            ->leftJoin('r.phones', 'p', Expr\Join::WITH, 'p.deleted = 0')
            ->leftJoin('r.emails', 'e', Expr\Join::WITH, 'e.deleted = 0')
            ->leftJoin('r.companies', 'company')
            ->addSelect('rc')
            ->addSelect('categories')
            ->addSelect('all_categories')
            ->addSelect('e')
            ->addSelect('company')
            ->addSelect('p');


        foreach (['manager', 'agent', 'publicist'] as $role) {
            if (!empty($filter[$role])) {
                $query
                    ->andWhere('rc.name = :role')
                    ->setParameter('role', $role);
            }
        }
        if (!empty($filter['companies'])) {
            if (is_array($filter['companies'])) {
                $query->andWhere('company.name in (:companies)')->setParameter('companies', $filter['companies']);
            }
        }
        if (!empty($filter['status'])) {
            $query->andWhere('r.status = :status')->setParameter('status', $filter['status']);
        }
        if (!empty($filter['category'])) {
            $query->andWhere('categories.id = :category')->setParameter('category', $filter['category']);
        }
        if (!empty($filter['type'])) {
            $query->andWhere('rc.name = :type')->setParameter('type', $filter['type']);
        }

        if (!empty($filter['name'])) {
            $filter['name'] = str_replace(" ", "%", trim($filter['name']));
            $query->andWhere($query->expr()->like('r.name', ':name'))->setParameter('name', "%" . $filter['name'] . "%");
        }

        if (array_key_exists('primary_category', $filter)) {
            if (is_null($filter['primary_category'])) {
                $query->andWhere($query->expr()->isNull('r.primaryCategory'));
            }
        }
        if (!empty($filter['verification']) && true === $filter['verification']) {
            $query->andWhere('r.needsVerifyFlag >= 1');
            $query->andWhere('r.unableToVerify = 0');
        }
        if (!empty($filter['unable_to_verify']) && "true" === $filter['unable_to_verify']) {
            $query->andWhere('r.unableToVerify = 1');
        }
        if (isset($filter['deleted'])) {
            $query->setParameter('deleted', 'deleted');
            if ($filter['deleted']) {
                $query->andWhere('r.status = :deleted');
            } else {
                $query->andWhere('r.status != :deleted');
            }
        }
        $order = (!empty($filter['order']) && strtolower($filter['order']) == 'desc') ? 'desc' : 'asc';

        if (!empty($filter['sort'])) {
            switch ($filter['sort']) {
                case "verify":
                    $query->orderBy('r.needsVerifyFlag', $order);
                    break;
                case "added":
                    $query->orderBy('r.created', $order);
                    break;
                case "modified":
                    $query->orderBy('r.valid_from', $order);
                    break;
                case "name":
                    $query->orderBy('r.name', $order);
                    break;
                case "company":
                    $query->orderBy('company.name', $order);
                    break;
                default:
                    $query->orderBy('r.valid_from', $order);
                    break;
            }
        } else {
            $query->orderBy('r.valid_from', $order);
        }

        if (!empty($filter['limit']) && is_numeric($filter['limit'])) {
            $query->setMaxResults($filter['limit']);
        }
        if (!empty($filter['offset']) && is_numeric($filter['offset'])) {
            $query->setFirstResult($filter['offset']);
        }
        return $query;
    }
}
