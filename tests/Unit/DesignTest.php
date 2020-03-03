<?php

namespace Tests\Unit;

use App\Account;
use App\Credit;
use App\Design;
use App\Factory\CreditFactory;
use App\Factory\DesignFactory;
use App\Filters\CreditFilter;
use App\Filters\DesignFilter;
use App\Filters\InvoiceFilter;
use App\Repositories\CreditRepository;
use App\Repositories\DesignRepository;
use App\Requests\SearchRequest;
use Tests\TestCase;
use App\Invoice;
use App\User;
use App\Customer;
use App\Repositories\InvoiceRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use App\Factory\InvoiceFactory;
use App\Traits\GeneratesCounter;

/**
 * Description of InvoiceTest
 *
 * @author michael.hampton
 */
class DesignTest extends TestCase
{

    use DatabaseTransactions, WithFaker, GeneratesCounter;

    private $customer;

    private $account;

    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
        $this->account = factory(Account::class)->create();
        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function it_can_show_all_the_designs()
    {
        factory(Design::class)->create();
        $list = (new DesignFilter(new DesignRepository(new Design())))->filter(new SearchRequest(), 1);
        $this->assertNotEmpty($list);
        $this->assertInstanceOf(Design::class, $list[0]);
    }

    /** @test */
    public function it_can_update_the_design()
    {
        $design = factory(Design::class)->create();
        $name = $this->faker->firstName;
        $data = ['name' => $name];
        $updated = $design->update($data);
        $designRepo = new DesignRepository(new Design);
        $found = $designRepo->findDesignById($design->id);
        $this->assertTrue($updated);
        $this->assertEquals($data['name'], $found->name);
    }

    /** @test */
    public function it_can_show_the_design()
    {
        $design = factory(Design::class)->create();
        $designRepo = new DesignRepository(new Design());
        $found = $designRepo->findDesignById($design->id);
        $this->assertInstanceOf(Design::class, $found);
        $this->assertEquals($design->name, $found->name);
    }

    /** @test */
    public function it_can_create_a_design()
    {

        $user = factory(User::class)->create();
        $design =
            (new DesignFactory)->create(1, $user->id);

        $name = $this->faker->firstName;


        $data = [
            'name' => $name,
            'user_id' => $user->id,
            'account_id' => 1,
            'design' => 'test'
        ];

        $designRepo = new DesignRepository(new Design);
        $design->fill($data);
        $saved = $design->save();
        $this->assertTrue($saved);
        $this->assertEquals($data['name'], $design->name);
    }
}
