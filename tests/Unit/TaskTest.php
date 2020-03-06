<?php

namespace Tests\Unit;

use App\Factory\TaskFactory;
use App\Filters\TaskFilter;
use App\Project;
use App\Repositories\ProjectRepository;
use App\Requests\SearchRequest;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Task;
use App\User;
use App\Product;
use App\Customer;
use App\Repositories\TaskRepository;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Transformations\TaskTransformable;

class TaskTest extends TestCase
{

    use DatabaseTransactions, WithFaker, TaskTransformable;

    private $user;
    private $customer;
    private $account_id = 1;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
        $this->user = factory(User::class)->create();
        $this->customer = factory(Customer::class)->create();
    }

    /** @test */
    public function it_can_show_all_the_tasks()
    {
        $insertedtask = factory(Task::class)->create();
        $list = (new TaskFilter(new TaskRepository(new Task,
            new ProjectRepository(new Project))))->filter(new SearchRequest(), 1);
        $this->assertNotEmpty($list);
        $this->assertInstanceOf(Task::class, $list[0]);
        // $this->assertInstanceOf(Collection::class, $list);
        //$this->assertEquals($insertedtask->title, $myLastElement['title']);
    }

    /** @test */
    public function it_can_delete_the_task()
    {
        $task = factory(Task::class)->create();
        $taskRepo = new TaskRepository($task, new ProjectRepository(new Project));
        $deleted = $taskRepo->newDelete($task);
        $this->assertTrue($deleted);
    }

    public function it_can_archive_the_task()
    {
        $task = factory(Task::class)->create();
        $taskRepo = new TaskRepository($task, new ProjectRepository(new Project));
        $deleted = $taskRepo->archive($task);
        $this->assertTrue($deleted);
    }

    /** @test */
    public function it_can_update_the_task()
    {
        $task = factory(Task::class)->create();
        $title = $this->faker->word;
        $data = ['title' => $title];
        $taskRepo = new TaskRepository($task, new ProjectRepository(new Project));
        $task = $taskRepo->save($data, $task);
        $found = $taskRepo->findTaskById($task->id);
        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($data['title'], $found->title);
    }

    /** @test */
    public function it_can_show_the_task()
    {
        $task = factory(Task::class)->create();
        $taskRepo = new TaskRepository(new Task, new ProjectRepository(new Project));
        $found = $taskRepo->findTaskById($task->id);
        $this->assertInstanceOf(Task::class, $found);
        $this->assertEquals($task->name, $found->name);
    }

    /** @test */
    public function it_can_attach_a_user()
    {
        $user = factory(User::class)->create();
        $task = factory(Task::class)->create();
        $taskRepo = new TaskRepository($task, new ProjectRepository(new Project));
        $result = $taskRepo->syncUsers($task, [$user->id]);
        $this->assertArrayHasKey('attached', $result);
    }

    /** @test */
    public function it_can_create_a_task()
    {

        $data = [
            'account_id' => $this->account_id,
            'task_type' => 1,
            'task_status' => 1,
            'customer_id' => $this->customer->id,
            'title' => $this->faker->word,
            'content' => $this->faker->sentence,
            'is_completed' => 0,
            'due_date' => $this->faker->dateTime,
        ];

        $taskRepo = new TaskRepository(new Task, new ProjectRepository(new Project));
        $factory = (new TaskFactory())->create($this->user->id, $this->account_id);
        $task = $taskRepo->save($data, $factory);
        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($data['title'], $task->title);
    }

    /** @test */
    public function it_can_create_a_project_task()
    {

        $project = factory(Project::class)->create();

        $data = [
            'project_id' => $project->id,
            'account_id' => $this->account_id,
            'task_type' => 1,
            'task_status' => 1,
            'customer_id' => $this->customer->id,
            'title' => $this->faker->word,
            'content' => $this->faker->sentence,
            'is_completed' => 0,
            'due_date' => $this->faker->dateTime,
        ];

        $taskRepo = new TaskRepository(new Task, new ProjectRepository(new Project));
        $factory = (new TaskFactory())->create($this->user->id, $this->account_id);
        $task = $taskRepo->save($data, $factory);
        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals($data['title'], $task->title);
        $this->assertEquals($data['project_id'], $task->project_id);
    }

    /**
     * @codeCoverageIgnore
     */
    public function it_errors_creating_the_task_when_required_fields_are_not_passed()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        $task = new TaskRepository(new Task, new ProjectRepository(new Project));
        $task->createTask([]);
    }

    /** @test */
    public function it_errors_finding_a_task()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $task = new TaskRepository(new Task, new ProjectRepository(new Project));
        $task->findTaskById(999);
    }

    /** @test */
    public function it_can_transform_task()
    {
        $customer = factory(Customer::class)->create();

        $title = $this->faker->title;
        $content = $this->faker->sentence;
        $due_date = $this->faker->dateTime;
        $task_type = 2;

        $address = factory(Task::class)->create([
            'account_id' => $this->account_id,
            'title' => $title,
            'content' => $content,
            'due_date' => $due_date,
            'task_type' => $task_type
        ]);

        $transformed = $this->transformTask($address);

        $this->assertEquals($title, $transformed->title);
        $this->assertEquals($content, $transformed->content);
        $this->assertEquals($due_date, $transformed->due_date);
        $this->assertEquals($task_type, $transformed->task_type);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

}
