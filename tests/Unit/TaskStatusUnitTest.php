<?php

namespace Tests\Unit;

use App\Shop\Orders\Order;
use App\TaskStatus;
use App\Repositories\TaskStatusRepository;
use Tests\TestCase;
use App\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;

class TaskStatusUnitTest extends TestCase
{

    use DatabaseTransactions, WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
    }

    /** @test */
    public function it_can_delete_the_order_status()
    {
        $os = factory(TaskStatus::class)->create();
        $taskStatusRepo = new TaskStatusRepository($os);
        $taskStatusRepo->deleteTaskStatus($os);
        $this->assertDatabaseMissing('task_statuses', $os->toArray());
    }

    /** @test */
    public function it_lists_all_the_task_statuses()
    {
        $create = [
            'title' => $this->faker->name,
            'column_color' => $this->faker->word
        ];
        $taskStatusRepo = new TaskStatusRepository(new TaskStatus);
        $taskStatusRepo->createTaskStatus($create);
        $taskStatusRepo = new TaskStatusRepository(new TaskStatus);
        $lists = $taskStatusRepo->listTaskStatuses();
        foreach ($lists as $list) {
            $this->assertDatabaseHas('task_statuses', ['title' => $list->title]);
            $this->assertDatabaseHas('task_statuses', ['column_color' => $list->column_color]);
        }
    }

    /** @test */
    public function it_errors_getting_not_existing_order_status()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $taskStatusRepo = new TaskStatusRepository(new TaskStatus);
        $taskStatusRepo->findTaskStatusById(999);
    }

    /** @test */
    public function it_can_get_the_task_status()
    {
        $create = [
            'title' => $this->faker->name,
            'column_color' => $this->faker->word
        ];
        $taskStatusRepo = new TaskStatusRepository(new TaskStatus);
        $taskStatus = $taskStatusRepo->createTaskStatus($create);
        $os = $taskStatusRepo->findTaskStatusById($taskStatus->id);
        $this->assertEquals($create['title'], $os->title);
        $this->assertEquals($create['column_color'], $os->column_color);
    }

    /** @test */
    public function it_can_update_the_task_status()
    {
        $os = factory(TaskStatus::class)->create();
        $taskStatusRepo = new TaskStatusRepository($os);
        $data = [
            'title' => $this->faker->name,
            'column_color' => $this->faker->word
        ];
        $updated = $taskStatusRepo->updateTaskStatus($data);
        $this->assertTrue($updated);
        $found = $taskStatusRepo->findTaskStatusById($os->id);
        $this->assertEquals($data['title'], $found->title);
        $this->assertEquals($data['column_color'], $found->column_color);
    }

    /** @test */
    public function it_can_create_the_task_status()
    {
        $create = [
            'title' => $this->faker->name,
            'task_type' => 1,
            'description' => $this->faker->sentence,
            'column_color' => $this->faker->word
        ];
        $taskStatusRepo = new TaskStatusRepository(new TaskStatus);
        $taskStatus = $taskStatusRepo->createTaskStatus($create);
        $this->assertEquals($create['title'], $taskStatus->title);
        $this->assertEquals($create['column_color'], $taskStatus->column_color);
    }

    public function it_errors_creating_the_task_when_required_fields_are_not_passed()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        $task = new TaskStatusRepository(new TaskStatus);
        $task->createTaskStatus([]);
    }
}
