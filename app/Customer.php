<?php

namespace App;

use App\DataMapper\CompanySettings;
use App\DataMapper\CustomerSettings;
use Illuminate\Database\Eloquent\Model;
use App\Message;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\PaymentMethod;
use App\CustomerType;
use App\Currency;
use App\Country;
use Laracasts\Presenter\PresentableTrait;
use App\Traits\CustomerSettingsSaver;
use App\Services\Customer\CustomerService;

class Customer extends Model
{

    use CustomerSettingsSaver,
        SoftDeletes,
        PresentableTrait;

    protected $presenter = 'App\Presenters\CustomerPresenter';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'credits',
        'last_name',
        'email',
        'status',
        'job_title',
        'company_id',
        'currency_id',
        'phone',
        'customer_type',
        'default_payment_method',
        'settings',
        'assigned_user_id',
        'custom_value1',
        'custom_value2',
        'custom_value3',
        'custom_value4',
        'group_settings_id',
    ];

    protected $casts = [
        'settings' => 'object',
        'updated_at' => 'timestamp',
        'created_at' => 'timestamp',
        'deleted_at' => 'timestamp',
        'is_deleted' => 'boolean',
    ];

    protected $with = [
        'contacts'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses()
    {
        return $this->hasMany(Address::class)->whereStatus(true);
    }

    public function credits()
    {
        return $this->hasMany(Credit::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function customerType()
    {
        return $this->belongsTo(CustomerType::class, 'customer_type');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'default_payment_method');
    }

    /**
     * @param $balanceAdjustment
     * @param $paidToDateAdjustment
     */
    public function updateBalances($balanceAdjustment, $paidToDateAdjustment)
    {
        if ($balanceAdjustment == 0 && $paidToDateAdjustment == 0) {
            return;
        }
        $this->balance = $this->balance + $balanceAdjustment;
        $this->paid_to_date = $this->paid_to_date + $paidToDateAdjustment;
        $this->save();
    }

      public function service() :CustomerService
     {
         return new CustomerService($this);
     }

     /**
      * Adjusts client "balances" when a customer
      * makes a payment that goes on file, but does
      * not effect the customer.balance record
      *
      * @param  float $amount Adjustment amount
      * @return Customer
      */
     public function processUnappliedPayment($amount) :Customer
     {

         return $this->service()->updatePaidToDate($amount)
                                 ->adjustCreditBalance($amount)
                                 ->save();
     }

     public function updateBalance($amount) :CustomerService
     {
         return $this->service()->updateBalance($amount);
     }

    /**
     * @return float|int
     */
    public function getTotalCredit()
    {
        return DB::table('credits')
            ->where('customer_id', '=', $this->id)
            ->whereNull('deleted_at')
            ->sum('balance');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function messages()
    {
        return $this->belongsToMany(Message::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function contacts()
    {
        return $this->hasMany(ClientContact::class)->orderBy('is_primary', 'desc');
    }

    public function primary_contact()
    {
        return $this->hasMany(ClientContact::class)->whereIsPrimary(true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function creditsWithBalance()
    {
        return $this->hasMany('App\Credit')->where('balance', '>', 0);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group_settings()
    {
        return $this->belongsTo(GroupSetting::class);
    }

    /**
     *
     * Returns a single setting
     * which cascades from
     * Client > Group > Company
     *
     * @param  string $setting The Setting parameter
     * @return mixed          The setting requested
     */
    public function getSetting($setting)
    {

        /*Client Settings*/
        if ($this->settings && (property_exists($this->settings,
                    $setting) !== false) && (isset($this->settings->{$setting}) !== false)
        ) {
            /*need to catch empty string here*/
            if (iconv_strlen($this->settings->{$setting}) >= 1) {
                return $this->settings->{$setting};
            }
        }

        /*Group Settings*/
        if ($this->group_settings && (property_exists($this->group_settings->settings,
                    $setting) !== false) && (isset($this->group_settings->settings->{$setting}) !== false)
        ) {
            echo $setting . ' b';
            return $this->group_settings->settings->{$setting};
        }
        /*Company Settings*/
        if ((property_exists($this->account->settings,
                    $setting) != false) && (isset($this->account->settings->{$setting}) !== false)
        ) {
            return $this->account->settings->{$setting};
        }
        throw new \Exception("Settings corrupted", 1);
    }

    /**
     *
     * Returns the entire filtered set
     * of settings which have been merged from
     * Client > Group > Company levels
     *
     * @return object stdClass object of settings
     */
    public function getMergedSettings() : ?object
    {
        if(empty($this->settings)) {
            return null;
        }

if ($this->group_settings !== null) {
    $group_settings = CustomerSettings::buildCustomerSettings($this->group_settings->settings,
        $this->settings);
    return CustomerSettings::buildCustomerSettings($this->account->settings, $group_settings);
}
return CompanySettings::setProperties(CustomerSettings::buildCustomerSettings($this->account->settings,
    $this->settings));
}

public
function getCountryId() : ?Country {
    $address = Address::where('address_type', '=', 1)->where('customer_id', '=', $this->id)->first();

    if ($address->count() > 0) {
        return $address->country;
    }
#
    return null;
}


    /**
     * Generates an array of payment urls per client
     * for a given amount.
     *
     * The route produced will provide the
     * company_gateway and payment_type ids
     *
     * The invoice/s will need to be injected
     * upstream of this method as they are not
     * included in this logic.
     *
     * @param  float $amount The amount to be charged
     * @return array         Array of payment labels and urls
     */
    public function getPaymentMethods($amount) :array
{
//this method will get all the possible gateways a client can pay with
//but we also need to consider payment methods that are already stored
//so we MUST filter the company gateways and remove duplicates.
//
//Also need to harvest the list of client gateway tokens and present these
//for instant payment
    $company_gateways = $this->getSetting('company_gateway_ids');
    if ($company_gateways) {
        $gateways = $this->company->company_gateways->whereIn('id', $payment_gateways);
    } else {
        $gateways = $this->account->company_gateways;
    }
    $gateways->filter(function ($method) use ($amount) {
        if ($method->min_limit !== null && $amount < $method->min_limit) {
            return false;
        }
        if ($method->max_limit !== null && $amount > $method->min_limit) {
            return false;
        }
    });
    $payment_methods = [];
    foreach ($gateways as $gateway) {
        foreach ($gateway->driver($this)->gatewayTypes() as $type) {
            $payment_methods[] = [$gateway->id => $type];
        }
    }

    $payment_methods_collections = collect($payment_methods);
    //** Plucks the remaining keys into its own collection
    $payment_methods_intersect = $payment_methods_collections->intersectByKeys($payment_methods_collections->flatten(1)->unique());
    $payment_urls = [];
    foreach ($payment_methods_intersect as $key => $child_array) {
        foreach ($child_array as $gateway_id => $gateway_type_id) {
            $gateway = $gateways->where('id', $gateway_id)->first();
            $fee_label = $gateway->calcGatewayFeeLabel($amount, $this);
            $payment_urls[] = [
                'label' => ctrans('texts.' . $gateway->getTypeAlias($gateway_type_id)) . $fee_label,
                'company_gateway_id' => $gateway_id,
                'gateway_type_id' => $gateway_type_id
            ];
        }
    }
    return $payment_urls;
}

        /**
         * Returns the first Credit Card Gateway
         *
         * @return NULL|CompanyGateway The Priority Credit Card gateway
         */
    public function getCreditCardGateway() :?CompanyGateway
    {
        $company_gateways = $this->getSetting('company_gateway_ids');

        if ($company_gateways) {
            $gateways = $this->account->company_gateways->whereIn('id', $payment_gateways);
        } else {
            $gateways = $this->account->company_gateways;
        }
        foreach ($gateways as $gateway) {
            if (in_array(GatewayType::CREDIT_CARD, $gateway->driver($this)->gatewayTypes())) {
                return $gateway;
            }
        }
        return null;
    }

    public function gateway_tokens()
{
    return $this->hasMany(ClientGatewayToken::class);
}
    /**
     * Retrieves the specific payment token per
     * gateway - per payment method
     *
     * Allows the storage of multiple tokens
     * per client per gateway per payment_method
     *
     * @param  int $company_gateway_id The company gateway ID
     * @param  int $payment_method_id The payment method ID
     * @return ClientGatewayToken       The client token record
     */
    public function gateway_token($company_gateway_id, $payment_method_id)
{
    return $this->gateway_tokens()
        ->whereCompanyGatewayId($company_gateway_id)
        ->whereGatewayTypeId($payment_method_id)
        ->first();
}
}