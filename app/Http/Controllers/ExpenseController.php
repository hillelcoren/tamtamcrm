<?php

namespace App\Http\Controllers;

use App\Factory\ExpenseFactory;
use App\Jobs\ProcessBulk;
use App\Filters\ExpenseFilter;
use App\Requests\Expense\CreateExpenseRequest;
use App\Requests\Expense\UpdateExpenseRequest;
use App\Requests\SearchRequest;
use App\Traits\CheckEntityStatus;
use App\Traits\BulkOptions;
use App\Expense;
use App\Repositories\ExpenseRepository;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Class VendorController
 * @package App\Http\Controllers
 * @covers App\Http\Controllers\VendorController
 */
class ExpenseController extends Controller
{
    use BulkOptions, CheckEntityStatus;

    /**
     * @var
     */
    protected $expense_repo;

    /**
     * ExpenseController constructor.
     * @param ExpenseRepository $expense_repo
     */
    public function __construct(ExpenseRepository $expense_repo)
    {
        $this->expense_repo = $expense_repo;
    }

    /**
     * @param SearchRequest $request
     * @return mixed
     */
    public function index(SearchRequest $request)
    {
        $expenses =
            (new ExpenseFilter($this->expense_repo))->filter($request, auth()->user()->account_user()->account_id);
        return response()->json($expenses);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     *
     *
     */
    public function show(int $id)
    {
        $expense = $this->expense_repo->findExpenseById($id);
        return response()->json($this->transformExpense($expense));
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateExpenseRequest $request
     * @param int $id
     * @return mixed
     */
    public function update(UpdateExpenseRequest $request, int $id)
    {
        $expense = $this->expense_repo->findExpenseById($id);

        if ($this->entityIsDeleted($expense)) {
            return $this->disallowUpdate();
        }

        $expense = $this->expense_repo->save($request->all(), $expense);

        return response()->json($expense->fresh());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     *
     *
     */
    public function store(CreateExpenseRequest $request)
    {
        $expense = $this->expense_repo->save($request->all(),
            ExpenseFactory::create(auth()->user()->account_user()->id, auth()->user()->account_id));

        return response()->json($expense);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     *
     */
    public function destroy(int $id)
    {
        $expense = Expense::withTrashed()->where('id', '=', $id)->first();
        $this->expense_repo->newDelete($expense);
        return response()->json([], 200);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id)
    {
        $group = Expense::withTrashed()->where('id', '=', $id)->first();
        $this->expense_repo->restore($group);
        return response()->json([], 200);
    }

    /**
     * @param $id
     *
     * @return RedirectResponse
     * @throws Exception
     */
    public function archive(int $id)
    {
        $expense = $this->expense_repo->findExpenseById($id);
        $expenseRepo = new ExpenseRepository($expense);
        $expenseRepo->archive($expense);
    }

    /**
     * Perform bulk actions on the list view
     *
     * @param BulkVendorRequest $request
     * @return Response
     *
     */
    public function bulk()
    {
        $action = request()->input('action');

        $ids = request()->input('ids');
        $expenses = Expense::withTrashed()->find($ids);

        $expenses->each(function ($expense, $key) use ($action) {
            $this->expense_repo->{$action}($expense);
        });

        return response()->json(Expense::withTrashed()->whereIn('id', $ids));
    }
}
