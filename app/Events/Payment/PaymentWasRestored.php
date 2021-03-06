<?php
/**
 * Invoice Ninja (https://invoiceninja.com)
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2020. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Events\Payment;

use App\Models\Payment;
use Illuminate\Queue\SerializesModels;

/**
 * Class PaymentWasRestored.
 */
class PaymentWasRestored
{
    use SerializesModels;

    /**
     * @var Payment
     */
    public $payment;
    public $fromDeleted;

    /**
     * Create a new event instance.
     *
     * @param Payment $payment
     * @param $fromDeleted
     */
    public function __construct(Payment $payment, $fromDeleted)
    {
        $this->payment = $payment;
        $this->fromDeleted = $fromDeleted;
    }
}
