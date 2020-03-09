<?php

namespace App\Filters;

use App\Product;
use App\Task;
use App\Repositories\ProductRepository;
use App\Requests\SearchRequest;
use App\Transformations\ProductTransformable;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductFilter extends QueryFilter
{
    use ProductTransformable;

    private $productRepository;

    private $model;

    /**
     * ProductFilter constructor.
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
        $this->model = $productRepository->getModel();
    }

    /**
     * @param SearchRequest $request
     * @param int $account_id
     * @return LengthAwarePaginator|mixed
     */
    public function filter(SearchRequest $request, int $account_id)
    {
        $recordsPerPage = !$request->per_page ? 0 : $request->per_page;
        $orderBy = !$request->column ? 'name' : $request->column;
        $orderDir = !$request->order ? 'asc' : $request->order;

        $this->query = $this->model->select('products.id as id', 'products.*')
                                   ->leftJoin('category_product', 'products.id', '=', 'category_product.product_id');

        if ($request->has('status')) {
            $this->status($request->status);
        }

        if ($request->filled('company_id')) {
            $this->query->where('company_id', '=', $request->company_id);
        }

        if ($request->filled('category_id')) {
            $this->query->where('category_id', '=', $request->category_id);
        }

        if ($request->filled('search_term')) {
            $this->query = $this->searchFilter($request->search_term);
        }

        if ($request->input('start_date') <> '' && $request->input('end_date') <> '') {
            $this->filterDates($request);
        }

        $this->addAccount($account_id);

        $this->orderBy($orderBy, $orderDir);

        $this->query->groupBy('products.id');

        $products = $this->transformList();

        if ($recordsPerPage > 0) {
            $paginatedResults = $this->productRepository->paginateArrayResults($products, $recordsPerPage);
            return $paginatedResults;
        }

        return $products;
    }

    private function filterDates($request)
    {
        $start = date("Y-m-d", strtotime($request->input('start_date')));
        $end = date("Y-m-d", strtotime($request->input('end_date')));
        $this->query->whereBetween('created_at', [$start, $end]);
    }

    private function orderBy($orderBy, $orderDir)
    {
        $this->query->orderBy($orderBy, $orderDir);
    }

    private function addAccount(int $account_id)
    {
        $this->query->where('account_id', '=', $account_id);
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
            $query->where('products.sku', 'like', '%' . $filter . '%')
                  ->orWhere('products.name', 'like', '%' . $filter . '%')
                  ->orWhere('products.notes', 'like', '%' . $filter . '%')
                  ->orWhere('products.custom_value1', 'like', '%' . $filter . '%')
                  ->orWhere('products.custom_value2', 'like', '%' . $filter . '%')
                  ->orWhere('products.custom_value3', 'like', '%' . $filter . '%')
                  ->orWhere('products.custom_value4', 'like', '%' . $filter . '%');
        });
    }

    /**
     * @param $list
     * @return mixed
     */
    private function transformList()
    {
        $list = $this->query->get();
        $products = $list->map(function (Product $product) {
            return $this->transformProduct($product);
        })->all();

        return $products;
    }


    /**
     * Filters the list based on the status
     * archived, active, deleted
     *
     * @param string filter
     * @return Illuminate\Database\Query\Builder
     */
    public function status(string $filter = '')
    {
        if (strlen($filter) == 0) {
            return $this->query;
        }

        $table = 'products';
        $filters = explode(',', $filter);

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
            $this->query->orWhere($table . '.is_deleted', '=', 1);
        }
    }
}
