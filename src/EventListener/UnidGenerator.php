<?php

namespace App\EventListener;

use App\Entity\Celebrity;
use App\Entity\Representative;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\DBAL\Connection;

class UnidGenerator implements EventSubscriber
{

    protected $unids = array();
    protected $connection;

    /**
     * UnidGenerator constructor.
     * @param $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    public function getSubscribedEvents()
    {
        return array(
            'prePersist'
        );
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Representative || $entity instanceof Celebrity) {
            if (!$entity->getUnid()) {
                $this->connection->insert('unids', []);
                $lastId = $this->connection->lastInsertId();
                $entity->setUnid($lastId);
            }
        }
    }
}
