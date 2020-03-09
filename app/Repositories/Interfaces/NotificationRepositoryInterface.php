<?php

namespace App\Repositories\Interfaces;

use App\Notification;
use Illuminate\Support\Collection;

interface NotificationRepositoryInterface
{
    /**
     *
     * @param type $columns
     * @param string $orderBy
     * @param string $sortBy
     */
    public function listNotifications($columns = array('*'),
        string $orderBy = 'id',
        string $sortBy = 'asc'): Collection;
}
