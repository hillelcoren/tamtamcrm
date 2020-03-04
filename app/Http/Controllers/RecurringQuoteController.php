<?php

namespace App\Http\Controllers;

use App\Factory\CloneRecurringQuoteFactory;
use App\Factory\CloneRecurringQuoteToQuoteFactory;
use App\Factory\RecurringQuoteFactory;
use App\Filters\RecurringInvoiceFilter;
use App\Filters\RecurringQuoteFilter;
use App\Invoice;
use App\Jobs\Invoice\CreateInvoicePdf;
use App\Jobs\RecurringInvoice\SendRecurring;
use App\RecurringQuote;
use App\Quote;
use App\Repositories\BaseRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\QuoteRepository;
use App\Repositories\RecurringQuoteRepository;
use App\Requests\SearchRequest;
use App\Requests\RecurringQuote\StoreRecurringQuoteRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\CheckEntityStatus;

/**
 * Class RecurringQuoteController
 * @package App\Http\Controllers\RecurringQuoteController
 */
class RecurringQuoteController extends Controller
{
    use CheckEntityStatus;

    /**
     * @var RecurringQuoteRepository
     */
    protected $recurring_quote_repo;

    /**
     * RecurringQuoteController constructor.
     *
     * @param RecurringQuoteRepository $recurring_quote_repo The RecurringQuote repo
     */
    public function __construct(RecurringQuoteRepository $recurring_quote_repo)
    {
        $this->recurring_quote_repo = $recurring_quote_repo;
    }

    /**
     * @param SearchRequest $request
     * @return mixed
     */
    public function index(SearchRequest $request)
    {

        $invoices = (new RecurringQuoteFilter($this->recurring_quote_repo))->filter($request,
            auth()->user()->account_user()->account_id);
        return response()->json($invoices);
    }

    /**
     * @param StoreRecurringQuoteRequest $request
     * @return mixed
     * @throws Exception
     */
    public function store(StoreRecurringQuoteRequest $request)
    {

        $quote = (new QuoteRepository(new Quote()))->findQuoteById($request->quote_id);

        $arrRecurring = array_merge(array(
            'total' => $quote->total,
            'sub_total' => $quote->sub_total,
            'tax_total' => $quote->tax_total,
            'discount_total' => $quote->discount_total,
            'date' => $quote->date,
            'due_date' => $quote->due_date,
            'line_items' => $quote->line_items,
            'footer' => $quote->footer,
            'notes' => $quote->notes,
            'terms' => $quote->terms,
            'total' => $quote->total,
            'partial' => $quote->partial
        ), $request->all());

        $recurringQuote = (new RecurringQuoteRepository(new RecurringQuote))->save($arrRecurring,
            RecurringQuoteFactory::create($request->customer_id, auth()->user()->account_user()->account_id,
                $quote->total));
        return response()->json($recurringQuote);
    }

    /**
     * @param int $id
     * @param StoreRecurringQuoteRequest $request
     * @return mixed
     */
    public function update(int $id, StoreRecurringQuoteRequest $request)
    {
        $recurring_quote = $this->recurring_quote_repo->findQuoteById($id);

        if ($this->entityIsDeleted($recurring_quote)) {
            return $this->disallowUpdate();
        }

        $recurring_quote = $this->recurring_quote_repo->save($request->all(), $recurring_quote);
        return response()->json($recurring_quote);
    }


    public function bulk()
    {
        $action = request()->input('action');

        $ids = request()->input('ids');
        $recurring_quotes = RecurringQuote::withTrashed()->find($this->transformKeys($ids));
        $recurring_quotes->each(function ($recurring_quote, $key) use ($action) {
            $this->recurring_quote_repo->{$action}($recurring_quote);
        });
        return $this->listResponse(RecurringQuote::withTrashed()->whereIn('id', $this->transformKeys($ids)));

    }

    /**
     * @param int $id
     * @return mixed
     */
    public function archive(int $id)
    {
        $invoice = $this->recurring_quote_repo->findQuoteById($id);
        $this->recurring_quote_repo->archive($invoice);
        return response()->json([], 200);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function destroy(int $id)
    {
        $quote = RecurringQuote::withTrashed()->where('id', '=', $id)->first();
        $this->recurring_quote_repo->newDelete($quote);
        return response()->json([], 200);
    }

    /**
     * @param ActionRecurringQuoteRequest $request
     * @param RecurringQuote $recurring_quote
     * @param $action
     */
    public function action(ActionRecurringQuoteRequest $request, RecurringQuote $recurring_quote, $action)
    {

        switch ($action) {
            case 'clone_to_RecurringQuote':
                //      $recurring_invoice = CloneRecurringQuoteFactory::create($recurring_invoice, auth()->user()->id);
                //      return $this->itemResponse($recurring_invoice);
                break;
            case 'clone_to_quote':
                //    $quote = CloneRecurringQuoteToQuoteFactory::create($recurring_invoice, auth()->user()->id);
                // todo build the quote transformer and return response here
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
            case 'archive':
                # code...
                break;
            case 'delete':
                # code...
                break;
            case 'email':
                //dispatch email to queue
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id)
    {
        $group = RecurringQuote::withTrashed()->where('id', '=', $id)->first();
        $this->recurring_quote_repo->restore($group);
        return response()->json([], 200);
    }

}
