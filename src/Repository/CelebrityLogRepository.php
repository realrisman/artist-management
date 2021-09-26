<?php

namespace App\Repository;

use App\Entity\CelebrityLog;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CelebrityLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method CelebrityLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method CelebrityLog[]    findAll()
 * @method CelebrityLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CelebrityLogRepository  extends CoreLogRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CelebrityLog::class);
    }

    /**
     * @return CelebrityLog[]
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
     * @return CelebrityLog[]|null
     */
    public function findSources($unid)
    {
        return parent::findSources($unid);
    }
}
