<?php

namespace App;

use App\Events\PaymentWasVoided;
use App\Services\Ledger\LedgerService;
use App\Services\Payment\PaymentService;
use Illuminate\Database\Eloquent\Model;
use App\PaymentMethod;
use App\Customer;
use App\Traits\Payment\Refundable;
use App\Invoice;
use App\Paymentable;
use Event;
use App\Events\PaymentWasRefunded;
use App\Traits\GeneratesCounter;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;
    use GeneratesCounter;
    use Refundable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_id',
        'date',
        'number',
        'type_id',
        'amount',
        'customer_id',
        'status_id',
        'refunded',
        'transaction_reference',
        'is_manual',
        'private_notes'

    ];

    protected $casts = [
        'exchange_rate' => 'float',
        'updated_at' => 'timestamp',
        'deleted_at' => 'timestamp',
        'is_deleted' => 'boolean',
    ];

    protected $with = [
        'paymentables',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    const STATUS_PENDING = 1;
    const STATUS_VOIDED = 2;
    const STATUS_FAILED = 3;
    const STATUS_COMPLETED = 4;
    const STATUS_PARTIALLY_REFUNDED = 5;
    const STATUS_REFUNDED = 6;

    const TYPE_CUSTOMER_CREDIT = 2;

    /**
     * @return BelongsTo
     */
    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, 'type_id');
    }

    /**
     * @return BelongsTo
     */
    public function payment_status()
    {
        return $this->belongsTo(PaymentStatus::class, 'status_id');
    }

    public function paymentables()
    {
        return $this->hasMany(Paymentable::class);
    }

    /**
     * @return BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->morphedByMany(Invoice::class, 'paymentable')->withPivot('amount', 'refunded')->withTimestamps();
    }

    /**
     * @return mixed
     */
    public function getCompletedAmount(): float
    {
        return $this->amount - $this->refunded;
    }

    public function recordRefund($amount = null)
    {
        //do i need $this->isRefunded() here?
        if ($this->isVoided()) {
            return false;
        }

        //if no refund specified
        if (!$amount) {
            $amount = $this->amount;
        }

        $new_refund = min($this->amount, $this->refunded + $amount);
        $refund_change = $new_refund - $this->refunded;

        if ($refund_change) {
            $this->refunded = $new_refund;
            $this->status_id =
                $this->refunded == $this->amount ? self::STATUS_REFUNDED : self::STATUS_PARTIALLY_REFUNDED;
            $this->save();

            event(new PaymentWasRefunded($this, $refund_change));
        }

        return true;
    }

    public function isVoided()
    {
        return $this->status_id == self::STATUS_VOIDED;
    }

    public function isPartiallyRefunded()
    {
        return $this->status_id == self::STATUS_PARTIALLY_REFUNDED;
    }

    public function isRefunded()
    {
        return $this->status_id == self::STATUS_REFUNDED;
    }

    public function markVoided()
    {
        if ($this->isVoided() || $this->isPartiallyRefunded() || $this->isRefunded()) {
            return false;
        }

        $this->refunded = $this->amount;
        $this->status_id = self::STATUS_VOIDED;
        $this->save();

        event(new PaymentWasVoided($this));
    }

    public function markComplete()
    {
        $this->status_id = STATUS_COMPLETED;
        $this->save();
        //Event::fire(new PaymentCompleted($this));
    }


    /**
     * @param string $failureMessage
     */
    public function markFailed($failureMessage = '')
    {
        $this->status_id = STATUS_FAILED;
        $this->gateway_error = $failureMessage;
        $this->save();
        //Event::fire(new PaymentFailed($this));
    }

    public function invoices()
    {
        return $this->morphedByMany(Invoice::class, 'paymentable')->withPivot('amount');
    }

    public function credits()
    {
        return $this->morphedByMany(Credit::class, 'paymentable')->withPivot('amount', 'refunded')->withTimestamps();
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function company_ledger()
    {
        return $this->morphMany(CompanyLedger::class, 'company_ledgerable');
    }

    public function refund(array $data): Payment
    {
        $this->processRefund($data);

        return $this;
    }

    public function service(): PaymentService
    {
        return new PaymentService($this);
    }

    public function ledger()
    {
        return new LedgerService($this);
    }
}
