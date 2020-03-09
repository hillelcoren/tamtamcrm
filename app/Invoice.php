<?php

namespace App;

use App\Helpers\Invoice\InvoiceSum;
use App\Helpers\Invoice\InvoiceSumInclusive;
use App\Jobs\Invoice\CreateInvoicePdf;
use App\Services\Ledger\LedgerService;
use Illuminate\Database\Eloquent\Model;
use App\Task;
use App\InvoiceStatus;
use App\PaymentMethod;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use App\Events\Quote\QuoteWasMarkedSent;
use App\Jobs\Customer\UpdateClientBalance;
use App\Events\Invoice\InvoiceWasPaid;
use App\Traits\MakesInvoiceValues;
use Laracasts\Presenter\PresentableTrait;
use App\Traits\MakesReminders;
use App\Services\Invoice\InvoiceService;

class Invoice extends Model
{

    use PresentableTrait, SoftDeletes, MakesInvoiceValues, MakesReminders;

    protected $presenter = 'App\Presenters\InvoicePresenter';

    protected $casts = [
        'line_items' => 'object',
        'updated_at' => 'timestamp',
        'deleted_at' => 'timestamp',
        'is_deleted' => 'boolean',
    ];

    protected $with = [
        //'account',
        //'customer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number',
        'customer_id',
        'total',
        'balance',
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
        'line_items',
        'po_number',
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
        'is_recurring',
        'task_id',
        'company_id',
        'custom_value1',
        'custom_value2',
        'custom_value3',
        'custom_value4',
        'design_id'
    ];

    const STATUS_DRAFT = 1;
    const STATUS_SENT = 2;
    const STATUS_PARTIAL = 4;
    const STATUS_PAID = 3;
    const STATUS_CANCELLED = 5;
    const STATUS_OVERDUE = -1;
    const STATUS_UNPAID = -2;
    const STATUS_REVERSED = -3;

    public function service(): InvoiceService
    {
        return new InvoiceService($this);
    }

    public function canBePaid()
    {
        return !$this->isPaid() && !$this->is_deleted && $this->isStandard();
    }

    /**
     * @return bool
     */
    public function isPartial(): bool
    {
        return $this->status_id >= self::STATUS_PARTIAL;
    }

    /**
     * @return bool
     */
    public function hasPartial(): bool
    {
        return ($this->partial && $this->partial > 0) === true;
    }

    /**
     * @return bool
     */
    /* public function isStandard()
    {
        return $this->isType(1) && !$this->is_recurring;
    } */

    /**
     * @return bool
     */
    public function isPaid()
    {
        return $this->status_id >= 3;
    }

    public function setReminder()
    {
        $settings = $this->customer->getMergedSettings();
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

    /**
     * Returns the template for the invoice
     *
     * @return string Either the template view, OR the template HTML string
     * @todo  this needs attention, invoice->settings needs clarification
     */
    public function design(): string
    {
        if ($this->client->getSetting('design')) {
            return File::exists(resource_path($this->customer->getSetting('design'))) ? File::get(resource_path($this->customer->getSetting('design'))) : File::get(resource_path('views/pdf/design1.blade.php'));
        } else {
            return File::get(resource_path('views/pdf/design1.blade.php'));
        }
    }

    /**
     * Access the invoice calculator object
     *
     * @return object The invoice calculator object getters
     */
    public function calc()
    {
        $invoice_calc = null;

        if ($this->uses_inclusive_taxes) {
            $invoice_calc = new InvoiceSumInclusive($this);
        } else {
            $invoice_calc = new InvoiceSum($this);
        }

        return $invoice_calc->build();
    }

    /** TODO// DOCUMENT THIS FUNCTIONALITY */
    public function pdf_url()
    {
        $public_path = $this->customer->invoice_filepath() . $this->number . '.pdf';

        $storage_path = 'storage/' . $this->customer->invoice_filepath() . $this->number . '.pdf';

        $disk = config('filesystems.default');

        if (!Storage::disk($disk)->exists($public_path)) {
            event(new InvoiceWasUpdated($this, $this->account));
            CreateInvoicePdf::dispatch($this, $this->account, $this->customer->primary_contact()->first());
        }

        return $storage_path;
    }

    public function pdf_file_path()
    {
        $storage_path = 'storage/' . $this->customer->invoice_filepath() . $this->number . '.pdf';
        if (!Storage::exists($storage_path)) {
            CreateInvoicePdf::dispatchNow($this, $this->account, $this->customer->primary_contact()->first());
        }

        return $storage_path;
    }

    /**
     * @return bool
     */
    public function isSent()
    {
        return $this->status_id >= 3;
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function company_ledger()
    {
        return $this->morphMany(CompanyLedger::class, 'company_ledgerable');
    }

    public function credits()
    {
        return $this->belongsToMany(Credit::class)->using(Paymentable::class)->withPivot('amount', 'refunded')
                    ->withTimestamps();
    }

    public function payments()
    {
        return $this->morphToMany(Payment::class, 'paymentable')->withPivot('amount', 'refunded')->withTimestamps();
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

    public function documents()
    {
        return $this->morphMany(File::class, 'documentable');
    }

    /**
     * @return mixed
     */
    public function invitations()
    {
        return $this->hasMany('App\InvoiceInvitation')->orderBy('invoice_invitations.client_contact_id');
    }

    public function isPayable(): bool
    {
        if ($this->status_id == Invoice::STATUS_SENT && $this->is_deleted == false) {
            return true;
        } elseif ($this->status_id == Invoice::STATUS_PARTIAL && $this->is_deleted == false) {
            return true;
        } elseif ($this->status_id == Invoice::STATUS_SENT && $this->is_deleted == false) {
            return true;
        } elseif ($this->status_id == Invoice::STATUS_DRAFT && $this->is_deleted == false) {
            return true;
        } else {
            return false;
        }
    }

    public static function stringStatus(int $status)
    {
        switch ($status) {
            case Invoice::STATUS_DRAFT:
                return 'draft';
                break;
            case Invoice::STATUS_SENT:
                return 'sent';
                break;
            case Invoice::STATUS_PARTIAL:
                return 'partial';
                break;
            case Invoice::STATUS_PAID:
                return 'paid';
                break;
            case Invoice::STATUS_CANCELLED:
                return 'cancelled';
                break;
            case Invoice::STATUS_OVERDUE:
                return 'overdue';
                break;
            case Invoice::STATUS_UNPAID:
                return 'unpaid';
                break;
            case Invoice::STATUS_REVERSED:
                return 'reversed';
                break;
            default:
                # code...
                break;
        }
    }

    public function isRefundable(): bool
    {
        if ($this->is_deleted) {
            return false;
        }

        if (($this->total - $this->balance) == 0) {
            return false;
        }

        return true;
    }

    public function ledger()
    {
        return new LedgerService($this);
    }
}
