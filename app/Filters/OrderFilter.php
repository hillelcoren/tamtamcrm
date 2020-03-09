<?php

namespace App\Filters;

use App\Order;
use App\Repositories\OrderRepository;
use App\Repositories\Support;
use App\Task;
use App\Transformations\OrderTransformable;

class OrderFilter
{
    use OrderTransformable;
    private $orderRepository;

    private $query;

    private $model;

    /**
     * OrderFilter constructor.
     * @param OrderRepository $orderRepository
     */
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->model = $orderRepository->getModel();
    }

    private function transformList()
    {
        $list = $this->query->get();

        $orders = $list->map(function (Order $order) {
            return $this->transformOrder($order);
        })->all();
        return $orders;
    }

    private function baseQuery()
    {
        $this->query = $this->model->join('products', 'products.id', '=', 'product_task.product_id')
                                   ->select('product_task.*', 'products.price', 'product_task.id as order_id');
    }

    /**
     * @param Task $objTask
     */
    private function addTaskToQuery(Task $objTask)
    {
        $this->baseQuery();
        $this->query->where('product_task.task_id', $objTask->id);
    }

    /**
     * @param $filter
     * @return mixed
     */
    private function addStatusToQuery($filter)
    {

        if (strlen($filter) == 0) {
            return $this->query;
        }

        $filters = explode(',', $filter);

        $this->query->whereIn('product_task.status', $filters);
    }

    /**
     * @param Task $task
     * @return Support
     */
    public function filterByTask(Task $task)
    {
        $this->baseQuery();
        $this->addTaskToQuery($task);
        return $this->transformList();
    }

    /**
     * @param Task $objTask
     * @param int $status
     * @return mixed
     */
    public function getProductsForTask(Task $objTask, $status)
    {
        $this->baseQuery();
        $this->addTaskToQuery($objTask);
        $this->addStatusToQuery($status);

        return $this->transformList();
    }
}
