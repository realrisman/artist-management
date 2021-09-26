<?php

namespace App\Repository;

use App\Entity\RepresentativeLog;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RepresentativeLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepresentativeLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepresentativeLog[]    findAll()
 * @method RepresentativeLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepresentativeLogRepository  extends CoreLogRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepresentativeLog::class);
    }

    /**
     * @return RepresentativeLog[]
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
     * @return RepresentativeLog[]|null
     */
    public function findSources($unid)
    {
        return parent::findSources($unid);
    }
}
