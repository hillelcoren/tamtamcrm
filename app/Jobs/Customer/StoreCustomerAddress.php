<?php

namespace App\Jobs\Customer;

use App\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoreCustomerAddress
{
    use Dispatchable;
    protected $data;
    protected $customer;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Customer $customer, array $data)
    {
        $this->data = $data;
        $this->customer = $customer;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CustomerRepository $client_repo): ?Customer
    {

        $this->customer->addresses()->forceDelete();

        if (isset($this->data['addresses'][0])) {
            $addresses = $this->data['addresses'][0];

            if (isset($addresses['billing']) && !empty($addresses['billing']) &&
                !empty($addresses['billing']['address_1'])) {
                $addresses['billing']['address_type'] = 1;
                $this->customer->addresses()->create($addresses['billing']);
            }

            if (isset($addresses['shipping']) && !empty($addresses['shipping']) &&
                !empty($addresses['shipping']['address_1'])) {
                $addresses['shipping']['address_type'] = 2;
                $this->customer->addresses()->create($addresses['shipping']);
            }
        }

        return $this->customer;
    }
}
