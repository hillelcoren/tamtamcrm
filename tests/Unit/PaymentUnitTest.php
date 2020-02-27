<?php

namespace Tests\Unit;

use App\Factory\CustomerFactory;
use App\Factory\InvoiceFactory;
use App\Filters\PaymentFilter;
use App\Helpers\Invoice\InvoiceSum;
use App\Invoice;
use App\Payment;
use App\Customer;
use App\Requests\SearchRequest;
use App\User;
use App\Repositories\PaymentRepository;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Transformations\EventTransformable;
use Illuminate\Foundation\Testing\WithFaker;
use App\Factory\PaymentFactory;

class PaymentUnitTest extends TestCase
{

    use DatabaseTransactions, EventTransformable, WithFaker;

    private $user;

    /**
     * @var int
     */
    private $account_id = 1;

    private $customer;

    public function setUp(): void
    {
        parent::setUp();
        $this->beginDatabaseTransaction();
        $this->user = factory(User::class)->create();
        $this->customer = factory(Customer::class)->create();
    }

    /** @test */
    public function it_can_list_all_the_payments()
    {

        $data = [
            'user_id' => $this->user->id,
            'type_id' => 1,
            'amount' => $this->faker->randomFloat()
        ];

        $factory = (new PaymentFactory())->create($this->customer->id, $this->user->id, $this->account_id);

        $paymentRepo = new PaymentRepository(new Payment);
        $paymentRepo->save($data, $factory);
        $lists = (new PaymentFilter(new PaymentRepository(new Payment)))->filter(new SearchRequest, $this->account_id);
        $this->assertInstanceOf(Payment::class, $lists[0]);
    }

    /** @test */
    public function it_errors_when_the_payments_is_not_found()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $paymentRepo = new PaymentRepository(new Payment);
        $paymentRepo->findPaymentById(999);
    }

    /** @test */
    public function it_can_get_the_payments()
    {

        $data = [
            'customer_id' => $this->customer->id,
            'type_id' => 1,
            'amount' => $this->faker->randomFloat()
        ];

        $paymentRepo = new PaymentRepository(new Payment);
        $factory = (new PaymentFactory())->create($this->customer->id, $this->user->id, $this->account_id);
        $created = $paymentRepo->save($data, $factory);
        $found = $paymentRepo->findPaymentById($created->id);
        $this->assertEquals($data['customer_id'], $found->customer_id);
    }

    /** @test */
//    public function it_errors_updating_the_payments()
//    {
//        $this->expectException(\Illuminate\Database\QueryException::class);
//        $payment = factory(Payment::class)->create();
//        $paymentRepo = new PaymentRepository($payment);
//        $paymentRepo->updatePayment(['name' => null]);
//    }

    /** @test */
    public function it_can_update_the_payments()
    {
        $payment = factory(Payment::class)->create();
        $paymentRepo = new PaymentRepository($payment);
        $update = [
            'customer_id' => $this->customer->id,
        ];
        $updated = $paymentRepo->save($update, $payment);
        $this->assertInstanceOf(Payment::class, $updated);
        $this->assertEquals($update['customer_id'], $updated->customer_id);
    }

    /** @test */
//    public function it_errors_when_creating_the_payments()
//    {
//        $this->expectException(\Illuminate\Database\QueryException::class);
//        $paymentRepo = new PaymentRepository(new Payment);
//        $paymentRepo->createPayment([]);
//    }
//
    /** @test */
    public function it_can_create_a_payments()
    {
        $invoice = factory(Invoice::class)->create();
        $factory = (new PaymentFactory())->create($this->customer->id, $this->user->id, $this->account_id);

        $data = [
            'customer_id' => $this->customer->id,
            'type_id' => 1,
            'amount' => $this->faker->randomFloat()
        ];

        $data['invoices'][0]['invoice_id'] = $invoice->id;
        $data['invoices'][0]['amount'] = $invoice->total;

        $paymentRepo = new PaymentRepository(new Payment);
        $created = $paymentRepo->save($data, $factory);
        $this->assertEquals($data['customer_id'], $created->customer_id);
        $this->assertEquals($data['type_id'], $created->type_id);
    }

