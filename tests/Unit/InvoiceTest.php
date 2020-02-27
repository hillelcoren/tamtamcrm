<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Tests\Unit;

use App\Account;
use App\DataMapper\CompanySettings;
use App\DataMapper\CustomerSettings;
use App\Factory\CustomerFactory;
use App\Filters\InvoiceFilter;
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
class InvoiceTest extends TestCase
{

    use DatabaseTransactions, WithFaker, GeneratesCounter;

    private $customer;

    private $account;

    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
        $this->customer = factory(Customer::class)->create();
        $this->account = factory(Account::class)->create();
        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function it_can_show_all_the_invoices()
    {
        factory(Invoice::class)->create();
        $list = (new InvoiceFilter(new InvoiceRepository(new Invoice)))->filter(new SearchRequest(), 1);
        $this->assertNotEmpty($list);
        $this->assertInstanceOf(Invoice::class, $list[0]);
    }

    /** @test */
    public function it_can_update_the_invoice()
    {
        $invoice = factory(Invoice::class)->create();
        $customer_id = $this->customer->id;
        $data = ['customer_id' => $customer_id];
        $invoiceRepo = new InvoiceRepository($invoice);
        $updated = $invoiceRepo->save($data, $invoice);
        $found = $invoiceRepo->findInvoiceById($invoice->id);
        $this->assertInstanceOf(Invoice::class, $updated);
        $this->assertEquals($data['customer_id'], $found->customer_id);
    }

    /** @test */
    public function it_can_show_the_invoice()
    {
        $invoice = factory(Invoice::class)->create();
        $invoiceRepo = new InvoiceRepository(new Invoice);
        $found = $invoiceRepo->findInvoiceById($invoice->id);
        $this->assertInstanceOf(Invoice::class, $found);
        $this->assertEquals($invoice->customer_id, $found->customer_id);
    }

    public function testMarkInvoicePaidInvoice()
    {
        $total = $this->faker->randomFloat();
        $user = factory(User::class)->create();
        $customer = factory(Customer::class)->create();
        $factory = (new InvoiceFactory())->create($user->id, 1, $customer, $total, $customer->getMergedSettings());

        $data = [
            'account_id' => 1,
            'user_id' => $user->id,
            'customer_id' => $this->customer->id,
            'total' => 200,
            'balance' => 200,
            'tax_total' => 0,
            'discount_total' => 0,
            'status_id' => 1,
        ];

        $invoiceRepo = new InvoiceRepository(new Invoice);
        $invoice = $invoiceRepo->save($data, $factory);
        $invoice_balance = $invoice->balance;
        $client = $invoice->customer;
        $client_balance = $client->balance;
        $invoice->service()->markPaid();

        $invoice = Invoice::find($invoice->id);
        $client = $invoice->customer->fresh();

        $this->assertEquals(0, $invoice->balance);

        $this->assertEquals(1, count($invoice->payments));

        foreach ($invoice->payments as $payment) {
            $this->assertEquals(round($invoice->total, 2), $payment->amount);
        }
    }

    /** @test */
    public function it_can_create_a_invoice()
    {

        $customerId = $this->customer->id;

        $total = $this->faker->randomFloat();
        $user = factory(User::class)->create();
        $factory =
            (new InvoiceFactory())->create($user->id, 1, $this->customer, $total, $this->customer->getMergedSettings());


        $data = [
            'account_id' => 1,
            'user_id' => $user->id,
            'customer_id' => $this->customer->id,
            'total' => $this->faker->randomFloat(),
            'tax_total' => $this->faker->randomFloat(),
            'discount_total' => $this->faker->randomFloat(),
            'status_id' => 1,
        ];

        $invoiceRepo = new InvoiceRepository(new Invoice);
        $invoice = $invoiceRepo->save($data, $factory);
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals($data['customer_id'], $invoice->customer_id);
    }

    /**
     * @codeCoverageIgnore
     */
    public function it_errors_creating_the_invoice_when_required_fields_are_not_passed()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        $invoice = new InvoiceRepository(new Invoice);
        $invoice->createInvoice([]);
    }

    /** @test */
    public function it_errors_finding_a_invoice()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $invoice = new InvoiceRepository(new Invoice);
        $invoice->findInvoiceById(999);
    }

    /** @test */
    public function it_can_list_all_invoices()
    {
        factory(Invoice::class, 5)->create();
        $invoiceRepo = new InvoiceRepository(new Invoice);
        $list = $invoiceRepo->listInvoices();
        $this->assertInstanceOf(Collection::class, $list);
    }

    /* public function testInvoiceNumberValue()
    {
        $customer = factory ( Customer::class )->create ();
    	$invoice_number = $this->getNextInvoiceNumber($customer);
        $this->assertEquals($invoice_number, 0002);
    	$invoice_number = $this->getNextInvoiceNumber($customer->fresh());
        $this->assertEquals($invoice_number, 0002);
    } */

    public function testInvoiceNumberPattern()
    {
        $settings = $this->customer->account->settings;
        $settings->invoice_number_counter = 1;
        $settings->invoice_number_pattern = '{$year}-{$counter}';
        $this->customer->account->settings = $settings;
        $this->customer->account->save();
        $this->customer->settings = $settings;
        $this->customer->save();
        $this->customer->fresh();

        $invoice_number = $this->getNextInvoiceNumber($this->customer);
        $invoice_number2 = $this->getNextInvoiceNumber($this->customer);
        $this->assertEquals($invoice_number, date('Y') . '-0001');
        $this->assertEquals($invoice_number2, date('Y') . '-0002');
        $this->assertEquals($this->customer->account->settings->invoice_number_counter, 3);

    }

    public function testInvoicePadding()
    {
        $settings = CompanySettings::defaults();
        $settings->counter_padding = 5;
        $settings->invoice_number_counter = 7;
        //$this->client->settings = $settings;
        $this->account->settings = $settings;
        $this->account->save();
        $customer = factory(Customer::class)->create();
        $customerSettings = CustomerSettings::defaults();
        $customerSettings->counter_padding = 5;
        $customerSettings->invoice_number_counter = 7;
        $customer->settings = $customerSettings;
        $customer->save();

        $invoice_number = $this->getNextInvoiceNumber($customer);

        $this->assertEquals($customer->getSetting('counter_padding'), 5);
        $this->assertEquals($invoice_number, '00007');
        $this->assertEquals(strlen($invoice_number), 5);
        $settings = $this->account->settings;
        $settings->counter_padding = 10;
        $this->account->settings = $settings;
        $this->account->save();

        $customer = factory(Customer::class)->create();
        $custSettings = CustomerSettings::defaults();
        $custSettings->counter_padding = 10;
        $custSettings->invoice_number_counter = 1;
        $customer->settings = $custSettings;
        $customer->save();

        $invoice_number = $this->getNextInvoiceNumber($customer);

        $this->assertEquals($customer->getSetting('counter_padding'), 10);
        $this->assertEquals(strlen($invoice_number), 10);
        $this->assertEquals($invoice_number, '0000000001');
    }

    /* public function testInvoicePrefix()
    {
        $settings = CompanySettings::defaults();
        $this->account->settings = $settings;
        $this->account->save();
        $customer = factory ( Customer::class )->create ();
        $settings = CustomerSettings::defaults();
        $settings->invoice_number_counter = 1;
        $customer->settings = $settings;
        $customer->save();
        $invoice_number = $this->getNextInvoiceNumber($customer);
        $this->assertEquals($invoice_number, '000002');
    } */
}
