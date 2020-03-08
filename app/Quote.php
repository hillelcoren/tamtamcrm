<?php

namespace App;

use App\Jobs\Invoice\CreateInvoicePdf;
use App\Helpers\Invoice\InvoiceSum;
use App\Helpers\Invoice\InvoiceSumInclusive;
use App\Jobs\Quote\CreateQuotePdf;
use App\Services\Quote\QuoteService;
use App\Traits\MakesInvoiceValues;
use App\Traits\MakesReminders;
use Illuminate\Database\Eloquent\Model;
use App\Task;
use App\InvoiceStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use App\Events\Invoice\QuoteWasMarkedSent;
use App\Jobs\Customer\UpdateClientBalance;
use App\Events\Invoice\InvoiceWasPaid;
use App\InvoiceLine;
use Illuminate\Support\Facades\Storage;
use Laracasts\Presenter\PresentableTrait;

class Quote extends Model
{
    use SoftDeletes;
    use MakesInvoiceValues;
    use PresentableTrait;
    use MakesReminders;
    use MakesInvoiceValues;

    protected $presenter = 'App\Presenters\QuotePresenter';

    protected $casts = [
        'line_items' => 'object',
        'updated_at' => 'timestamp',
        'deleted_at' => 'timestamp',
        'is_deleted' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id',
        'customer_id',
        'total',
        'sub_total',
        'tax_total',
        'tax_rate',
        'tax_rate_name',
        'discount_total',
        'payment_type',
        'due_date',
        'status_id',
        'finance_type',
        'created_at',
        'start_date',
        'end_date',
        'frequency',
        'recurring_due_date',
        'public_notes',
        'private_notes',
        'terms',
        'footer',
        'partial',
        'date',
        'balance',
        'line_items',
        'company_id',
        'task_id',
        'custom_value1',
        'custom_value2',
        'custom_value3',
        'custom_value4',
        'number',
        'invoice_type_id',
        'is_amount_discount',
        'po_number',
        'design_id'
    ];

    const STATUS_DRAFT = 1;
    const STATUS_SENT = 2;
    const STATUS_APPROVED = 4;
    const STATUS_EXPIRED = -1;

    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo
     */
    public function invoiceStatus()
    {
        return $this->belongsTo(InvoiceStatus::class, 'status_id');
    }

    /**
     * @return BelongsTo
     */
    public function paymentType()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_type');
    }

    /**
     * @return mixed
     */
    public function invitations()
    {
        return $this->hasMany(QuoteInvitation::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function markApproved()
    {

        /* Return immediately if status is not draft */
        if ($this->status_id != Quote::STATUS_DRAFT) {
            return $this;
        }

        $this->status_id = Quote::STATUS_APPROVED;
        $this->save();
    }

    /**
     * Updates Invites to SENT
     *
     */
    public function markInvitationsSent()
    {
        $this->invitations->each(function ($invitation) {
            if (!isset($invitation->sent_date)) {
                $invitation->sent_date = Carbon::now();
                $invitation->save();
            }
        });
    }

    public function pdf_file_path($invitation = null)
    {
        $storage_path = 'storage/' . $this->customer->quote_filepath() . $this->number . '.pdf';

        if (Storage::exists($storage_path)) {
            return $storage_path;
        }

        if (!$invitation) {
            CreateQuotePdf::dispatchNow($this, $this->account, $this->customer->primary_contact()->first());
        } else {
            CreateQuotePdf::dispatchNow($invitation->quote, $invitation->account, $invitation->contact);
        }

        return $storage_path;

    }

    /**
     * Access the quote calculator object
     *
     * @return object The quote calculator object getters
     */
    public function calc()
    {
        $quote_calc = null;

        if ($this->uses_inclusive_taxes) {
            $quote_calc = new InvoiceSumInclusive($this);
        } else {
            $quote_calc = new InvoiceSum($this);
        }

        return $quote_calc->build();

    }

    public function service(): QuoteService
    {
        return new QuoteService($this);
    }
}
