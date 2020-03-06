<?php

namespace Tests\Unit;

use App\Factory\LeadFactory;
use App\Filters\LeadFilter;
use App\Lead;
use App\Repositories\LeadRepository;
use App\Repositories\ProjectRepository;
use App\Requests\SearchRequest;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Task;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Transformations\TaskTransformable;

class LeadTest extends TestCase
{

    use DatabaseTransactions, WithFaker, TaskTransformable;

    private $user;
    private $account_id = 1;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function it_can_show_all_the_leads()
    {
        //$insertedtask = factory(Lead::class)->create();
        $list = (new LeadFilter(new LeadRepository(new Lead)))->filter(new SearchRequest(), 1);
        $this->assertNotEmpty($list);
        $this->assertInstanceOf(Lead::class, $list[0]);
        // $this->assertInstanceOf(Collection::class, $list);
        //$this->assertEquals($insertedtask->title, $myLastElement['title']);
    }

    /** @test */
    public function it_can_delete_the_lead()
    {
        $lead = factory(Lead::class)->create();
        $taskRepo = new LeadRepository($lead);
        $deleted = $taskRepo->newDelete($lead);
        $this->assertTrue($deleted);
    }

    public function it_can_archive_the_lead()
    {
        $lead = factory(Lead::class)->create();
        $taskRepo = new LeadRepository($lead);
        $deleted = $taskRepo->archive($lead);
        $this->assertTrue($deleted);
    }

    /** @test */
    public function it_can_update_the_lead()
    {
        $lead = factory(Lead::class)->create();
        $title = $this->faker->word;
        $data = ['first_name' => $this->faker->firstName];
        $leadRepo = new LeadRepository($lead);
        $task = $leadRepo->save($lead, $data);
        $found = $leadRepo->findLeadById($lead->id);
        $this->assertInstanceOf(Lead::class, $lead);
        $this->assertEquals($data['first_name'], $found->first_name);
    }

    /** @test */
    public function it_can_show_the_lead()
    {
        $lead = factory(Lead::class)->create();
        $leadRepo = new LeadRepository(new Lead);
        $found = $leadRepo->findLeadById($lead->id);
        $this->assertInstanceOf(Lead::class, $found);
        $this->assertEquals($lead->first_name, $found->first_name);
    }


    /** @test */
    public function it_can_create_a_lead()
    {

        $data = [
            'account_id' => $this->account_id,
            'user_id' => $this->user->id,
            'task_status' => 1,
            'title' => $this->faker->word,
            'description' => $this->faker->sentence,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->safeEmail
        ];

        $leadRepo = new LeadRepository(new Lead);
        $factory = (new LeadFactory)->create($this->user->id, $this->account_id);
        $lead = $leadRepo->save($factory, $data);
        $this->assertInstanceOf(Lead::class, $lead);
        $this->assertEquals($data['first_name'], $lead->first_name);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

}
