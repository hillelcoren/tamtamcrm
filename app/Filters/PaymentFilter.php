<?php

namespace App\Filters;

use App\Payment;
use App\Repositories\PaymentRepository;
use App\Requests\SearchRequest;
use App\Transformations\PaymentTransformable;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentFilter extends QueryFilter
{
    use PaymentTransformable;

    private $paymentRepository;

    private $model;

    /**
     * PaymentFilter constructor.
     * @param PaymentRepository $paymentRepository
     */
    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
        $this->model = $paymentRepository->getModel();
    }

    /**
     * @param SearchRequest $request
     * @return array|LengthAwarePaginator
     */
    public function filter(SearchRequest $request, int $account_id)
    {
        $recordsPerPage = !$request->per_page ? 0 : $request->per_page;
        $orderBy = !$request->column ? 'amount' : $request->column;
        $orderDir = !$request->order ? 'asc' : $request->order;

        $this->query = $this->model->select('*');

        if ($request->has('status')) {
            $this->filterStatus($request->status);
        }

        if ($request->filled('customer_id')) {
            $this->query->whereCustomerId($request->customer_id);
        }

        if ($request->filled('search_term')) {
            $this->query = $this->searchFilter($request->search_term);
        }

        if ($request->input('start_date') <> '' && $request->input('end_date') <> '') {
            $this->filterDates($request);
        }

        $this->addAccount($account_id);

        $this->orderBy($orderBy, $orderDir);

        $payments = $this->transformList();

        if ($recordsPerPage > 0) {
            $paginatedResults = $this->paymentRepository->paginateArrayResults($payments, $recordsPerPage);
            return $paginatedResults;
        }
        return $payments;
    }

    private function filterDates($request)
    {
        $start = date("Y-m-d", strtotime($request->input('start_date')));
        $end = date("Y-m-d", strtotime($request->input('end_date')));
        $this->query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Filter based on search text
     *
     * @param string query filter
     * @return Illuminate\Database\Query\Builder
     * @deprecated
     *
     */
    public function searchFilter(string $filter = '')
    {
        if (strlen($filter) == 0) {
            return $this->query;
        }
        return $this->query->where(function ($query) use ($filter) {
            $query->where('payments.amount', 'like', '%' . $filter . '%')
                  ->orWhere('payments.date', 'like', '%' . $filter . '%')
                  ->orWhere('payments.custom_value1', 'like', '%' . $filter . '%')
                  ->orWhere('payments.custom_value2', 'like', '%' . $filter . '%')
                  ->orWhere('payments.custom_value3', 'like', '%' . $filter . '%')
                  ->orWhere('payments.custom_value4', 'like', '%' . $filter . '%');
        });
    }

    private function orderBy($orderBy, $orderDir)
    {
        $this->query->orderBy($orderBy, $orderDir);
    }

    private function addAccount(int $account_id)
    {
        $this->query->where('account_id', '=', $account_id);
    }

    private function transformList()
    {
        $list = $this->query->get();
        $payments = $list->map(function (Payment $payment) {
            return $this->transformPayment($payment);
        })->all();

        return $payments;
    }

    private function filterStatus($filter)
    {
        $filters = explode(',', $filter);
        $table = 'payments';
        $this->query->whereNull($table . '.id');

        if (in_array(parent::STATUS_ACTIVE, $filters)) {
            $this->query->orWhereNull($table . '.deleted_at');
        }
        if (in_array(parent::STATUS_ARCHIVED, $filters)) {

            $this->query->orWhere(function ($query) use ($table) {
                $query->whereNotNull($table . '.deleted_at');
            });

            $this->query->withTrashed();
        }
        if (in_array(parent::STATUS_DELETED, $filters)) {
            $this->query->orWhere($table . '.is_deleted', '=', 1)->withTrashed();
        }
    }

}
