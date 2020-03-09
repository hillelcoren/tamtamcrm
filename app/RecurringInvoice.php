<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;


/**
 * Class for Recurring Invoices.
 */
class RecurringInvoice extends Model
{
    use SoftDeletes;

    /**
     * Invoice Statuses
     */
    const STATUS_DRAFT = 2;
    const STATUS_ACTIVE = 3;
    const STATUS_CANCELLED = 4;
    const STATUS_PENDING = -1;
    const STATUS_COMPLETED = -2;


    /* Make sure we support overflow!!!!!!!!!!
    $start = Carbon::today();
    $subscription = Carbon::parse('2017-12-31');
    foreach (range(1, 12) as $month) {
        $day = $start->addMonthNoOverflow()->thisDayOrLast($subscription->day);
        echo "You will be billed on {$day} in month {$month}\n";
    }
     */
    const FREQUENCY_DAILY = 1;
    const FREQUENCY_WEEKLY = 2;
    const FREQUENCY_TWO_WEEKS = 3;
    const FREQUENCY_FOUR_WEEKS = 4;
    const FREQUENCY_MONTHLY = 5;
    const FREQUENCY_TWO_MONTHS = 6;
    const FREQUENCY_THREE_MONTHS = 7;
    const FREQUENCY_FOUR_MONTHS = 8;
    const FREQUENCY_SIX_MONTHS = 9;
    const FREQUENCY_ANNUALLY = 10;
    const FREQUENCY_TWO_YEARS = 11;
    const FREQUENCY_THREE_YEARS = 12;
    const RECURS_INDEFINITELY = -1;

    protected $fillable = [
        'status_id',
        'account_id',
        'customer_id',
        'number',
        'total',
        'sub_total',
        'tax_total',
        'discount_total',
        'partial_due_date',
        'is_amount_discount',
        'po_number',
        'date',
        'due_date',
        'line_items',
        'footer',
        'public_notes',
        'private_notes',
        'terms',
        'total',
        'partial',
        'frequency_id',
        'start_date',
        'custom_value1',
        'custom_value2',
        'custom_value3',
        'custom_value4',
        'tax_rate_name',
        'tax_rate',
        'settings',
    ];
    protected $casts = [
        'settings' => 'object',
        'line_items' => 'object',
        'updated_at' => 'timestamp',
        'deleted_at' => 'timestamp',
    ];
//    protected $appends = [
//        'hashed_id',
//        'status'
//    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function assigned_user()
    {
        return $this->belongsTo(User::class, 'assigned_user_id', 'id')->withTrashed();
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, "id", "recurring_id")->withTrashed();
    }

    public function invitations()
    {
        $this->morphMany(InvoiceInvitation::class);
    }

    public function getStatusAttribute()
    {
        if ($this->status_id == RecurringInvoice::STATUS_ACTIVE &&
            $this->start_date > Carbon::now()) //marked as active, but yet to fire first cycle
        {
            return RecurringInvoice::STATUS_PENDING;
        } else {
            if ($this->status_id == RecurringInvoice::STATUS_ACTIVE && $this->next_send_date > Carbon::now()) {
                return RecurringInvoice::STATUS_COMPLETED;
            } else {
                return $this->status_id;
            }
        }
    }

    public function nextSendDate(): ?Carbon
    {
        switch ($this->frequency_id) {
            case RecurringInvoice::FREQUENCY_WEEKLY:
                return Carbon::parse($this->next_send_date->addWeek());
            case RecurringInvoice::FREQUENCY_TWO_WEEKS:
                return Carbon::parse($this->next_send_date->addWeeks(2));
            case RecurringInvoice::FREQUENCY_FOUR_WEEKS:
                return Carbon::parse($this->next_send_date->addWeeks(4));
            case RecurringInvoice::FREQUENCY_MONTHLY:
                return Carbon::parse($this->next_send_date->addMonth());
            case RecurringInvoice::FREQUENCY_TWO_MONTHS:
                return Carbon::parse($this->next_send_date->addMonths(2));
            case RecurringInvoice::FREQUENCY_THREE_MONTHS:
                return Carbon::parse($this->next_send_date->addMonths(3));
            case RecurringInvoice::FREQUENCY_FOUR_MONTHS:
                return Carbon::parse($this->next_send_date->addMonths(4));
            case RecurringInvoice::FREQUENCY_SIX_MONTHS:
                return Carbon::parse($this->next_send_date->addMonths(6));
            case RecurringInvoice::FREQUENCY_ANNUALLY:
                return Carbon::parse($this->next_send_date->addYear());
            case RecurringInvoice::FREQUENCY_TWO_YEARS:
                return Carbon::parse($this->next_send_date->addYears(2));
            case RecurringInvoice::FREQUENCY_THREE_YEARS:
                return Carbon::parse($this->next_send_date->addYears(3));
            default:
                return null;
        }
    }

    public function remainingCycles(): int
    {
        if ($this->remaining_cycles == 0) {
            return 0;
        } else {
            return $this->remaining_cycles - 1;
        }
    }

    public function setCompleted(): void
    {
        $this->status_id = self::STATUS_COMPLETED;
        $this->next_send_date = null;
        $this->remaining_cycles = 0;
        $this->save();
    }

}
