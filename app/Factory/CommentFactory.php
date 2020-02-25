<?php

namespace App\Factory;

use App\ClientContact;
use App\Comment;
use Illuminate\Support\Str;

class CommentFactory
{
    /**
     * @param int $account_id
     * @param int $user_id
     * @return Comment
     */
    public static function create(int $user_id, int $account_id): Comment
    {
        $comment = new Comment;
        $comment->comment = "";
        $comment->user_id = $user_id;
        $comment->account_id = $account_id;

        return $comment;
    }
}
