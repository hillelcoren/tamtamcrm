<?php

namespace App;

use App\Helpers\Invoice\InvoiceSum;
use App\Helpers\Invoice\InvoiceSumInclusive;
use Illuminate\Database\Eloquent\Model;
use App\Task;
use App\InvoiceStatus;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use App\Events\Invoice\InvoiceWasMarkedSent;
use App\Jobs\Customer\UpdateClientBalance;
use App\Events\Invoice\InvoiceWasPaid;
use App\InvoiceLine;

class Quote extends Model
{

    use SoftDeletes;

    protected $casts = [
        'line_items' => 'object',
        'updated_at' => 'timestamp',
        'created_at' => 'timestamp',
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
        'notes',
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
    ];

    const STATUS_DRAFT = 1;
    const STATUS_SENT =  2;
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoiceStatus()
    {
        return $this->belongsTo(InvoiceStatus::class, 'status_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
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
      * Access the quote calculator object
      *
      * @return object The quote calculator object getters
      */
     public function calc()
     {
         $quote_calc = null;

         if($this->uses_inclusive_taxes) {
             $quote_calc = new InvoiceSumInclusive($this);
         } else {
             $quote_calc = new InvoiceSum($this);
         }

         return $quote_calc->build();

     }
}