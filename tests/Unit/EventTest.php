<?php

namespace Tests\Unit;

use App\Event;
use App\Customer;
use App\Factory\EventFactory;
use App\Task;
use App\User;
use App\Repositories\EventRepository;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Transformations\EventTransformable;
use Illuminate\Foundation\Testing\WithFaker;

class EventTest extends TestCase
{

    use DatabaseTransactions, EventTransformable, WithFaker;

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
    public function it_can_transform_the_event()
    {
        $event = factory(Event::class)->create();
        $repo = new EventRepository($event);
        $eventFromDb = $repo->findEventById($event->id);
        $cust = $this->transformEvent($event);
        //$this->assertInternalType('string', $eventFromDb->status);
        $this->assertInternalType('string', $cust->title);
    }

    /** @test */
    public function it_can_delete_a_event()
    {
        $event = factory(Event::class)->create();
        $eventRepo = new EventRepository($event);
        $delete = $eventRepo->deleteEvent();
        $this->assertTrue($delete);
        //$this->assertDatabaseHas('events', $event->toArray());
    }

    /** @test */
    public function it_fails_when_the_event_is_not_found()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $event = new EventRepository(new Event);
        $event->findEventById(999);
    }

    /** @test */
    public function it_can_find_a_event()
    {
        $data = [
            'account_id' => $this->account_id,
            'customer_id' => $this->customer->id,
            'title' => $this->faker->sentence,
            'location' => $this->faker->sentence,
            'beginDate' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
            'endDate' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
        ];


        $eventRepo = new EventRepository(new Event);
        $eventFactory = (new EventFactory())->create($this->user->id, $this->account_id);
        $created = $eventRepo->save($data, $eventFactory);
        $found = $eventRepo->findEventById($created->id);
        $this->assertInstanceOf(Event::class, $found);
        $this->assertEquals($data['title'], $found->title);
        $this->assertEquals($data['location'], $found->location);
    }

    /** @test */
    public function it_can_update_the_event()
    {
        $cust = factory(Event::class)->create();
        $event = new EventRepository($cust);
        $update = [
            'location' => $this->faker->sentence,
        ];
        $updated = $event->save($update, $cust);
        $this->assertInstanceOf(Event::class, $updated);
        $this->assertEquals($update['location'], $cust->location);
        $this->assertDatabaseHas('events', $update);
    }

    /** @test */
    public function it_can_create_a_event()
    {
        $factory = (new EventFactory())->create($this->user->id, $this->account_id);
        $data = [
            'account_id' => $this->account_id,
            'title' => $this->faker->word,
            'description' => $this->faker->sentence,
            'location' => $this->faker->sentence,
            'beginDate' => $this->faker->dateTime(),
            'endDate' => $this->faker->dateTime(),
            'customer_id' => $this->customer->id,
            'event_type' => 2
        ];

        $event = new EventRepository(new Event);
        $created = $event->save($data, $factory);
        $this->assertInstanceOf(Event::class, $created);
        $this->assertEquals($data['title'], $created->title);
        $this->assertEquals($data['location'], $created->location);
        $collection = collect($data);
        $this->assertDatabaseHas('events', $collection->all());
    }

    public function it_errors_creating_the_event_when_required_fields_are_not_passed()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        $task = new EventRepository(new Event);
        $task->createEvent([]);
    }

    /** @test */
    public function it_can_attach_a_task()
    {
        $task = factory(Task::class)->create();
        $event = factory(Event::class)->create();
        $eventRepo = new EventRepository($event);
        $result = $eventRepo->syncTask($event, $task->id);
        $this->assertArrayHasKey('attached', $result);
    }

    /** @test */
    public function it_can_attach_a_user()
    {
        $user = factory(User::class)->create();
        $event = factory(Event::class)->create();
        $eventRepo = new EventRepository($event);
        $result = $eventRepo->attachUsers($event, [$user->id]);
        $this->assertTrue($result);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

}
