<?php

namespace App\Http\Controllers;

use App\Factory\CommentFactory;
use App\Requests\CommentRequest;
use App\Repositories\Interfaces\CommentRepositoryInterface;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CommentCreated;
use App\Repositories\CommentRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{

    /**
     * @var CommentRepositoryInterface
     */
    private $comment_repo;

    /**
     * @var TaskRepositoryInterface
     */
    private $task_repo;

    /**
     * CommentController constructor.
     *
     * @param CommentRepositoryInterface $commentRepository
     * TaskRepositoryInterface $taskRepository
     */
    public function __construct(CommentRepositoryInterface $comment_repo, TaskRepositoryInterface $task_repo)
    {
        $this->comment_repo = $comment_repo;
        $this->task_repo = $task_repo;
    }

    public function index($task_id)
    {
        $objTask = $this->task_repo->findTaskById($task_id);
        $comments = $this->comment_repo->getAllCommentsForTask($objTask, auth()->user()->account_user()->account_id);
        return $comments->toJson();
    }

    /**
     *
     * @param CommentRequest $request
     * @return type
     */
    public function store(CommentRequest $request)
    {
        $validatedData = $request->validated();

        $user = Auth::user();

        $data = [
            'parent_id' => !empty($validatedData['parent_id']) ? $validatedData['parent_id'] : 0,
            'comment' => $validatedData['comment'],
            'parent_type' => (int)!empty($validatedData['task_id']) ? 1 : 2,
            'user_id' => $user->id
        ];

        $comment = $this->comment_repo->save($data,
            CommentFactory::create(auth()->user()->id, auth()->user()->account_user()->account_id));

        if (!empty($validatedData['task_id'])) {
            $task = $this->task_repo->findTaskById($validatedData['task_id']);
            $task->comments()->attach($comment);
        }

        $arrResponse[0] = $comment;
        $arrResponse[0]['user'] = $user->toArray();

        //send notification

        Notification::send($user, new CommentCreated($comment));

        return collect($arrResponse)->toJson();
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(int $id)
    {
        $comment = $this->comment_repo->findCommentById($id);
        $commentRepo = new CommentRepository($comment);
        $commentRepo->deleteComment();

        return response()->json('Comment deleted!');
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(Request $request, $id)
    {
        $comment = $this->comment_repo->findCommentById($id);
        $update = new CommentRepository($comment);
        $update->updateComment($request->all());
        return response()->json('Comment updated!');
    }

}
