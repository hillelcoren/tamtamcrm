<?php

namespace App\Helpers\Invoice;

use App\Helpers\Invoice\Balancer;
use App\Helpers\Invoice\CustomValuer;
use App\Helpers\Invoice\Discounter;
use App\Helpers\Invoice\InvoiceItemSum;
use App\Helpers\Invoice\Taxer;
use App\Traits\NumberFormatter;
use Illuminate\Support\Collection;

class InvoiceSum
{
    use NumberFormatter;
    use Discounter;
    use Taxer;

    protected $invoice;

    public $tax_map;

    public $invoice_item;

    public $total_taxes;

    private $total;

    private $total_discount;

    private $total_custom_values;

    private $total_tax_map;

    private $sub_total;

    /**
     * Constructs the object with Invoice and Settings object
     * InvoiceSum constructor.
     * @param $invoice
     */
    public function __construct($invoice)
    {

        $this->invoice = $invoice;
        //$this->total = $invoice->total;
        $this->tax_map = new Collection;
    }

    public function build()
    {
        $this->calculateLineItems()->calculateDiscount()->calculateInvoiceTaxes()->setTaxMap()->calculateTotals()
             ->calculateBalance()->calculatePartial();
        return $this;
    }

    private function calculateLineItems()
    {
        $this->invoice_items = new InvoiceItemSum($this->invoice);
        $this->invoice_items->process();
        $this->invoice->line_items = $this->invoice_items->getLineItems();
        //$this->total = $this->invoice_items->getSubTotal();
        $this->setSubTotal($this->invoice_items->getSubTotal());

        return $this;
    }

    private function calculateInvoiceTaxes()
    {
        if ($this->invoice->tax_rate > 0) {
            $tax = $this->taxer($this->total, $this->invoice->tax_rate);
            $this->total_taxes += $tax;
            $this->total_tax_map[] =
                ['name' => $this->invoice->tax_rate_name . ' ' . $this->invoice->tax_rate . '%', 'total' => $tax];
        }

        return $this;
    }

    private function calculateDiscount()
    {
        $total = $this->invoice_items->getSubTotal() - $this->invoice->discount_total;

        if ($total <= 0) {
            return $this;
        }

        //$this->total_discount = $this->discount($this->invoice_items->getSubTotal());

        $this->total = $total;

        return $this;
    }

    private function calculateTotals()
    {
        if (empty($this->total_taxes)) {
            return $this;
        }

        $this->total += $this->total_taxes;

        return $this;
    }

    /**
     * Calculates the balance.
     *
     * @return     self  The balance.
     */
    private function calculateBalance()
    {
        $this->setCalculatedAttributes();
        return $this;
    }

    private function calculatePartial()
    {
        if (!isset($this->invoice->id) && isset($this->invoice->partial)) {
            $this->invoice->partial =
                max(0, min($this->formatValue($this->invoice->partial, 2), $this->invoice->balance));
        }

        return $this;
    }

    public function setTaxMap()
    {
        if ($this->invoice->is_amount_discount == true) {
            $this->invoice_items->calcTaxesWithAmountDiscount();
        }

        $this->tax_map = collect();

        $keys = $this->invoice_items->getGroupedTaxes()->pluck('key')->unique();

        $values = $this->invoice_items->getGroupedTaxes();

        foreach ($keys as $key) {
            $tax_name = $values->filter(function ($value, $k) use ($key) {
                return $value['key'] == $key;
            })->pluck('tax_name')->first();

            $total_line_tax = $values->filter(function ($value, $k) use ($key) {
                return $value['key'] == $key;
            })->sum('total');

            //$total_line_tax -= $this->discount($total_line_tax);

            $this->tax_map[] = ['name' => $tax_name, 'total' => $total_line_tax];

            $this->total_taxes += $total_line_tax;
        }

        return $this;
    }

    public function getTaxMap()
    {
        return $this->tax_map;
    }

    /**
     * Build $this->invoice variables after
     * calculations have been performed.
     */
    private function setCalculatedAttributes()
    {
        $precision = !empty($this->invoice->customer) &&
        !empty($this->invoice->customer->currency) ? $this->invoice->customer->currency->precision : 2;

        /* If amount != balance then some money has been paid on the invoice, need to subtract this difference from the total to set the new balance */
        if ($this->invoice->total != $this->invoice->balance) {
            $paid_to_date = $this->invoice->total - $this->invoice->balance;

            $this->invoice->balance = $this->formatValue($this->getTotal(), $precision) - $paid_to_date;
        } else {
            $this->invoice->balance = $this->formatValue($this->getTotal(), $precision);
        }

        /* Set new calculated total */
        $this->invoice->total = $this->formatValue($this->getTotal(), $precision);

        $this->invoice->tax_total = $this->getTotalTaxes();

        return $this;
    }

    public function getInvoice()
    {
        //Build invoice values here and return Invoice
        $this->setCalculatedAttributes();
        $this->invoice->save();

        return $this->invoice;
    }

    public function getQuote()
    {
        $this->setCalculatedAttributes();
        $this->invoice->save();

        return $this->invoice;
    }

    public function getCredit()
    {
        $this->setCalculatedAttributes();
        $this->invoice->save();

        return $this->invoice;
    }

    public function setTotalDiscount($discount_total)
    {
        $this->discount_total = $discount_total;
        return $this;
    }

    public function setSubTotal($sub_total)
    {
        $this->sub_total = $sub_total;
        return $this;
    }

    public function getSubTotal()
    {
        return $this->sub_total;
    }

    public function getTotalDiscount()
    {
        return $this->total_discount;
    }

    public function getTotal()
    {
        if ($this->total <= 0) {
            return $this->invoice->total;
        }

        return $this->total;
    }

    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    public function getBalance()
    {
        return $this->invoice->balance;
    }

    public function getItemTotalTaxes()
    {
        return $this->getTotalTaxes();
    }

    public function getTotalTaxes()
    {
        return $this->total_taxes;
    }

    public function getTotalTaxMap()
    {
        return $this->total_tax_map;
    }
}
