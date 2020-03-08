<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Factory\CloneInvoiceFactory;
use App\Factory\CloneQuoteFactory;
use App\Factory\NotificationFactory;
use App\Jobs\Invoice\ZipInvoices;
use App\Jobs\Order\QuoteOrders;
use App\Jobs\RecurringQuote\SaveRecurringQuote;
use App\Repositories\Interfaces\InvoiceRepositoryInterface;
use App\Notification;
use App\Repositories\NotificationRepository;
use App\Repositories\TaskRepository;
use App\Requests\Quote\CreateQuoteRequest;
use App\Requests\SearchRequest;
use App\Task;
use App\Transformations\InvoiceTransformable;
use Carbon\Carbon;
use App\Factory\QuoteFactory;
use App\Transformations\QuoteTransformable;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Class QuoteController
 * @package App\Http\Controllers
 */
class QuoteController extends Controller
{

    use QuoteTransformable, CheckEntityStatus, InvoiceTransformable;

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
    public function __construct(InvoiceRepositoryInterface $invoice_repo, QuoteRepositoryInterface $quote_repo)
    {
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
        $invoice = $this->quote_repo->findQuoteById($quote_id);
        return response()->json($invoice);
    }

    /**
     * @param CreateQuoteRequest $request
     * @return mixed
     */
    public function store(CreateQuoteRequest $request)
    {
        $customer = Customer::find($request->input('customer_id'));
        $data = $customer->setCompanyDefaults($request->all(), 'invoice');
        $quote = $this->quote_repo->save($data,
            QuoteFactory::create(auth()->user()->account_user()->account_id, auth()->user()->id, $customer));
        SaveRecurringQuote::dispatchNow($request, $quote->account, $quote);
        QuoteOrders::dispatchNow($quote);
        $notification = NotificationFactory::create(auth()->user()->account_user()->account_id, auth()->user()->id);
        (new NotificationRepository(new Notification))->save($notification, [
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
     * @return JsonResponse
     * @throws FileNotFoundException
     */
    public function action(Request $request, Quote $quote, $action)
    {
        return $this->performAction($request, $quote, $action);
    }

    /**
     * @param Request $request
     * @param Quote $quote
     * @param $action
     * @param bool $bulk
     * @return JsonResponse
     * @throws FileNotFoundException
     */
    public function performAction(Request $request, Quote $quote, $action, $bulk = false)
    {
        switch ($action) {
            case 'clone_to_invoice':
                $invoice = $this->invoice_repo->save($request->all(),
                    CloneQuoteToInvoiceFactory::create($this->quote_repo->findQuoteById($quote->id), auth()->user()->id,
                        auth()->user()->account_user()->account_id));
                return response()->json($this->transformInvoice($invoice));
                break;
            case 'clone_to_quote':
                $quote = CloneQuoteFactory::create($quote, auth()->user()->id);
                $this->quote_repo->save($request->all(), $quote);
                return response()->json($this->transformQuote($quote));
                break;
            case 'history':
                # code...
                break;
            case 'delivery_note':
                # code...
                break;
            case 'mark_sent':
                $quote = $quote->service()->markSent();

                if (!$bulk) {
                    return response()->json($quote);
                }

                break;
            case 'approve':
                if($quote->status_id != Quote::STATUS_SENT)
                     return response()->json(['message' => 'Unable to approve this quote as it has expired.'], 400);

                 return response()->json($quote->service()->approve()->save());
                break;
            case 'convert':
                //convert  quote to an invoice make sure we link the two entities!!!
                break;
            case
            'mark_approved':
                $quote = $quote->service()->markApproved($this->invoice_repo);
                return response()->json($quote);
                break;
            case 'download':
                $disk = config('filesystems.default');
                $content = Storage::disk($disk)->get($quote->service()->getQuotePdf(null));
                return response()->json(['data' => base64_encode($content)]);
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
                $quote->service()->sendEmail();
                return response()->json(['message' => 'email sent'], 200);
                break;
            default:
                return response()->json(['message' => "The requested action `{$action}` is not available."], 400);
                break;
        }
    }

    /**
     * @param int $task_id
     * @return mixed
     * @throws Exception
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
    public function restore(int $id)
    {
        $invoice = Quote::withTrashed()->where('id', '=', $id)->first();
        $this->quote_repo->restore($invoice);
        return response()->json([], 200);
    }

    public function bulk(Request $request)
    {
        $action = request()->input('action');

        $ids = request()->input('ids');

        $quotes = Quote::withTrashed()->whereIn('id', $ids)->get();

        if (!$quotes) {
            return response()->json(['message' => 'No Quotes Found']);
        }

        /*
         * Download Invoice/s
         */

        if ($action == 'download' && $quotes->count() > 1) {
            /*$quotes->each(function ($quote) {

                if (auth()->user()->cannot('view', $quote)) {
                    return response()->json(['message' => 'Insufficient privileges to access quote ' . $quote->number]);
                }
            });*/

            ZipInvoices::dispatch($quotes, $quotes->first()->account, auth()->user()->email);

            return response()->json(['message' => 'Email Sent!'], 200);
        }

        /*
           * Send the other actions to the switch
           */
        $quotes->each(function ($quote, $key) use ($action, $request) {
            $this->performAction($request, $quote, $action, true);
        });

        /* Need to understand which permission are required for the given bulk action ie. view / edit */
        return response()->json(Quote::withTrashed()->whereIn('id', $ids)->get());
    }

    public function downloadPdf($invitation_key)
    {
        $invitation = $this->quote_repo->getInvitationByKey($invitation_key);
        $contact = $invitation->contact;
        $quote = $invitation->quote;

        $file_path = $quote->service()->getQuotePdf($contact);

        return response()->download($file_path);

    }
}