//    public function testPartialPaymentAmount()
//    {
//        $client = CustomerFactory::create($this->account_id, $this->user->id);
//        $client->save();
//
//        $invoice = InvoiceFactory::create($this->customer->id, $this->user->id,
//            $this->account_id);//stub the company and user_id
//        $invoice->customer_id = $client->id;
//
//        $invoice->total = 10;
//        $invoice->partial = 2.0;
//        //$invoice->uses_inclusive_taxes = false;
//
//        $invoice->save();
//
//        $invoice_calc = new InvoiceSum($invoice);
//        $invoice_calc->build();
//
//        $invoice = $invoice_calc->getInvoice();
//        $invoice->total = 2;
//        $invoice->save();
//        $invoice->markSent();
//        $invoice->save();
//
//
//        $data = [
//            'amount' => 2.0,
//            'customer_id' => $client->id,
//            'invoices' => [
//                [
//                    'invoice_id' => $invoice->id,
//                    'amount' => 2.0
//                ],
//            ],
//            'date' => '2019/12/12',
//        ];
//
//        $factory = (new PaymentFactory())->create($this->customer->id, $this->user->id, $this->account_id);
//        $paymentRepo = new PaymentRepository(new Payment);
//        $payment = $paymentRepo->save($data, $factory);
//        $this->assertEquals($data['customer_id'], $payment->customer_id);
//        $this->assertNotNull($payment);
//        $this->assertNotNull($payment->invoices());
//        $this->assertEquals(1, $payment->invoices()->count());
//
//        $pivot_invoice = $payment->invoices()->first();
//        //$this->assertEquals($pivot_invoice->pivot->total, 2);
//        $this->assertEquals($pivot_invoice->partial, 0);
//        //$this->assertEquals($pivot_invoice->amount, 10.0000);
//        $this->assertEquals($pivot_invoice->balance, 8.0000);
//    }

    public function testPaymentGreaterThanPartial()
    {
        $client = CustomerFactory::create($this->account_id, $this->user->id);
        $client->save();

        $invoice = InvoiceFactory::create($this->user->id, $this->account_id, $client, 0,
            $client->getMergedSettings());//stub the company and user_id
        $invoice->customer_id = $client->id;

        $invoice->partial = 5.0;
        //$invoice->uses_inclusive_taxes = false;

        $invoice->save();

        $invoice_calc = new InvoiceSum($invoice);
        $invoice_calc->build();

        $invoice = $invoice_calc->getInvoice();
        $invoice->save();
        $invoice->service()->markSent();
        $invoice->is_deleted = false;
        $invoice->save();


        $data = [
            'amount' => 6.0,
            'customer_id' => $client->id,
            'invoices' => [
                [
                    'invoice_id' => $invoice->id,
                    'amount' => 6.0
                ],
            ],
            'date' => '2019/12/12',
        ];

        $factory = (new PaymentFactory())->create($client->id, $this->user->id, $this->account_id);
        $paymentRepo = new PaymentRepository(new Payment);
        $payment = $paymentRepo->save($data, $factory);
        $this->assertEquals($data['customer_id'], $payment->customer_id);
        $this->assertNotNull($payment->invoices());
        $this->assertEquals(1, $payment->invoices()->count());

        $invoice = $payment->invoices()->first();

        $this->assertEquals($invoice->partial, 0);
        //$this->assertEquals($invoice->balance, 4);
    }

    public function testPaymentLessThanPartialAmount()
    {
        $client = CustomerFactory::create($this->account_id, $this->user->id);
        $client->save();

        $invoice = InvoiceFactory::create($this->user->id, $this->account_id, $client, 0,
            $client->getMergedSettings());//stub the company and user_id
        $invoice->customer_id = $client->id;

        $invoice->partial = 5.0;
        //$invoice->line_items = $this->buildLineItems();
        //$invoice->uses_inclusive_taxes = false;

        $invoice->save();

        $invoice_calc = new InvoiceSum($invoice);
        $invoice_calc->build();

        $invoice = $invoice_calc->getInvoice();
        $invoice->save();
        $invoice->service()->markSent();
        $invoice->save();


        $data = [
            'amount' => 2.0,
            'customer_id' => $client->id,
            'invoices' => [
                [
                    'invoice_id' => $invoice->id,
                    'amount' => 2.0
                ],
            ],
            'date' => '2019/12/12',
        ];

        $factory = (new PaymentFactory())->create($this->customer->id, $this->user->id, $this->account_id);
        $paymentRepo = new PaymentRepository(new Payment);
        $payment = $paymentRepo->save($data, $factory);

        $this->assertNotNull($payment);
        $this->assertNotNull($payment->invoices());
        $this->assertEquals(1, $payment->invoices()->count());

        $invoice = $payment->invoices()->first();

        $this->assertEquals($invoice->partial, 3);
        //$this->assertEquals($invoice->balance, 8);

    }

    public function testBasicRefundValidation()
    {
        $client = CustomerFactory::create($this->account_id, $this->user->id);
        $client->save();

        $invoice = InvoiceFactory::create($this->user->id, $this->account_id, $client, 0,
            $client->getMergedSettings());//stub the company and user_id
        $invoice->customer_id = $client->id;
        $invoice->status_id = Invoice::STATUS_SENT;
        //$invoice->uses_inclusive_Taxes = false;
        $invoice->save();

        $invoice_calc = new InvoiceSum($invoice);
        $invoice_calc->build();

        $invoice = $invoice_calc->getInvoice();
        $invoice->save();

        $data = [
            'amount' => 50,
            'customer_id' => $client->id,
            // 'invoices' => [
            //     [
            //     'invoice_id' => $this->invoice->hashed_id,
            //     'amount' => $this->invoice->amount
            //     ],
            // ],
            'date' => '2020/12/12',

        ];

        $factory = (new PaymentFactory())->create($this->customer->id, $this->user->id, $this->account_id);
        $paymentRepo = new PaymentRepository(new Payment);
        $payment = $paymentRepo->save($data, $factory);

        $this->assertNotNull($payment);
        $this->assertEquals(50, $payment->amount);


        $data = [
            'id' => $payment->id,
            'refunded' => 50,
            // 'invoices' => [
            //     [
            //     'invoice_id' => $this->invoice->hashed_id,
            //     'amount' => $this->invoice->amount
            //     ],
            // ],
            'date' => '2020/12/12',
        ];

        $paymentRepo = new PaymentRepository(new Payment);
        $payment = $paymentRepo->save($data, $factory);
        $this->assertNotNull($payment);
        $this->assertEquals(50, $payment->refunded);
    }

