<?php

namespace App\Http\Controllers;

use App\Factory\CloneRecurringInvoiceFactory;
use App\Factory\CloneRecurringInvoiceToQuoteFactory;
use App\Factory\RecurringInvoiceFactory;
use App\Filters\InvoiceFilter;
use App\RecurringInvoice;
use App\Repositories\RecurringInvoiceRepository;
use App\Requests\RecurringInvoice\StoreRecurringInvoiceRequest;
use App\Requests\SearchRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Filters\RecurringInvoiceFilter;
use App\Repositories\InvoiceRepository;
use App\Invoice;
use App\Traits\CheckEntityStatus;

/**
 * Class RecurringInvoiceController
 * @package App\Http\Controllers\RecurringInvoiceController
 */
class RecurringInvoiceController extends Controller
{
    use CheckEntityStatus;

    /**
     * @var RecurringInvoiceRepository
     */
    protected $recurring_invoice_repo;

    /**
     * RecurringInvoiceController constructor.
     *
     * @param RecurringInvoiceRepository $recurring_invoice_repo The RecurringInvoice repo
     */
    public function __construct(RecurringInvoiceRepository $recurring_invoice_repo)
    {
        $this->recurring_invoice_repo = $recurring_invoice_repo;
    }

    /**
     * @param SearchRequest $request
     * @return mixed
     */
    public function index(SearchRequest $request)
    {
        $invoices = (new RecurringInvoiceFilter($this->recurring_invoice_repo))->filter($request,
            auth()->user()->account_user()->account_id);
        return response()->json($invoices);
    }

    /**
     * @param StoreRecurringInvoiceRequest $request
     * @return mixed
     * @throws Exception
     */
    public function store(StoreRecurringInvoiceRequest $request)
    {
        $invoice = (new InvoiceRepository(new Invoice()))->findInvoiceById($request->invoice_id);

        $arrRecurring = array_merge(array(
            'total' => $invoice->total,
            'sub_total' => $invoice->sub_total,
            'tax_total' => $invoice->tax_total,
            'discount_total' => $invoice->discount_total,
            'date' => $invoice->date,
            'due_date' => $invoice->due_date,
            'line_items' => $invoice->line_items,
            'footer' => $invoice->footer,
            'notes' => $invoice->notes,
            'terms' => $invoice->terms,
            'total' => $invoice->total,
            'partial' => $invoice->partial
        ), $request->all());

        $recurringInvoice = (new RecurringInvoiceRepository(new RecurringInvoice))->save($arrRecurring,
            RecurringInvoiceFactory::create($request->customer_id, auth()->user()->account_user()->account_id,
                $invoice->total));
        return response()->json($recurringInvoice);
    }

    /**
     * @param int $id
     * @param StoreRecurringInvoiceRequest $request
     * @return mixed
     */
    public function update(int $id, StoreRecurringInvoiceRequest $request)
    {
        $recurring_invoice = $this->recurring_invoice_repo->findInvoiceById($id);

        if ($this->entityIsDeleted($recurring_invoice)) {
            return $this->disallowUpdate();
        }

        $recurring_invoice = $this->recurring_invoice_repo->save($request->all(), $recurring_invoice);
        return response()->json($recurring_invoice);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function archive(int $id)
    {
        $invoice = $this->recurring_invoice_repo->findInvoiceById($id);
        $this->recurring_invoice_repo->archive($invoice);
        return response()->json([], 200);
    }

    public function destroy(int $id)
    {
        $recurring_invoice = RecurringInvoice::withTrashed()->where('id', '=', $id)->first();
        $this->recurring_invoice_repo->newDelete($recurring_invoice);
        return response()->json([], 200);
    }

    /**
     * @return mixed
     */
    public function bulk()
    {
        $action = request()->input('action');

        $ids = request()->input('ids');
        $recurring_invoices = RecurringInvoice::withTrashed()->find($ids);
        $recurring_invoices->each(function ($recurring_invoice, $key) use ($action) {
            $this->recurring_invoice_repo->{$action}($recurring_invoice);
        });
        return response()->json(RecurringInvoice::withTrashed()->whereIn('id', $ids));
    }

    /**
     * @param ActionRecurringInvoiceRequest $request
     * @param RecurringInvoice $recurring_invoice
     * @param $action
     */
    public function action(ActionRecurringInvoiceRequest $request, RecurringInvoice $recurring_invoice, $action)
    {
        switch ($action) {
            case 'clone_to_RecurringInvoice':
                //      $recurring_invoice = CloneRecurringInvoiceFactory::create($recurring_invoice, auth()->user()->id);
                //      return $this->itemResponse($recurring_invoice);
                break;
            case 'clone_to_quote':
                //    $recurring_invoice = CloneRecurringInvoiceToQuoteFactory::create($recurring_invoice, auth()->user()->id);
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
        $group = RecurringInvoice::withTrashed()->where('id', '=', $id)->first();
        $this->recurring_invoice_repo->restore($group);
        return response()->json([], 200);
    }
}
