<?php

namespace App\Http\Controllers;

use App\Event;
use App\Factory\EventFactory;
use App\Filters\EventFilter;
use App\Notifications\EventCreated;
use App\Requests\SearchRequest;
use Exception;
use Illuminate\Http\Request;
use App\Requests\Event\CreateEventRequest;
use App\Requests\Event\UpdateEventRequest;
use App\Repositories\Interfaces\EventRepositoryInterface;
use App\Transformations\EventTransformable;
use App\Repositories\TaskRepository;
use App\Task;
use App\Repositories\UserRepository;
use App\User;
use App\Repositories\EventTypeRepository;
use App\EventType;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;

class EventController extends Controller
{

    use EventTransformable;

    private $event_repo;

    /**
     * EventController constructor.
     * @param EventRepositoryInterface $event_repo
     */
    public function __construct(EventRepositoryInterface $event_repo)
    {
        $this->event_repo = $event_repo;
    }

    public function index(SearchRequest $request)
    {
        $events = (new EventFilter($this->event_repo))->filter($request, auth()->user()->account_user()->account_id);
        return collect($events)->toJson();
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show(int $id)
    {
        $event = $this->event_repo->findEventById($id);
        return response()->json($this->transformEvent($event));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(CreateEventRequest $request)
    {
        $event = $this->event_repo->save($request->all(),
            (new EventFactory())->create(auth()->user()->id, auth()->user()->account_user()->account_id));
        Notification::send(auth()->user(), new EventCreated($event));
        return $event->toJson();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function archive(int $id)
    {
        $objEvent = $this->event_repo->findEventById($id);
        $response = $objEvent->delete();

        if ($response) {
            return response()->json('Event deleted!');
        }
        return response()->json('Unable to delete event!');
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function destroy(int $id)
    {
        $tax_rate = Event::withTrashed()->where('id', '=', $id)->first();
        $this->event_repo->newDelete($tax_rate);
        return response()->json([], 200);
    }

    /**
     * @param UpdateEventRequest $request
     * @param int $id
     *
     * @return Response
     */
    public function update(UpdateEventRequest $request, int $id)
    {
        $event = $this->event_repo->findEventById($id);
        $event = $this->event_repo->save($request->all(), $event);

        return response()->json($event);
    }

    /**
     * @param int $task_id
     * @return mixed
     * @throws Exception
     */
    public function getEventsForTask(int $task_id)
    {

        $objTask = (new TaskRepository(new Task))->findTaskById($task_id);
        $events = $this->event_repo->getEventsForTask($objTask);
        return $events->toJson();
    }

    /**
     * @param int $user_id
     * @return mixed
     * @throws Exception
     */
    public function getEventsForUser(int $user_id)
    {
        $objTask = (new UserRepository(new User))->findUserById($user_id);
        $events = $this->event_repo->getEventsForUser($objTask, auth()->user()->account_user()->account_id);
        return $events->toJson();
    }

    public function getEventTypes()
    {
        $eventTypes = (new EventTypeRepository(new EventType))->getAll();
        return response()->json($eventTypes);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function filterEvents(Request $request)
    {
        $events = (new EventFilter($this->event_repo))->filterBySearchCriteria($request->all(),
            auth()->user()->account_user()->account_id);
        return response()->json($events);
    }

    /**
     * @param int $id
     * @param Request $request
     */
    public function updateEventStatus(int $id, Request $request)
    {
        $user = auth()->user();
        $event = $this->event_repo->findEventById($id);
        $this->event_repo->updateInvitationResponseForUser($event, $user, $request->all());
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id)
    {
        $invoice = Event::withTrashed()->where('id', '=', $id)->first();
        $this->event_repo->restore($invoice);
        return response()->json([], 200);
    }

}
