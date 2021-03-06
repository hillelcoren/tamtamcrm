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

use App\Models\Expense;

class ExpenseObserver
{
    /**
     * Handle the expense "created" event.
     *
     * @param Expense $expense
     * @return void
     */
    public function created(Expense $expense)
    {
        //
    }

    /**
     * Handle the expense "updated" event.
     *
     * @param Expense $expense
     * @return void
     */
    public function updated(Expense $expense)
    {
        //
    }

    /**
     * Handle the expense "deleted" event.
     *
     * @param Expense $expense
     * @return void
     */
    public function deleted(Expense $expense)
    {
        //
    }

    /**
     * Handle the expense "restored" event.
     *
     * @param Expense $expense
     * @return void
     */
    public function restored(Expense $expense)
    {
        //
    }

    /**
     * Handle the expense "force deleted" event.
     *
     * @param Expense $expense
     * @return void
     */
    public function forceDeleted(Expense $expense)
    {
        //
    }
}
