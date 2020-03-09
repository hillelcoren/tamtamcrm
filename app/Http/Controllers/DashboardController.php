<?php

namespace App\Http\Controllers;

use App\Expense;
use App\Invoice;
use App\Payment;
use App\Quote;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Transformations\TaskTransformable;
use App\Task;

class DashboardController extends Controller
{

    use TaskTransformable;

    /**
     * @var TaskRepositoryInterface
     */
    private $taskRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * DashboardController constructor.
     *
     * TaskRepositoryInterface $taskRepository
     */
    public function __construct(TaskRepositoryInterface $taskRepository,
        CustomerRepositoryInterface $customerRepository)
    {
        $this->taskRepository = $taskRepository;
        $this->customerRepository = $customerRepository;
    }

    public function index()
    {

        $arrSources = $this->taskRepository->getSourceTypeCounts(3, auth()->user()->account_user()->account_id);
        $arrStatuses = $this->taskRepository->getStatusCounts(3, auth()->user()->account_user()->account_id);
        $leadsToday = $this->taskRepository->getRecentTasks(3, 3, auth()->user()->account_user()->account_id);
        $customersToday = $this->customerRepository->getRecentCustomers(3, auth()->user()->account_user()->account_id);
        $newDeals = $this->taskRepository->getNewDeals(3, auth()->user()->account_user()->account_id);
        $leads = $this->taskRepository->getLeads(10, null, auth()->user()->account_user()->account_id);
        $totalEarnt = $this->taskRepository->getTotalEarnt(3, auth()->user()->account_user()->account_id);

        $tasks = $leads->map(function (Task $task) {
            return $this->transformTask($task);
        })->all();

        $arrOutput = [
            'sources' => $arrSources->toArray(),
            'leadCounts' => $arrStatuses->toArray(),
            'totalBudget' => number_format($totalEarnt, 2),
            'totalEarnt' => number_format($totalEarnt, 2),
            'leadsToday' => number_format($leadsToday, 2),
            'newDeals' => number_format($newDeals, 2),
            'newCustomers' => number_format($customersToday, 2),
            'deals' => $tasks,
            'invoices' => Invoice::all()->where('account_id', auth()->user()->account_user()->account_id),
            'quotes' => Quote::all()->where('account_id', auth()->user()->account_user()->account_id),
            'payments' => Payment::all()->where('account_id', auth()->user()->account_user()->account_id),
            'expenses' => Expense::all()->where('account_id', auth()->user()->account_user()->account_id),
            'tasks' => Task::all()->where('account_id', auth()->user()->account_user()->account_id),
        ];

        return response()->json($arrOutput);
    }

}