//    public function testPaymentValidationAmount()
//    {
//        $client = CustomerFactory::create($this->account_id, $this->user->id);
//        $client->save();
//
//        $invoice = InvoiceFactory::create($this->customer->id, $this->user->id, $this->account_id);//stub the company and user_id
//        $invoice->partial = 5.0;
//        //$this-invoice->line_items = $this->buildLineItems();
//        $invoice->uses_inclusive_taxes = false;
//        $invoice->customer_id = $client->id;
//        $invoice->save();
//
//        $invoice_calc = new InvoiceSum($this->invoice);
//        $invoice_calc->build();
//
//        $invoice = $invoice_calc->getInvoice();
//        $invoice->save();
//        $invoice->markSent();
//        $invoice->save();
//
//
//        $data = [
//            'amount' => 1.0,
//            'customer_id' => $client->id,
//            'invoices' => [
//                [
//                    'invoice_id' => $invoice->id,
//                    'amount' => 2.0
//                ],
//            ],
//            'date' => '2019/12/12',
//        ];
//
//        try {
//            $response = $this->withHeaders([
//                'X-API-SECRET' => config('ninja.api_secret'),
//                'X-API-TOKEN' => $this->token,
//            ])->post('/api/v1/payments?include=invoices', $data);
//
//        }
//        catch(ValidationException $e) {
//
//            $message = json_decode($e->validator->getMessageBag(),1);
//
//            $this->assertTrue(array_key_exists('amount', $message));
//
//        }
//
//        //     if($response)
//        //         $response->assertStatus(200);
//    }
//
//
//    public function testPaymentChangesBalancesCorrectly()
//    {
//
//        $this->invoice = null;
//
//        $client = ClientFactory::create($this->company->id, $this->user->id);
//        $client->save();
//
//        $this->invoice = InvoiceFactory::create($this->company->id,$this->user->id);//stub the company and user_id
//        $this->invoice->client_id = $client->id;
//
//        $this->invoice->line_items = $this->buildLineItems();
//        $this->invoice->uses_inclusive_taxes = false;
//
//        $this->invoice->save();
//
//        $this->invoice_calc = new InvoiceSum($this->invoice);
//        $this->invoice_calc->build();
//
//        $this->invoice = $this->invoice_calc->getInvoice();
//        $this->invoice->save();
//        $this->invoice->markSent();
//        $this->invoice->save();
//
//
//        $data = [
//            'amount' => 2.0,
//            'client_id' => $client->hashed_id,
//            'invoices' => [
//                [
//                    'invoice_id' => $this->invoice->hashed_id,
//                    'amount' => 2.0
//                ],
//            ],
//            'date' => '2019/12/12',
//        ];
//
//        $response = false;
//
//        try {
//            $response = $this->withHeaders([
//                'X-API-SECRET' => config('ninja.api_secret'),
//                'X-API-TOKEN' => $this->token,
//            ])->post('/api/v1/payments?include=invoices', $data);
//
//        }
//        catch(ValidationException $e) {
//
//            $message = json_decode($e->validator->getMessageBag(),1);
//
//            $this->assertTrue(array_key_exists('amount', $message));
//
//        }
//
//        if($response){
//            $response->assertStatus(200);
//
//            $invoice = Invoice::find($this->decodePrimaryKey($this->invoice->hashed_id));
//
//            $this->assertEquals($invoice->balance, 8);
//
//            $payment = $invoice->payments()->first();
//
//            $this->assertEquals($payment->applied, 2);
//        }
//    }
//
//    public function testUpdatePaymentValidationWorks()
//    {
//
//        $this->invoice = null;
//
//        $client = ClientFactory::create($this->company->id, $this->user->id);
//        $client->save();
//
//        $this->invoice = InvoiceFactory::create($this->company->id,$this->user->id);//stub the company and user_id
//        $this->invoice->client_id = $client->id;
//
//        $this->invoice->line_items = $this->buildLineItems();
//        $this->invoice->uses_inclusive_taxes = false;
//
//        $this->invoice->save();
//
//        $this->invoice_calc = new InvoiceSum($this->invoice);
//        $this->invoice_calc->build();
//
//        $this->invoice = $this->invoice_calc->getInvoice();
//        $this->invoice->save();
//        $this->invoice->markSent();
//        $this->invoice->save();
//
//        $payment = PaymentFactory::create($this->company->id, $this->user->id);
//        $payment->amount = 10;
//        $payment->client_id = $client->id;
//        $payment->date = now();
//        $payment->save();
//
//
//        $data = [
//            'amount' => 2.0,
//            'client_id' => $client->hashed_id,
//            'invoices' => [],
//            'date' => '2019/12/12',
//        ];
//
//        $response = false;
//
//        try {
//            $response = $this->withHeaders([
//                'X-API-SECRET' => config('ninja.api_secret'),
//                'X-API-TOKEN' => $this->token,
//            ])->put('/api/v1/payments/'.$this->encodePrimaryKey($payment->id), $data);
//
//        }
//        catch(ValidationException $e) {
//
//            $message = json_decode($e->validator->getMessageBag(),1);
//
//            $this->assertTrue(array_key_exists('invoices', $message));
//
//        }
//
//        if($response)
//            $response->assertStatus(302);
//    }
//
//
//    public function testUpdatePaymentValidationPasses()
//    {
//
//        $this->invoice = null;
//
//        $client = ClientFactory::create($this->company->id, $this->user->id);
//        $client->save();
//
//        $this->invoice = InvoiceFactory::create($this->company->id,$this->user->id);//stub the company and user_id
//        $this->invoice->client_id = $client->id;
//
//        $this->invoice->line_items = $this->buildLineItems();
//        $this->invoice->uses_inclusive_taxes = false;
//
//        $this->invoice->save();
//
//        $this->invoice_calc = new InvoiceSum($this->invoice);
//        $this->invoice_calc->build();
//
//        $this->invoice = $this->invoice_calc->getInvoice();
//        $this->invoice->save();
//        $this->invoice->markSent();
//        $this->invoice->save();
//
//        $payment = PaymentFactory::create($this->company->id, $this->user->id);
//        $payment->amount = 10;
//        $payment->client_id = $client->id;
//        $payment->date = now();
//        $payment->number = $client->getNextPaymentNumber($client);
//        $payment->save();
//
//
//        $data = [
//            'amount' => 10.0,
//            'client_id' => $this->encodePrimaryKey($client->id),
//            'invoices' => [
//                [
//                    'invoice_id' => $this->encodePrimaryKey($this->invoice->id),
//                    'amount' => 10,
//                ]
//            ],
//            'date' => '2019/12/12',
//        ];
//
//        $response = false;
//
//        try {
//            $response = $this->withHeaders([
//                'X-API-SECRET' => config('ninja.api_secret'),
//                'X-API-TOKEN' => $this->token,
//            ])->put('/api/v1/payments/'.$this->encodePrimaryKey($payment->id), $data);
//
//        }
//        catch(ValidationException $e) {
//
//            $message = json_decode($e->validator->getMessageBag(),1);
//            \Log::error(print_r($e->validator->getMessageBag(),1));
//
//            $this->assertTrue(array_key_exists('invoices', $message));
//
//        }
//
//        if($response)
//            $response->assertStatus(200);
//    }
//
//
//    public function testDoublePaymentTestWithInvalidAmounts()
//    {
//
//        $this->invoice = null;
//
//        $client = ClientFactory::create($this->company->id, $this->user->id);
//        $client->save();
//
//        $this->invoice = InvoiceFactory::create($this->company->id,$this->user->id);//stub the company and user_id
//        $this->invoice->client_id = $client->id;
//
//        $this->invoice->line_items = $this->buildLineItems();
//        $this->invoice->uses_inclusive_taxes = false;
//
//        $this->invoice->save();
//
//        $this->invoice_calc = new InvoiceSum($this->invoice);
//        $this->invoice_calc->build();
//
//        $this->invoice = $this->invoice_calc->getInvoice();
//        $this->invoice->save();
//        $this->invoice->markSent();
//
//        $data = [
//            'amount' => 15.0,
//            'client_id' => $this->encodePrimaryKey($client->id),
//            'invoices' => [
//                [
//                    'invoice_id' => $this->encodePrimaryKey($this->invoice->id),
//                    'amount' => 10,
//                ]
//            ],
//            'date' => '2019/12/12',
//        ];
//
//        $response = false;
//
//        try {
//            $response = $this->withHeaders([
//                'X-API-SECRET' => config('ninja.api_secret'),
//                'X-API-TOKEN' => $this->token,
//            ])->post('/api/v1/payments/', $data);
//        }
//        catch(ValidationException $e) {
//
//            $message = json_decode($e->validator->getMessageBag(),1);
//            \Log::error(print_r($e->validator->getMessageBag(),1));
//        }
//
//        $response->assertStatus(200);
//
//        $arr = $response->json();
//
//        $payment_id = $arr['data']['id'];
//
//        $payment = Payment::whereId($this->decodePrimaryKey($payment_id))->first();
//
//        $this->assertEquals($payment->amount, 15);
//        $this->assertEquals($payment->applied, 10);
//
//        $this->invoice = null;
//        $this->invoice = InvoiceFactory::create($this->company->id,$this->user->id);//stub the company and user_id
//        $this->invoice->client_id = $client->id;
//
//        $this->invoice->line_items = $this->buildLineItems();
//        $this->invoice->uses_inclusive_taxes = false;
//
//        $this->invoice->save();
//
//        $this->invoice_calc = new InvoiceSum($this->invoice);
//        $this->invoice_calc->build();
//
//        $this->invoice = $this->invoice_calc->getInvoice();
//        $this->invoice->save();
//        $this->invoice->markSent();
//
//
//        $data = [
//            'amount' => 15.0,
//            'client_id' => $this->encodePrimaryKey($client->id),
//            'invoices' => [
//                [
//                    'invoice_id' => $this->encodePrimaryKey($this->invoice->id),
//                    'amount' => 10,
//                ]
//            ],
//            'date' => '2019/12/12',
//        ];
//
//
//        $response = false;
//
//        try {
//            $response = $this->withHeaders([
//                'X-API-SECRET' => config('ninja.api_secret'),
//                'X-API-TOKEN' => $this->token,
//            ])->put('/api/v1/payments/'.$this->encodePrimaryKey($payment->id), $data);
//
//        }
//        catch(ValidationException $e) {
//
//            $message = json_decode($e->validator->getMessageBag(),1);
//            // \Log::error(print_r($e->validator->getMessageBag(),1));
//
//            $this->assertTrue(array_key_exists('invoices', $message));
//            // \Log::error('hit error');
//        }
//
//        //$response->assertStatus(302);
//
//    }
//
//
//    public function testDoublePaymentTestWithValidAmounts()
//    {
//
//        $this->invoice = null;
//
//        $client = ClientFactory::create($this->company->id, $this->user->id);
//        $client->save();
//
//        $this->invoice = InvoiceFactory::create($this->company->id,$this->user->id);//stub the company and user_id
//        $this->invoice->client_id = $client->id;
//
//        $this->invoice->line_items = $this->buildLineItems();
//        $this->invoice->uses_inclusive_taxes = false;
//
//        $this->invoice->save();
//
//        $this->invoice_calc = new InvoiceSum($this->invoice);
//        $this->invoice_calc->build();
//
//        $this->invoice = $this->invoice_calc->getInvoice();
//        $this->invoice->save();
//        $this->invoice->markSent();
//
//        $data = [
//            'amount' => 20.0,
//            'client_id' => $this->encodePrimaryKey($client->id),
//            'invoices' => [
//                [
//                    'invoice_id' => $this->encodePrimaryKey($this->invoice->id),
//                    'amount' => 10,
//                ]
//            ],
//            'date' => '2019/12/12',
//        ];
//
//        $response = $this->withHeaders([
//            'X-API-SECRET' => config('ninja.api_secret'),
//            'X-API-TOKEN' => $this->token,
//        ])->post('/api/v1/payments/', $data);
//
//        $response->assertStatus(200);
//
//        $arr = $response->json();
//
//        $payment_id = $arr['data']['id'];
//
//        $payment = Payment::whereId($this->decodePrimaryKey($payment_id))->first();
//
//        $this->assertEquals($payment->amount, 20);
//        $this->assertEquals($payment->applied, 10);
//
//        $this->invoice = null;
//        $this->invoice = InvoiceFactory::create($this->company->id,$this->user->id);//stub the company and user_id
//        $this->invoice->client_id = $client->id;
//
//        $this->invoice->line_items = $this->buildLineItems();
//        $this->invoice->uses_inclusive_taxes = false;
//
//        $this->invoice->save();
//
//        $this->invoice_calc = new InvoiceSum($this->invoice);
//        $this->invoice_calc->build();
//
//        $this->invoice = $this->invoice_calc->getInvoice();
//        $this->invoice->save();
//        $this->invoice->markSent();
//
//
//        $data = [
//            'amount' => 20.0,
//            'client_id' => $this->encodePrimaryKey($client->id),
//            'invoices' => [
//                [
//                    'invoice_id' => $this->encodePrimaryKey($this->invoice->id),
//                    'amount' => 10,
//                ]
//            ],
//            'date' => '2019/12/12',
//        ];
//
//
//        $response = false;
//
//        try {
//            $response = $this->withHeaders([
//                'X-API-SECRET' => config('ninja.api_secret'),
//                'X-API-TOKEN' => $this->token,
//            ])->put('/api/v1/payments/'.$this->encodePrimaryKey($payment->id), $data);
//
//        }
//        catch(ValidationException $e) {
//
//            $message = json_decode($e->validator->getMessageBag(),1);
//            \Log::error(print_r($e->validator->getMessageBag(),1));
//
//            $this->assertTrue(array_key_exists('invoices', $message));
//            \Log::error('hit error');
//        }
//
//        $response->assertStatus(200);
//
//    }
//
//
//    public function testStorePaymentWithNoAmountField()
//    {
//        $client = ClientFactory::create($this->company->id, $this->user->id);
//        $client->save();
//
//        $this->invoice = InvoiceFactory::create($this->company->id,$this->user->id);//stub the company and user_id
//        $this->invoice->client_id = $client->id;
//        $this->invoice->status_id = Invoice::STATUS_SENT;
//
//        $this->invoice->line_items = $this->buildLineItems();
//        $this->invoice->uses_inclusive_Taxes = false;
//
//        $this->invoice->save();
//
//        $this->invoice_calc = new InvoiceSum($this->invoice);
//        $this->invoice_calc->build();
//
//        $this->invoice = $this->invoice_calc->getInvoice();
//        $this->invoice->save();
//
//        $data = [
//            'client_id' => $client->hashed_id,
//            'invoices' => [
//                [
//                    'invoice_id' => $this->invoice->hashed_id,
//                    'amount' => $this->invoice->amount
//                ],
//            ],
//            'date' => '2020/12/12',
//
//        ];
//
//        $response = null;
//
//        try {
//            $response = $this->withHeaders([
//                'X-API-SECRET' => config('ninja.api_secret'),
//                'X-API-TOKEN' => $this->token,
//            ])->post('/api/v1/payments?include=invoices', $data);
//
//        }
//        catch(ValidationException $e) {
//            // \Log::error('in the validator');
//            $message = json_decode($e->validator->getMessageBag(),1);
//            // \Log::error($message);
//            $this->assertNotNull($message);
//
//        }
//
//        if($response){
//            $arr = $response->json();
//            $response->assertStatus(200);
//
//            $payment_id = $arr['data']['id'];
//
//            $this->assertEquals($this->invoice->amount, $arr['data']['amount']);
//
//            $payment = Payment::whereId($this->decodePrimaryKey($payment_id))->first();
//
//            $this->assertNotNull($payment);
//            $this->assertNotNull($payment->invoices());
//            $this->assertEquals(1, $payment->invoices()->count());
//        }
//
//    }
//
//
//    public function testStorePaymentWithZeroAmountField()
//    {
//        $client = ClientFactory::create($this->company->id, $this->user->id);
//        $client->save();
//
//        $this->invoice = InvoiceFactory::create($this->company->id,$this->user->id);//stub the company and user_id
//        $this->invoice->client_id = $client->id;
//        $this->invoice->status_id = Invoice::STATUS_SENT;
//
//        $this->invoice->line_items = $this->buildLineItems();
//        $this->invoice->uses_inclusive_Taxes = false;
//
//        $this->invoice->save();
//
//        $this->invoice_calc = new InvoiceSum($this->invoice);
//        $this->invoice_calc->build();
//
//        $this->invoice = $this->invoice_calc->getInvoice();
//        $this->invoice->save();
//
//        $data = [
//            'amount' => 0,
//            'client_id' => $client->hashed_id,
//            'invoices' => [
//                [
//                    'invoice_id' => $this->invoice->hashed_id,
//                    'amount' => $this->invoice->amount
//                ],
//            ],
//            'date' => '2020/12/12',
//
//        ];
//
//        try {
//            $response = $this->withHeaders([
//                'X-API-SECRET' => config('ninja.api_secret'),
//                'X-API-TOKEN' => $this->token,
//            ])->post('/api/v1/payments?include=invoices', $data);
//
//        }
//        catch(ValidationException $e) {
//            // \Log::error('in the validator');
//            $message = json_decode($e->validator->getMessageBag(),1);
//            // \Log::error($message);
//            $this->assertNotNull($message);
//
//        }
//
//
//    }
//
//
//    public function testBasicRefundValidation()
//    {
//        $client = CustomerFactory::create($this->company->id, $this->user->id);
//        $client->save();
//
//        $this->invoice = InvoiceFactory::create($this->company->id,$this->user->id);//stub the company and user_id
//        $this->invoice->client_id = $client->id;
//        $this->invoice->status_id = Invoice::STATUS_SENT;
//
//        $this->invoice->line_items = $this->buildLineItems();
//        $this->invoice->uses_inclusive_Taxes = false;
//
//        $this->invoice->save();
//
//        $this->invoice_calc = new InvoiceSum($this->invoice);
//        $this->invoice_calc->build();
//
//        $this->invoice = $this->invoice_calc->getInvoice();
//        $this->invoice->save();
//
//        $data = [
//            'amount' => 50,
//            'client_id' => $client->hashed_id,
//            // 'invoices' => [
//            //     [
//            //     'invoice_id' => $this->invoice->hashed_id,
//            //     'amount' => $this->invoice->amount
//            //     ],
//            // ],
//            'date' => '2020/12/12',
//
//        ];
//
//        $response = $this->withHeaders([
//            'X-API-SECRET' => config('ninja.api_secret'),
//            'X-API-TOKEN' => $this->token,
//        ])->post('/api/v1/payments', $data);
//
//
//        $arr = $response->json();
//        $response->assertStatus(200);
//
//        $payment_id = $arr['data']['id'];
//
//        $this->assertEquals(50, $arr['data']['amount']);
//
//        $payment = Payment::whereId($this->decodePrimaryKey($payment_id))->first();
//
//        $this->assertNotNull($payment);
//        // $this->assertNotNull($payment->invoices());
//        // $this->assertEquals(1, $payment->invoices()->count());
//
//
//        $data = [
//            'id' => $this->encodePrimaryKey($payment->id),
//            'refunded' => 50,
//            'gateway_refund' => false,
//            // 'invoices' => [
//            //     [
//            //     'invoice_id' => $this->invoice->hashed_id,
//            //     'amount' => $this->invoice->amount
//            //     ],
//            // ],
//            'date' => '2020/12/12',
//        ];
//
//
//        $response = $this->withHeaders([
//            'X-API-SECRET' => config('ninja.api_secret'),
//            'X-API-TOKEN' => $this->token,
//        ])->post('/api/v1/payments/refund', $data);
//
//        $arr = $response->json();
//
//        $response->assertStatus(200);
//
//        $payment_id = $arr['data']['id'];
//
//        $payment = Payment::whereId($this->decodePrimaryKey($payment_id))->first();
//
//        $this->assertEquals(50, $payment->refunded);
//        $this->assertEquals(50, $payment->amount);
//
//    }
}

