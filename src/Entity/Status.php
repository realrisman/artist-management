<?php

namespace App\Entity;


class Status
{
    public const DRAFT = 'draft';
    public const QA = 'qa';
    public const LIVE = 'live';
    public const READY = 'ready';
    public const DELETED = 'deleted';
}
