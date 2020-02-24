<?php

namespace App;

use App\Helpers\Invoice\InvoiceSum;
use App\Helpers\Invoice\InvoiceSumInclusive;
use App\Services\Credit\CreditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Credit extends Model
{
    use SoftDeletes;

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
        'private_notes'

    ];

    protected $casts = [
        'line_items' => 'object',
        'updated_at' => 'timestamp',
        'deleted_at' => 'timestamp',
    ];

    const STATUS_DRAFT = 1;
    const STAUS_PARTIAL = 2;
    const STATUS_APPLIED = 3;

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
}
