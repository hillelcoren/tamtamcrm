<?php

namespace App;

use App\Helpers\Invoice\InvoiceSum;
use App\Helpers\Invoice\InvoiceSumInclusive;
use App\Jobs\Credit\CreateCreditPdf;
use App\Services\Credit\CreditService;
use App\Traits\MakesInvoiceValues;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Laracasts\Presenter\PresentableTrait;

class Credit extends Model
{
    use SoftDeletes;
    use MakesInvoiceValues;
    use PresentableTrait;

    protected $presenter = 'App\Presenters\CreditPresenter';

    /**
     * @var array
     */
    protected $fillable = [
        'notes',
        'customer_id',
        'line_items',
        'account_id',
        'user_id',
        'total',
        'balance',
        'credit_date',
        'tax_rate',
        'tax_rate_name',
        'number',
        'terms',
        'footer',
        'public_notes',
        'private_notes',
        'invoice_id',
        'design_id'

    ];

    protected $casts = [
        'line_items' => 'object',
        'updated_at' => 'timestamp',
        'deleted_at' => 'timestamp',
    ];

    const STATUS_DRAFT = 1;
    const STATUS_SENT = 2;
    const STATUS_PARTIAL = 3;
    const STATUS_APPLIED = 4;

    public function assigned_user()
    {
        return $this->belongsTo(User::class, 'assigned_user_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function account()
    {
        return $this->belongsTo('App\Account');
    }

    /**
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo('App\User')->withTrashed();
    }

    public function service(): CreditService
    {
        return new CreditService($this);
    }


    /**
     * @return mixed
     */
    public function invoice()
    {
        return $this->belongsTo('App\Invoice')->withTrashed();
    }

    public function invitations()
    {
        return $this->hasMany(CreditInvitation::class);
    }

    public function payments()
    {
        return $this->morphToMany(Payment::class, 'paymentable');
    }

    /**
     * @return mixed
     */
    public function customer()
    {
        return $this->belongsTo('App\Customer')->withTrashed();
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class)->using(Paymentable::class);
    }

    public function company_ledger()
    {
        return $this->morphMany(CompanyLedger::class, 'company_ledgerable');
    }

    /**
     * Access the invoice calculator object
     *
     * @return object The invoice calculator object getters
     */
    public function calc()
    {
        $credit_calc = null;

        if ($this->uses_inclusive_taxes) {
            $credit_calc = new InvoiceSumInclusive($this);
        } else {
            $credit_calc = new InvoiceSum($this);
        }

        return $credit_calc->build();
    }

    /**
     * @param float $balance_adjustment
     */
    public function updateBalance($balance_adjustment)
    {
        if ($this->is_deleted) {
            return;
        }
        $balance_adjustment = floatval($balance_adjustment);
        $this->balance = $this->balance + $balance_adjustment;
        if ($this->balance == 0) {
            $this->status_id = self::STATUS_APPLIED;
            $this->save();
            //event(new InvoiceWasPaid($this, $this->company));//todo
            return;
        }
        $this->save();
    }

    public function setStatus($status)
    {
        $this->status_id = $status;
        $this->save();
    }

    /**
     * @param null $invitation
     * @return string
     */
    public function pdf_file_path($invitation = null)
    {
        $storage_path = 'storage/' . $this->customer->credit_filepath() . $this->number . '.pdf';

        if (Storage::exists($storage_path)) {
            return $storage_path;
        }

        if (!$invitation) {
            CreateCreditPdf::dispatchNow($this, $this->account, $this->customer->primary_contact()->first());
        } else {
            CreateCreditPdf::dispatchNow($invitation->credit, $invitation->account, $invitation->contact);
        }

        return $storage_path;
    }

    public function markInvitationsSent()
    {
        $this->invitations->each(function ($invitation) {
            if (!isset($invitation->sent_date)) {
                $invitation->sent_date = Carbon::now();
                $invitation->save();
            }
        });
    }
}
