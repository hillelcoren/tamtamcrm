<?php

namespace App;

use App\Account;
use App\ClientContact;
use App\Traits\Inviteable;
use App\Customer;
use App\Quote;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuoteInvitation extends Model
{
    use Inviteable;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'client_contact_id',
    ];

    public function entityType()
    {
        return Quote::class;
    }

    /**
     * @return mixed
     */
    public function quote()
    {
        return $this->belongsTo(Quote::class)->withTrashed();
    }

    /**
     * @return mixed
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    /**
     * @return mixed
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return mixed
     */
    public function contact()
    {
        return $this->belongsTo(ClientContact::class, 'client_contact_id', 'id')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function signatureDiv()
    {
        if (!$this->signature_base64) {
            return false;
        }
        return sprintf('<img src="data:image/svg+xml;base64,%s"></img><p/>%s: %s', $this->signature_base64,
            ctrans('texts.signed'),
            $this->createClientDate($this->signature_date, $this->contact->client->timezone()->name));
    }

    public function markViewed()
    {
        $this->viewed_date = Carbon::now();
        $this->save();
    }
}
