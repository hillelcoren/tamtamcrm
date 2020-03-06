<?php

namespace Tests\Unit;

use App\Company;
use App\Expense;
use App\Factory\ExpenseFactory;
use App\Filters\ExpenseFilter;
use App\Filters\QuoteFilter;
use App\Repositories\ExpenseRepository;
use App\Requests\SearchRequest;
use Tests\TestCase;
use App\Quote;
use App\User;
use App\Customer;
use App\Repositories\QuoteRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use App\Factory\QuoteFactory;
use App\Traits\GeneratesCounter;

class ExpenseTest extends TestCase
{
    use DatabaseTransactions, WithFaker, GeneratesCounter;

    private $customer;

    private $user;

    private $company;

    /**
     * @var int
     */
    private $account_id = 1;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
        $this->customer = factory(Customer::class)->create();
        $this->user = factory(User::class)->create();
        $this->company = factory(Company::class)->create();
    }

    public function it_can_show_all_the_expenses()
    {
        factory(Expense::class)->create();
        $list = (new ExpenseFilter(new ExpenseRepository(new Expense)))->filter(new SearchRequest(), 1);
        $this->assertNotEmpty($list);
    }

    /** @test */
    public function it_can_update_the_expense()
    {
        $expense = factory(Expense::class)->create();
        $customer_id = $this->customer->id;
        $data = ['customer_id' => $customer_id];
        $expenseRepo = new ExpenseRepository($expense);
        $updated = $expenseRepo->save($data, $expense);
        $found = $expenseRepo->findExpenseById($expense->id);
        $this->assertInstanceOf(Expense::class, $updated);
        $this->assertEquals($data['customer_id'], $found->customer_id);
    }

    /** @test */
    public function it_can_show_the_expense()
    {
        $expense = factory(Expense::class)->create();
        $quoteRepo = new ExpenseRepository(new Expense);
        $found = $quoteRepo->findExpenseById($expense->id);
        $this->assertInstanceOf(Expense::class, $found);
        $this->assertEquals($expense->customer_id, $found->customer_id);
    }

    /** @test */
    public function it_can_create_a_expense()
    {

        $factory = (new ExpenseFactory)->create(1, $this->user->id);

        $data = [
            'account_id' => 1,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'amount' => $this->faker->randomFloat(),
            'company_id' => $this->company->id
        ];

        $expenseRepo = new ExpenseRepository(new Expense);
        $expense = $expenseRepo->save($data, $factory);
        $this->assertInstanceOf(Expense::class, $expense);
        $this->assertEquals($data['customer_id'], $expense->customer_id);
    }
}
