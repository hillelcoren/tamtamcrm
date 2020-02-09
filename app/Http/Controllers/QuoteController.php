<?php

namespace App\Http\Controllers;

use App\Factory\CloneQuoteFactory;
use App\Factory\NotificationFactory;
use App\Jobs\Order\QuoteOrders;
use App\Jobs\RecurringQuote\SaveRecurringQuote;
use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use App\Notification;
use App\Repositories\NotificationRepository;
use App\Repositories\TaskRepository;
use App\Requests\Quote\CreateQuoteRequest;
use App\Requests\SearchRequest;
use App\Task;
use Carbon\Carbon;
use App\Factory\QuoteFactory;
use App\Transformations\QuoteTransformable;
use Illuminate\Support\Facades\Auth;
use App\Event;
use App\RecurringQuote;
use App\Factory\RecurringQuoteFactory;
use App\Services\Interfaces\InvoiceServiceInterface;
use App\Quote;
use App\Repositories\QuoteRepository;
use App\Repositories\Interfaces\QuoteRepositoryInterface;
use App\Repositories\Interfaces\InvoiceLineRepositoryInterface;
use Illuminate\Http\Request;
use App\Filters\QuoteFilter;
use App\Repositories\RecurringQuoteRepository;
use App\Factory\CloneQuoteToInvoiceFactory;
use App\Traits\CheckEntityStatus;

/**
 * Class QuoteController
 * @package App\Http\Controllers
 */
class QuoteController extends Controller
{

    use QuoteTransformable,
        CheckEntityStatus;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoice_repo;

    /**
     * @var QuoteRepositoryInterface
     */
    private $quote_repo;

    /**
     * QuoteController constructor.
     * @param InvoiceRepositoryInterface $invoice_repo
     * @param QuoteRepositoryInterface $quote_repo
     */
    public function __construct(
        InvoiceRepositoryInterface $invoice_repo,
        QuoteRepositoryInterface $quote_repo
    ) {
        $this->invoice_repo = $invoice_repo;
        $this->quote_repo = $quote_repo;
    }

    /**
     * @param SearchRequest $request
     * @return mixed
     */
    public function index(SearchRequest $request)
    {
        $invoices = (new QuoteFilter($this->quote_repo))->filter($request, auth()->user()->account_user()->account_id);
        return response()->json($invoices);
    }

    /**
     * @param int $quote_id
     * @return mixed
     */
    public function show(int $quote_id)
    {
        $invoice = $this->quote_repo->findQuoteById($quote_id)->first();
        return response()->json($invoice);
    }

    /**
     * @param CreateQuoteRequest $request
     * @return mixed
     */
    public function store(CreateQuoteRequest $request)
    {
        $quote = $this->quote_repo->save($request->all(),
            QuoteFactory::create($request->customer_id, auth()->user()->account_user()->account_id, auth()->user()->id,
                $request->total));
        SaveRecurringQuote::dispatchNow($request, $quote->account, $quote);
        QuoteOrders::dispatchNow($quote);
        $notification = NotificationFactory::create(auth()->user()->account_user()->account_id, auth()->user()->id);
        (new NotificationRepository(new \App\Notification))->save($notification, [
            'data' => json_encode(['id' => $quote->id, 'message' => 'A new quote was created']),
            'type' => 'App\Notifications\QuoteCreated'
        ]);
        $invoiceTransformed = $this->transformQuote($quote);
        return $invoiceTransformed->toJson();
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $quote = $this->quote_repo->findQuoteById($id);

        if ($this->entityIsDeleted($quote)) {
            return $this->disallowUpdate();
        }

        $quote = $this->quote_repo->save($request->all(), $quote);
        //SaveRecurringQuote::dispatchNow($request, $quote->account);
        QuoteOrders::dispatchNow($quote);
        $invoiceTransformed = $this->transformQuote($quote);
        return $invoiceTransformed->toJson();
    }

    /**
     * @param Request $request
     * @param Quote $quote
     * @param $action
     * @return mixed
     */
    public function action(Request $request, Quote $quote, $action)
    {

        switch ($action) {
            case 'clone_to_invoice':
                $this->invoice_repo->save($request->all(),
                    (new CloneQuoteToInvoiceFactory)->create($this->quote_repo->findQuoteById($quote->id),
                        auth()->user()->id, auth()->user()->account_user()->account_id));
                return response()->json($quote);
                break;
            case 'clone_to_quote':
                $quote = CloneQuoteFactory::create($quote, auth()->user()->id);
                $this->quote_repo->save($request->all(), $quote);
                return response()->json($quote);
                break;
            case 'history':
                # code...
                break;
            case 'delivery_note':
                # code...
                break;
            case 'mark_paid':
                # code...
                break;
            case 'download':
                return response()->download(public_path($quote->pdf_file_path()));
                break;
            case 'archive':
                $this->invoice_repo->archive($quote);
                return response()->json($quote);
                break;
            case 'delete':
                $this->quote_repo->newDelete($quote);
                return response()->json($quote);
                break;
            case 'email':
                return response()->json(['message' => 'email sent'], 200);
                break;
            default:
                return response()->json(['message' => "The requested action `{$action}` is not available."], 400);
                break;

        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function filterQuotes(Request $request)
    {
        $quotes = (new QuoteFilter($this->quote_repo))->filterBySearchCriteria($request->all(),
            auth()->user()->account_user()->account_id);
        return response()->json($quotes);
    }

    /**
     * @param int $task_id
     * @return mixed
     * @throws \Exception
     */
    public function getQuoteLinesForTask(int $task_id)
    {
        $task = (new TaskRepository(new Task))->findTaskById($task_id);
        $quote = $this->quote_repo->getQuoteForTask($task);

        if (!$quote->count()) {
            return response()->json('empty');
        }

        $arrTest = [
            'lines' => $quote->line_items,
            'invoice' => $quote
        ];

        return response()->json($arrTest);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function archive(int $id)
    {
        $invoice = $this->quote_repo->findQuoteById($id);
        $this->quote_repo->archive($invoice);
        return response()->json([], 200);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function destroy(int $id)
    {
        $quote = Quote::withTrashed()->where('id', '=', $id)->first();
        $this->quote_repo->newDelete($quote);
        return response()->json([], 200);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id) {
        $invoice = Quote::withTrashed()->where('id', '=', $id)->first();
        $this->quote_repo->restore($invoice);
        return response()->json([], 200);
    }

    public function bulk()
    {
        $action = request()->input('action');

        $ids = request()->input('ids');
        $quotes = Quote::withTrashed()->find($ids);
        $quotes->each(function ($quote, $key) use ($action) {
            $this->quote_repo->{$action}($quote);
        });
        return response()->json(Quote::withTrashed()->whereIn('id', $ids));
    }
}