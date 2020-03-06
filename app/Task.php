<?php

namespace App;

use App\Services\Task\TaskService;
use Illuminate\Database\Eloquent\Model;
use App\Project;
use App\Product;
use App\User;
use App\TaskStatus;
use App\Customer;
use App\Comment;
use App\Libraries\Utils;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{

    use SoftDeletes;

    const TASK_TYPE_DEAL = 3;

    protected $fillable = [
        'title',
        'content',
        'is_completed',
        'due_date',
        'start_date',
        'project_id',
        'task_status',
        'created_by',
        'task_type',
        'customer_id',
        'rating',
        'valued_at',
        'parent_id',
        'source_type',
        'time_log',
        'is_running',
        'account_id',
        'custom_value1',
        'custom_value2',
    ];

    protected $casts = [
        'updated_at' => 'timestamp',
    ];

    public function projects()
    {
        return $this->belongsTo(Project::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function taskStatus()
    {
        return $this->belongsTo(TaskStatus::class, 'task_status');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function comments()
    {
        return $this->belongsToMany(Comment::class);
    }

    public function documents()
    {
        return $this->morphMany(File::class, 'documentable');
    }

    /**
     * @param $task
     *
     * @return string
     */
    public static function calcStartTime($task)
    {
        $parts = json_decode($task->time_log) ?: [];

        if (count($parts)) {
            return Utils::timestampToDateTimeString($parts[0][0]);
        } else {
            return '';
        }
    }

    /**
     * @param $task
     *
     * @return int
     */
    public static function calcDuration($task, $startTimeCutoff = 0, $endTimeCutoff = 0)
    {
        $duration = 0;
        $parts = json_decode($task->time_log) ?: [];

        foreach ($parts as $part) {
            $startTime = $part[0];
            if (count($part) == 1 || !$part[1]) {
                $endTime = time();
            } else {
                $endTime = $part[1];
            }
            if ($startTimeCutoff) {
                $startTime = max($startTime, $startTimeCutoff);
            }
            if ($endTimeCutoff) {
                $endTime = min($endTime, $endTimeCutoff);
            }
            $duration += max($endTime - $startTime, 0);
        }
        return round($duration);
    }

    /**
     * @return int
     */
    public function getDuration($startTimeCutoff = 0, $endTimeCutoff = 0)
    {
        return self::calcDuration($this, $startTimeCutoff, $endTimeCutoff);
    }

    public function service(): TaskService
    {
        return new TaskService($this);
    }

    /**
     * @return bool
     */
    public function hasPreviousDuration()
    {
        $parts = json_decode($this->time_log) ?: [];

        return count($parts) && (count($parts[0]) && $parts[0][1]);
    }

    /**
     * @return string
     */
    public function getStartTime()
    {
        return self::calcStartTime($this);
    }
}
