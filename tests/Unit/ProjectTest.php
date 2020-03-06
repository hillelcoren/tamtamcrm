<?php

namespace Tests\Unit;

use App\Customer;
use App\Factory\ProjectFactory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Project;
use App\Repositories\ProjectRepository;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;

class ProjectTest extends TestCase
{

    use DatabaseTransactions, WithFaker;

    /**
     * @var int
     */
    private $account_id = 1;

    private $user;

    private $customer;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
        $this->user = factory(User::class)->create();
        $this->customer = factory(Customer::class)->create();
    }

    /** @test */
    public function it_can_show_all_the_projects()
    {
        $insertedproject = factory(Project::class)->create();
        $projectRepo = new ProjectRepository(new Project);
        $list = $projectRepo->listProjects()->toArray();
        $myLastElement = end($list);
        // $this->assertInstanceOf(Collection::class, $list);
        $this->assertEquals($insertedproject->toArray(), $myLastElement);
    }

    /** @test */
    public function it_can_delete_the_project()
    {
        $project = factory(Project::class)->create();
        $projectRepo = new ProjectRepository($project);
        $deleted = $projectRepo->newDelete($project);
        $this->assertTrue($deleted);
    }

    /** @test */
    public function it_can_update_the_project()
    {
        $project = factory(Project::class)->create();
        $title = $this->faker->word;
        $data = ['title' => $title];
        $projectRepo = new ProjectRepository($project);
        $updated = $projectRepo->save($data, $project);
        $found = $projectRepo->findProjectById($project->id);
        $this->assertInstanceOf(Project::class, $updated);
        $this->assertEquals($data['title'], $found->title);
    }

    /** @test */
    public function it_can_show_the_project()
    {
        $project = factory(Project::class)->create();
        $projectRepo = new ProjectRepository(new Project);
        $found = $projectRepo->findProjectById($project->id);
        $this->assertInstanceOf(Project::class, $found);
        $this->assertEquals($project->name, $found->name);
    }

    /** @test */
    public function it_can_create_a_project()
    {
        $data = [
            'account_id' => $this->account_id,
            'user_id' => $this->user->id,
            'title' => $this->faker->word,
            'description' => $this->faker->sentence,
            'is_completed' => 0,
        ];

        $projectRepo = new ProjectRepository(new Project);
        $factory = (new ProjectFactory())->create($this->user->id, $this->customer->id, $this->account_id);
        $project = $projectRepo->save($data, $factory);
        $this->assertInstanceOf(Project::class, $project);
        $this->assertEquals($data['title'], $project->title);
    }

    /**
     * @codeCoverageIgnore
     */
    public function it_errors_creating_the_project_when_required_fields_are_not_passed()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        $product = new ProjectRepository(new Project);
        $product->createProject([]);
    }

    /** @test */
    public function it_errors_finding_a_project()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $category = new ProjectRepository(new Project);
        $category->findProjectById(999);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

}
