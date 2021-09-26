<?php

namespace App\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;


class LogCreator implements EventSubscriber
{

    protected $logEntries = [];
    protected $emails = [];
    protected $links = [];
    protected $phones = [];
    protected $reps = [];

    public function getSubscribedEvents()
    {
        return array(
            'onFlush',
            'postFlush'
        );
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     * @throws \Doctrine\ORM\ORMException
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
    }

    /**
     * @param PostFlushEventArgs $args
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (!empty($this->logEntries)) {
            $em = $args->getEntityManager();
            foreach ($this->logEntries as $obj) {
                $em->persist($obj);
            }
            $this->logEntries = [];
            $em->flush();
        }
    }
}
