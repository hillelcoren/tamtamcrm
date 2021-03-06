<?php
/**
 * Invoice Ninja (https://invoiceninja.com)
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2019. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Observers;

use App\Models\Client;
use App\Models\Invoice;

class InvoiceObserver
{
    /**
     * Handle the client "created" event.
     *
     * @param Client $client
     * @return void
     */
    public function created(Invoice $invoice)
    {
        //
    }

    /**
     * Handle the client "updated" event.
     *
     * @param Client $client
     * @return void
     */
    public function updated(Invoice $invoice)
    {
        //
    }

    /**
     * Handle the client "deleted" event.
     *
     * @param Client $client
     * @return void
     */
    public function deleted(Invoice $invoice)
    {
        //
    }

    /**
     * Handle the client "restored" event.
     *
     * @param Client $client
     * @return void
     */
    public function restored(Invoice $invoice)
    {
        //
    }

    /**
     * Handle the client "force deleted" event.
     *
     * @param Client $client
     * @return void
     */
    public function forceDeleted(Invoice $invoice)
    {
        //
    }
}
