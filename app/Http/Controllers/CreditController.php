<?php

namespace App\Http\Controllers;

use App\Factory\CloneCreditFactory;
use App\Factory\CloneCreditToQuoteFactory;
use App\Filters\CreditFilter;
use App\Jobs\Credit\EmailCredit;
use App\Jobs\Invoice\MarkInvoicePaid;
use App\Quote;
use App\Repositories\QuoteRepository;
use App\Requests\Credit\ActionCreditRequest;
use App\Requests\Credit\CreateCreditRequest;
use App\Requests\Credit\UpdateCreditRequest;
use App\Customer;
use App\Credit;
use App\Repositories\CreditRepository;
use App\Services\CreditService;
use App\Repositories\Interfaces\CreditRepositoryInterface;
use App\Requests\SearchRequest;
use App\Transformations\CreditTransformable;
use App\Factory\CreditFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Traits\CheckEntityStatus;
use Illuminate\Support\Facades\Storage;

class CreditController extends Controller
{
    use CreditTransformable;
    use CheckEntityStatus;

    protected $credit_repo;

    /**
     * CreditController constructor.
     * @param CreditRepositoryInterface $credit_repo
     */
    public function __construct(CreditRepositoryInterface $credit_repo)
    {
        $this->credit_repo = $credit_repo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(SearchRequest $request)
    {
        $credits = (new CreditFilter($this->credit_repo))->filter($request, auth()->user()->account_user()->account_id);

        return response()->json($credits);
    }

    /**
     * @param UpdateCreditRequest $request
     * @param int $id
     * @return mixed
     */
    public function update(UpdateCreditRequest $request, int $id)
    {
        $credit = $this->credit_repo->findCreditById($id);

        if ($this->entityIsDeleted($credit)) {
            return $this->disallowUpdate();
        }

        $credit = $this->credit_repo->save($request->all(), $credit);
        return response()->json($credit);
    }

    /**
     * @param CreateCreditRequest $request
     * @return mixed
     */
    public function store(CreateCreditRequest $request)
    {
        $customer = Customer::find($request->input('customer_id'));
        $credit = $this->credit_repo->save($request->all(),
            CreditFactory::create(auth()->user()->account_user()->account_id, auth()->user()->id, $request->total,
                $customer, $customer->getMergedSettings()));
        return response()->json($this->transformCredit($credit));
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function archive(int $id)
    {
        $invoice = $this->credit_repo->findCreditById($id);
        $this->credit_repo->archive($invoice);
        return response()->json([], 200);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function destroy(int $id)
    {
        $credit = Credit::withTrashed()->where('id', '=', $id)->first();
        $this->credit_repo->newDelete($credit);
        return response()->json([], 200);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id)
    {
        $group = Credit::withTrashed()->where('id', '=', $id)->first();
        $this->credit_repo->restore($group);
        return response()->json([], 200);
    }

    public function show(int $id)
    {
        $credit = $this->credit_repo->findCreditById($id);
        return response()->json($this->transformCredit($credit));
    }

    public function bulk()
    {
        $action = request()->input('action');

        $ids = request()->input('ids');

        $credits = Credit::withTrashed()->whereIn('id', $ids);

        if (!$credits) {
            return response()->json(['message' => 'No Credits Found']);
        }

        $credits->each(function ($credit, $key) use ($action) {
            $this->performAction($credit, request(), $action, true);
        });

        return response()->json(Credit::withTrashed()->whereIn('id', $ids));
    }

    /**
     * @param Request $request
     * @param Credit $credit
     * @param $action
     * @return mixed
     */
    public function action(Request $request, Credit $credit, $action)
    {
        return $this->performAction($credit, $request, $action);
    }

    /**
     * @param Credit $credit
     * @param $action
     * @param bool $bulk
     * @return mixed
     */
    private function performAction(Credit $credit, Request $request, $action, $bulk = false)
    {
        /*If we are using bulk actions, we don't want to return anything */
        switch ($action) {
            case 'clone_to_credit':
                $credit = CloneCreditFactory::create($credit, auth()->user()->id);
                $this->credit_repo->save($request->all(), $credit);
                return response()->json($this->transformCredit($credit));
                break;
            case 'clone_to_quote':
                $quote = CloneCreditToQuoteFactory::create($credit, auth()->user()->id);
                (new QuoteRepository(new Quote))->save($request->all(), $quote);
                // todo build the quote transformer and return response here
                break;
            case 'history':
                # code...
                break;
            case 'delivery_note':
                # code...
                break;
            case 'mark_paid':
                if ($credit->balance < 0 || $credit->status_id == Credit::STATUS_PAID || $credit->is_deleted === true) {
                    return response()->json('Credit cannot be marked as paid', 400);
                }

                $credit = MarkInvoicePaid::dispatchNow($credit, $credit->account);

                if (!$bulk) {
                    return $this->itemResponse($credit);
                }
                break;
            case 'mark_sent':
                $credit->markSent();

                if (!$bulk) {
                    return $this->itemResponse($credit);
                }
                break;
            case 'download':
                $disk = config('filesystems.default');
                $content = Storage::disk($disk)->get($credit->service()->getCreditPdf(null));
                return response()->json(['data' => base64_encode($content)]);
                break;
            case 'archive':
                $this->credit_repo->archive($credit);

                if (!$bulk) {
                    return $this->listResponse($credit);
                }
                break;
            case 'delete':
                $this->credit_repo->delete($credit);

                if (!$bulk) {
                    return $this->listResponse($credit);
                }
                break;
            case 'email':
                EmailCredit::dispatch($credit, $credit->account);
                if (!$bulk) {
                    return response()->json(['message' => 'email sent'], 200);
                }
                break;

            default:
                return response()->json(['message' => "The requested action `{$action}` is not available."], 400);
                break;
        }
    }
}
