<?php

namespace App\Repositories;

use App\Event;
use App\Notification;
use App\Repositories\Interfaces\EventRepositoryInterface;
use App\Repositories\Base\BaseRepository;
use Exception;
use Illuminate\Support\Collection;
use App\Repositories\UserRepository;
use App\User;
use App\Task;

class EventRepository extends BaseRepository implements EventRepositoryInterface
{

    /**
     * EventRepository constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        parent::__construct($event);
        $this->model = $event;
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param int $id
     *
     * @return User
     * @throws Exception
     */
    public function findEventById(int $id): Event
    {
        return $this->findOneOrFail($id);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function deleteEvent(): bool
    {
        $result = $this->delete();
        $this->model->users()->detach();
        return $result;
    }

    /**
     * @param array $columns
     * @param string $orderBy
     * @param string $sortBy
     *
     * @return Collection
     */
    public function listEvents($columns = array('*'), string $orderBy = 'id', string $sortBy = 'asc'): Collection
    {
        return $this->all($columns, $orderBy, $sortBy);
    }

    /**
     *
     * @param Event $objEvent
     * @param array $arrUsers
     */
    public function attachUsers(Event $objEvent, array $arrUsers)
    {

        $objEvent->users()->detach();

        foreach ($arrUsers as $userId) {
            $objUser = (new UserRepository(new User))->findUserById($userId);
            $objEvent->users()->attach($objUser);
        }

        return true;
    }

    /**
     * Sync the categories
     *
     * @param array $params
     */
    public function syncTask(Event $event, int $task_id)
    {
        return $event->tasks()->sync($task_id);
    }

    /**
     *
     * @param Task $objTask
     * @return Collection
     */
    public function getEventsForTask(Task $objTask): Collection
    {

        return $this->model->join('event_task', 'event_task.event_id', '=', 'events.id')->select('events.*')
                           ->where('event_task.task_id', $objTask->id)->groupBy('events.id')->get();
    }

    /**
     *
     * @param User $objUser
     * @return Collection
     */
    public function getEventsForUser(User $objUser, int $account_id): Collection
    {
        return $this->model->join('event_user', 'event_user.event_id', '=', 'events.id')
                           ->select('events.*', 'event_user.status')->where('events.account_id', $account_id)
                           ->where('event_user.user_id', $objUser->id)->get();
    }

    /**
     * @param Event $event
     * @param User $objUser
     * @param $status
     */
    public function updateInvitationResponseForUser(Event $event, User $objUser, $status)
    {
        $event->users()->updateExistingPivot($objUser->id, $status);
    }

    /**
     * @param array $data
     * @param Event $event
     */
    public function save(array $data, Event $event): ?Event
    {
        $event->fill($data);
        $event->save();

        if (isset($data['users']) && !empty($data['users'])) {
            //attach invited users
            $this->attachUsers($event, $data['users']);
        }


        if (!empty($data['task_id'])) {
            $this->syncTask($event, $data['task_id']);
        }

        return $event->fresh();

    }
}
