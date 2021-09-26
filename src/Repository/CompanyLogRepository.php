<?php

namespace App\Repository;

use App\Entity\CompanyLog;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompanyLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyLog[]    findAll()
 * @method CompanyLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyLogRepository extends CoreLogRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyLog::class);
    }

    /**
     * @return CompanyLog[]
     */
    public function findLogs($filter)
    {
        return parent::findLogs($filter);
    }

    public function findLogsCount($filter)
    {
        return parent::findLogsCount($filter);
    }

    /**
     * @param $unid
     * @return CompanyLog[]|null
     */
    public function findSources($unid)
    {
        return parent::findSources($unid);
    }
}
