<?php

namespace App\Repositories\Interfaces;

use App\Event;
use Illuminate\Support\Collection;
use App\Repositories\Base\BaseRepositoryInterface;
use App\Task;
use App\User;

interface EventRepositoryInterface extends BaseRepositoryInterface
{
    //public function getAll(string $orderBy, string $orderDir, int $recordsPerPage, $blActive = true);

    /**
     *
     * @param type $columns
     * @param string $orderBy
     * @param string $sortBy
     */
    public function listEvents($columns = array('*'), string $orderBy = 'id', string $sortBy = 'asc'): Collection;

    /**
     *
     */
    public function deleteEvent(): bool;

    /**
     *
     * @param int $id
     */
    public function findEventById(int $id): Event;

    /**
     *
     * @param \App\Repositories\Interfaces\Task $objTask
     */
    public function getEventsForTask(Task $objTask): Collection;

    /**
     *
     * @param \App\Repositories\Interfaces\User $objUser
     */
    public function getEventsForUser(User $objUser, int $account_id): Collection;
}
