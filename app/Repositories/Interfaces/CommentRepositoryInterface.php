<?php

namespace App\Repositories\Interfaces;

use App\Comment;
use App\Task;
use Illuminate\Support\Collection;

interface CommentRepositoryInterface
{
    /**
     *
     * @param Task $objTask
     */
    public function getAllCommentsForTask(Task $objTask, int $account_id);

    /**
     *
     * @param array $data
     */
    public function createComment(array $data): Comment;

    /**
     *
     * @param int $id
     */
    public function findCommentById(int $id): Comment;

    /**
     *
     */
    public function deleteComment(): bool;

    /**
     *
     * @param type $columns
     * @param string $orderBy
     * @param string $sortBy
     */
    public function listComments($columns = array('*'), string $orderBy = 'id', string $sortBy = 'asc'): Collection;

    /**
     *
     */
    public function getCommentsForActivityFeed(int $account_id): Collection;

}
