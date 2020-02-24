<?php

namespace App\Helpers\Invoice;

/**
 * Class for discount calculations
 */
trait Balancer
{
    public function balance($total, $invoice)
    {
        if (isset($this->invoice->id) && $this->invoice->id >= 1) {
            return round($total - ($this->invoice->amount - $this->invoice->balance), 2);
        }

        return $total;
    }
}
