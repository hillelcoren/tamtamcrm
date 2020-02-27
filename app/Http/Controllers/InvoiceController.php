<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Factory\CloneInvoiceFactory;
use App\Jobs\Invoice\ZipInvoices;
use App\Factory\CloneInvoiceToQuoteFactory;
use App\Factory\NotificationFactory;
use App\Jobs\Order\InvoiceOrders;
use App\Jobs\RecurringInvoice\SaveRecurringInvoice;
use App\Notification;
use App\Quote;
use App\Repositories\NotificationRepository;
use App\Repositories\QuoteRepository;
use Exception;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use App\Transformations\InvoiceTransformable;
use App\Invoice;
use App\Requests\SearchRequest;
use App\Requests\Invoice\CreateInvoiceRequest;
use App\Jobs\Invoice\MarkInvoicePaid;
use App\Factory\InvoiceFactory;
use App\Jobs\Invoice\StoreInvoice;
use App\Events\Invoice\InvoiceWasUpdated;
use App\Filters\InvoiceFilter;
use App\Repositories\TaskRepository;
use App\Task;
use App\Traits\CheckEntityStatus;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{

    use InvoiceTransformable, CheckEntityStatus;

    private $invoiceRepository;
    private $invoiceLineRepository;

    /**
     * InvoiceController constructor.
     * @param InvoiceRepositoryInterface $invoice_repo
     */
    public function __construct(InvoiceRepositoryInterface $invoice_repo)
    {
        $this->invoice_repo = $invoice_repo;
    }

    /**
     *
     * @param Request $request
     * @return type
     */
    public function index(SearchRequest $request)
    {
        $invoices =
            (new InvoiceFilter($this->invoice_repo))->filter($request, auth()->user()->account_user()->account_id);
        return response()->json($invoices);
    }

    public function getInvoicesByStatus(int $status)
    {
        $invoices = $this->invoice_repo->findInvoicesByStatus($status);
        return response()->json($invoices);
    }

    /**
     * @param CreateInvoiceRequest $request
     * @return string
     */
    public function store(CreateInvoiceRequest $request)
    {
        $customer = Customer::find($request->input('customer_id'));

        $invoice = $this->invoice_repo->save($request->all(),
            InvoiceFactory::create(auth()->user()->id, auth()->user()->account_user()->account_id, $customer,
                $request->total, $customer->getMergedSettings()));
        $invoice = StoreInvoice::dispatchNow($invoice, $request->all(),
            $invoice->account); //todo potentially this may return mixed ie PDF/$invoice... need to revisit when we implement UI
        InvoiceOrders::dispatchNow($invoice);
        $notification = NotificationFactory::create(auth()->user()->account_user()->account_id, auth()->user()->id);
        (new NotificationRepository(new Notification))->save($notification, [
            'data' => json_encode(['id' => $invoice->id, 'message' => 'A new invoice was created']),
            'type' => 'App\Notifications\InvoiceCreated'
        ]);


        SaveRecurringInvoice::dispatchNow($request, $invoice->account, $invoice);

        $invoiceTransformed = $this->transformInvoice($invoice);
        return $invoiceTransformed->toJson();
    }

    /**
     * @param int $invoice_id
     * @return mixed
     */
    public function show(int $invoice_id)
    {

        $invoice = $this->invoice_repo->findInvoiceById($invoice_id);
        return response()->json($this->transformInvoice($invoice));
    }

    /**
     * @param int $task_id
     * @return mixed
     * @throws Exception
     */
    public function getInvoiceLinesForTask(int $task_id)
    {
        $task = (new TaskRepository(new Task))->findTaskById($task_id);
        $invoice = $this->invoice_repo->getInvoiceForTask($task);

        if (!$invoice->count()) {
            return response()->json('empty');
        }

        $arrTest = [
            'lines' => $invoice->line_items,
            'invoice' => $invoice
        ];

        return response()->json($arrTest);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return mixed
     */
    public function update(int $id, Request $request)
    {
        $invoice = $this->invoice_repo->findInvoiceById($id);

        if ($this->entityIsDeleted($invoice)) {
            return $this->disallowUpdate();
        }

        $invoice = $this->invoice_repo->save($request->all(), $invoice);
        //SaveRecurringInvoice::dispatchNow($request, $invoice->account);
        InvoiceOrders::dispatchNow($invoice);
        event(new InvoiceWasUpdated($invoice, $invoice->account));
        $invoiceTransformed = $this->transformInvoice($invoice);
        return $invoiceTransformed->toJson();
    }

    public function action(Request $request, Invoice $invoice, $action)
    {
        return $this->performAction($invoice, $request, $action);
    }

    private function performAction(Invoice $invoice, Request $request, $action, $bulk = false)
    {
        switch ($action) {
            case 'clone_to_invoice':
                $invoice = CloneInvoiceFactory::create($invoice, auth()->user()->id,
                    auth()->user()->account_user()->account_id);
                $this->invoice_repo->save($request->all(), $invoice);
                return response()->json($invoice);
                break;
            case 'clone_to_quote':
                $quote = CloneInvoiceToQuoteFactory::create($invoice, auth()->user()->id);
                (new QuoteRepository(new Quote))->save($request->all(), $quote);
                // todo build the quote transformer and return response here
                break;
            case 'history':
                break;
            case 'delivery_note':
                # code...
                break;
            case 'mark_paid':
                if ($invoice->balance < 0 || $invoice->status_id == Invoice::STATUS_PAID ||
                    $invoice->is_deleted === true) {
                    return response()->json('Invoice cannot be marked as paid', 400);
                }
                $invoice = $invoice->service()->markPaid();
                if (!$bulk) {
                    return response()->json($invoice);
                }
                break;
            case 'mark_sent':
                $invoice->service()->markSent();
                if (!$bulk) {
                    return response()->json($invoice);
                }
                break;
            case 'download':
                $disk = config('filesystems.default');
                $content = Storage::disk($disk)->get($invoice->service()->getInvoicePdf(null));
                return response()->json(['data' => base64_encode($content)]);
                break;
            case 'archive':
                $this->invoice_repo->archive($invoice);
                if (!$bulk) {
                    return response()->json($invoice);
                }
                break;
            case 'delete':
                $this->invoice_repo->newDelete($invoice);
                if (!$bulk) {
                    return response()->json($invoice);
                }
                break;
            case 'email':
                $invoice->service()->sendEmail(null);
                if (!$bulk) {
                    return response()->json(['message' => 'email sent'], 200);
                }
                break;
            default:
                return response()->json(['message' => "The requested action `{$action}` is not available."], 400);
                break;
        }
    }

    public function downloadPdf($invitation_key)
    {
        $invitation = $this->invoice_repo->getInvitationByKey($invitation_key);
        $contact = $invitation->contact;
        $invoice = $invitation->invoice;

        $file_path = $invoice->service()->getInvoicePdf();

        return response()->download($file_path);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function archive(int $id)
    {
        $invoice = $this->invoice_repo->findInvoiceById($id);
        $this->invoice_repo->archive($invoice);
        return response()->json([], 200);
    }

    public function destroy(int $id)
    {
        $invoice = Invoice::withTrashed()->where('id', '=', $id)->first();
        $this->invoice_repo->newDelete($invoice);
        return response()->json([], 200);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id)
    {
        $invoice = Invoice::withTrashed()->where('id', '=', $id)->first();
        $this->invoice_repo->restore($invoice);
        return response()->json([], 200);
    }

    public function bulk()
    {

        /*
         * WIP!
         */
        $action = request()->input('action');

        $ids = request()->input('ids');

        $invoices = Invoice::withTrashed()->whereIn('id', $ids)->get();

        if (!$invoices) {
            return response()->json(['message' => 'No Invoices Found']);
        }


        if ($action == 'download' && $invoices->count() > 1) {

            /* $invoices->each(function ($invoice) {

                if(auth()->user()->cannot('view', $invoice)){
                    return response()->json(['message'=>'Insufficient privileges to access invoice '. $invoice->number]);
                }

            }); */

            ZipInvoices::dispatch($invoices, $invoices->first()->account, auth()->user()->email);

            return response()->json(['message' => 'Email Sent!'], 200);
        }


        $invoices->each(function ($invoice, $key) use ($action) {
            $this->performAction($invoice, $action, true);
        });

        /* Need to understand which permission are required for the given bulk action ie. view / edit */

        return $this->response->json(Invoice::withTrashed()->whereIn('id', $ids));
    }
}
