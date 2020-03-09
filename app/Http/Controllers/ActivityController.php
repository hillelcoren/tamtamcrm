<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\CommentRepositoryInterface;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Repositories\Interfaces\EventRepositoryInterface;
use App\Notification;
use App\Transformations\NotificationTransformable;
use App\Transformations\EventTransformable;

;

use Illuminate\Support\Facades\Auth;
use App\Event;

class ActivityController extends Controller
{

    use NotificationTransformable, EventTransformable;

    /**
     * @var CommentRepositoryInterface
     */
    private $comment_repo;

    /**
     * @var NotificationRepositoryInterface
     */
    private $notification_repo;

    /**
     * @var EventRepositoryInterface
     */
    private $event_repo;

    /**
     * ActivityController constructor.
     *
     * @param CommentRepositoryInterface $commentRepository
     * NotificationRepositoryInterface $notificationRepository
     */
    public function __construct(CommentRepositoryInterface $comment_repo,
        NotificationRepositoryInterface $notification_repo,
        EventRepositoryInterface $event_repo)
    {
        $this->comment_repo = $comment_repo;
        $this->notification_repo = $notification_repo;
        $this->event_repo = $event_repo;
    }

    public function index()
    {
        $currentUser = auth()->user();
        $comments = $this->comment_repo->getCommentsForActivityFeed(auth()->user()->account_user()->account_id);
        $list = $this->notification_repo->listNotifications();
        $userEvents = $this->event_repo->getEventsForUser($currentUser, auth()->user()->account_user()->account_id);

        $events = $userEvents->map(function (Event $event) {
            return $this->transformEvent($event);
        })->all();

        $notifications = $list->map(function (Notification $notification) {
            return $this->transformNotification($notification);
        })->all();

        return response()->json([
            'notifications' => $notifications,
            'comments' => $comments,
            'events' => $events
        ]);
    }

}
