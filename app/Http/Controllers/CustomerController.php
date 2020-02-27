<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Events\Customer\CustomerWasCreated;
use App\Events\Customer\CustomerWasUpdated;
use App\Jobs\Customer\StoreCustomerAddress;
use App\Jobs\Utils\ProcessBulk;
use App\Order;
use App\Repositories\CustomerRepository;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Repositories\Interfaces\AddressRepositoryInterface;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Requests\Customer\BulkCustomerRequest;
use App\Transformations\CustomerTransformable;
use App\Requests\Customer\UpdateCustomerRequest;
use App\Requests\Customer\CreateCustomerRequest;
use App\Requests\SearchRequest;
use App\Repositories\CustomerTypeRepository;
use App\CustomerType;
use Exception;
use Illuminate\Http\Request;
use App\Factory\CustomerFactory;
use App\Filters\CustomerFilter;
use App\Traits\BulkOptions;
use App\Traits\CheckEntityStatus;
use Illuminate\Http\Response;

class CustomerController extends Controller
{

    use CustomerTransformable, BulkOptions, CheckEntityStatus;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customer_repo;

    /**
     * CustomerController constructor.
     * @param CustomerRepositoryInterface $customer_repo
     */
    public function __construct(CustomerRepositoryInterface $customer_repo)
    {
        $this->customer_repo = $customer_repo;
    }

    /**
     * @param SearchRequest $request
     * @return mixed
     */
    public function index(SearchRequest $request)
    {
        $customers =
            (new CustomerFilter($this->customer_repo))->filter($request, auth()->user()->account_user()->account_id);
        return response()->json($customers);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCustomerRequest $request
     * @param int $id
     * @return Response
     */
    public function update(UpdateCustomerRequest $request, $id)
    {
        $customer = $this->customer_repo->findCustomerById($id);

        if ($this->entityIsDeleted($customer)) {
            return $this->disallowUpdate();
        }

        $customer = $this->customer_repo->save($request->except('addresses', 'settings'), $customer);
        $customer = StoreCustomerAddress::dispatchNow($customer, $request->all());
        event(new CustomerWasUpdated($customer));

        if ($request->has('settings') && !empty($request->settings)) {
            $customer->saveSettings($request->input('settings'), $customer);
        }

        return response()->json($this->transformCustomer($customer));
    }

    public function show(int $id)
    {
        $customer = $this->customer_repo->findCustomerById($id);
        return response()->json($this->transformCustomer($customer));
    }

    /**
     * @param CreateCustomerRequest $request
     * @return Customer
     */
    public function store(CreateCustomerRequest $request)
    {
        $customer = $this->customer_repo->save($request->except('addresses', 'settings'),
            CustomerFactory::create(auth()->user()->account_user()->account_id, auth()->user()->id,
                $request->company_id));
        $customer = StoreCustomerAddress::dispatchNow($customer, $request->only('addresses'));
        event(new CustomerWasCreated($customer));

        if ($request->has('settings') && !empty($request->settings)) {
            $customer->saveSettings($request->input('settings'), $customer);
        }

        return $this->transformCustomer($customer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     * @throws Exception
     */
    public function archive(int $id)
    {
        $customer = $this->customer_repo->findCustomerById($id);
        $response = (new CustomerRepository($customer))->delete($id);

        if ($response) {
            return response()->json('Customer deleted!');
        }

        return response()->json('Unable to delete customer!');
    }

    public function destroy(int $id)
    {
        $customer = Customer::withTrashed()->where('id', '=', $id)->first();
        $this->customer_repo->newDelete($customer);
        return response()->json([], 200);
    }

    public function getCustomerTypes()
    {
        $customerTypes = (new CustomerTypeRepository(new CustomerType))->getAll();
        return response()->json($customerTypes);
    }

    /**
     * @param BulkCustomerRequest $request
     * @return mixed
     */
    public function bulk()
    {
        $action = request()->input('action');

        $ids = request()->input('ids');
        $clients = Customer::withTrashed()->find($ids);

        $clients->each(function ($client, $key) use ($action) {
            $this->customer_repo->{$action}($client);
        });
        return response()->json(Customer::withTrashed()->whereIn('id', $ids));
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id)
    {
        $group = Customer::withTrashed()->where('id', '=', $id)->first();
        $this->customer_repo->restore($group);
        return response()->json([], 200);
    }
}
