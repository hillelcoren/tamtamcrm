<?php

namespace App\Http\Controllers;

use App\Factory\NotificationFactory;
use App\Repositories\NotificationRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Requests\Payment\CreatePaymentRequest;
use App\Requests\Payment\RefundPaymentRequest;
use App\Requests\Payment\UpdatePaymentRequest;
use App\Http\Controllers\Controller;
use App\Requests\SearchRequest;
use App\Transformations\PaymentTransformable;
use App\Payment;
use App\Filters\PaymentFilter;
use App\Services\Interfaces\PaymentServiceInterface;
use App\Mailers\ContactMailer;
use App\Factory\PaymentFactory;
use App\Jobs\Invoice\ReverseInvoicePayment;
use Illuminate\Http\Request;
use App\Notifications\PaymentCreated;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use App\Traits\CheckEntityStatus;

class PaymentController extends Controller
{

    use PaymentTransformable, CheckEntityStatus;

    /**
     * @var PaymentRepositoryInterface
     */
    private $payment_repo;

    /**
     * PaymentController constructor.
     * @param PaymentRepositoryInterface $payment_repo
     */
    public function __construct(PaymentRepositoryInterface $payment_repo)
    {
        $this->payment_repo = $payment_repo;
    }

    /**
     * @param SearchRequest $request
     * @return mixed
     */
    public function index(SearchRequest $request)
    {
        $payments =
            (new PaymentFilter($this->payment_repo))->filter($request, auth()->user()->account_user()->account_id);
        return response()->json($payments);
    }

    /**
     * Store a newly created resource in storage.
     * @param CreatePaymentRequest $request
     * @return mixed
     */
    public function store(CreatePaymentRequest $request)
    {
        $payment = $this->payment_repo->save($request->all(),
            PaymentFactory::create($request->customer_id, auth()->user()->id,
                auth()->user()->account_user()->account_id));

        $notification = NotificationFactory::create(auth()->user()->account_user()->account_id, auth()->user()->id);
        (new NotificationRepository(new \App\Notification))->save($notification, [
            'data' => json_encode(['id' => $payment->id, 'message' => 'A new payment was created']),
            'type' => 'App\Notifications\PaymentCreated'
        ]);

        return response()->json($this->transformPayment($payment));
    }

    public function show(int $id)
    {
        $payment = $this->payment_repo->findPaymentById($id);
        return response()->json($this->transformPayment($payment));
    }

    /**
     * Update the specified resource in storage.
     * @param UpdatePaymentRequest $request
     * @param $id
     * @return mixed
     */
    public function update(UpdatePaymentRequest $request, $id)
    {
        $payment = $this->payment_repo->findPaymentById($id);

        if ($this->entityIsDeleted($payment)) {
            return $this->disallowUpdate();
        }

        $payment = (new PaymentRepository($payment))->save($request->all(), $payment);
        return response()->json($this->transformPayment($payment));
    }

    public function archive(int $id)
    {
        $payment = Payment::withTrashed()->where('id', '=', $id)->first();
        $this->payment_repo->archive($payment);
        return response()->json([], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy(int $id)
    {
        $payment = $this->payment_repo->findPaymentById($id);
        $payment->service()->reversePayment();

        $payment->is_deleted = true;
        $payment->save();
        $payment->delete();
        return response()->json('deleted');
    }

    public function bulk()
    {
        $action = request()->input('action');

        $ids = request()->input('ids');
        $payments = Payment::withTrashed()->find($ids);
        $payments->each(function ($payment, $key) use ($action) {
            $this->payment_repo->{$action}($payment);
        });
        return response()->json(Payment::withTrashed()->whereIn('id', $ids));
    }

    /**
     * @param Request $request
     * @param Payment $payment
     * @param $action
     */
    public function action(Request $request, Payment $payment, $action)
    {
        switch ($action) {
            case 'clone_to_invoice':
                //$payment = CloneInvoiceFactory::create($payment, auth()->user()->id);
                //return $this->itemResponse($payment);
                break;
            case 'clone_to_quote':
                //$quote = CloneInvoiceToQuoteFactory::create($payment, auth()->user()->id);
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
                $payment->service()->sendEmail();
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * @param int $payment_id
     * @param RefundRequest $request
     * @return mixed
     */
    public function refund(RefundPaymentRequest $request)
    {
        $payment = $request->payment();

        $payment->refund($request->all());

        return response()->json($payment);
    }


    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id)
    {
        $group = Payment::withTrashed()->where('id', '=', $id)->first();
        $this->payment_repo->restore($group);
        return response()->json([], 200);
    }

}
