<?php

namespace App\Helpers\Invoice;

use App\DataMapper\BaseSettings;
use App\DataMapper\InvoiceItem;
//use App\Helpers\Invoice\Discounter;
use App\Helpers\Invoice\Taxer;
use App\Invoice;
use App\Traits\NumberFormatter;
use Illuminate\Support\Collection;

class InvoiceItemSum
{
    use NumberFormatter;
    use Taxer;

    protected $invoice;

    private $items;

    private $line_total;

    private $currency;

    private $total_taxes;

    private $item;

    private $line_items;

    private $sub_total;

    private $total_discount;

    private $tax_collection;

    public function __construct($invoice)
    {
        $this->tax_collection = collect([]);

        $this->invoice = $invoice;

        $this->currency = $this->invoice->customer->currency;

        $this->line_items = [];
    }

    public function process()
    {
        if (!$this->invoice->line_items || !isset($this->invoice->line_items) ||
            !is_array($this->invoice->line_items) || count($this->invoice->line_items) == 0) {
            $this->items = [];
            return $this;
        }

        $this->calcLineItems();

        return $this;
    }

    private function calcLineItems()
    {
        foreach ($this->invoice->line_items as $this->item) {
            $this->cleanLineItem()->sumLineItem()->setDiscount()->calcTaxes()->push();
        }

        return $this;
    }

    private function push()
    {
        $precision = !empty($this->currency) && !empty($this->currency->precision) ? $this->currency->precision : 2;

        $this->sub_total += $this->formatValue($this->item->unit_price, $precision) *
            $this->formatValue($this->item->quantity, $precision);

        $this->line_items[] = $this->item;

        return $this;
    }

    private function sumLineItem()
    {
        $precision = !empty($this->currency) && !empty($this->currency->precision) ? $this->currency->precision : 2;
        $this->setLineTotal($this->formatValue($this->item->unit_price, $precision) *
            $this->formatValue($this->item->quantity, $precision));

        return $this;
    }

    private function setDiscount()
    {
        $precision = !empty($this->currency) && !empty($this->currency->precision) ? $this->currency->precision : 2;

        if ($this->invoice->is_amount_discount) {
            $this->setLineTotal($this->getLineTotal() - $this->formatValue($this->item->unit_discount, $precision));
        } else {
            $this->setLineTotal($this->getLineTotal() -
                $this->formatValue(round($this->item->sub_total * ($this->item->unit_discount / 100), 2), $precision));
        }

        $this->item->is_amount_discount = $this->invoice->is_amount_discount;

        return $this;
    }

    private function calcTaxes()
    {
        $precision = !empty($this->currency) && !empty($this->currency->precision) ? $this->currency->precision : 2;

        $item_tax = 0;

        $amount = $this->item->sub_total - ($this->item->sub_total * ($this->invoice->unit_discount / 100));

        $item_tax_rate1_total = $this->calcAmountLineTax($this->item->unit_tax, $amount);

        $item_tax += $item_tax_rate1_total;

        if ($item_tax_rate1_total > 0) {
            $this->groupTax($this->item->tax_rate_name, $this->item->unit_tax, $item_tax_rate1_total);
        }

        $this->setTotalTaxes($this->formatValue($item_tax, $precision));

        return $this;
    }

    private function groupTax($tax_name, $tax_rate, $tax_total)
    {
        $key = str_replace(" ", "", $tax_name . $tax_rate);

        $group_tax = ['key' => $key, 'total' => $tax_total, 'tax_name' => $tax_name . ' ' . $tax_rate . '%'];

        $this->tax_collection->push(collect($group_tax));
    }

    public function getTotalTaxes()
    {
        return $this->total_taxes;
    }

    public function setTotalTaxes($total)
    {
        $this->total_taxes = $total;

        return $this;
    }

    public function setLineTotal($total)
    {
        $this->item->sub_total = $total;

        return $this;
    }

    public function getLineTotal()
    {
        return $this->item->sub_total;
    }

    public function getLineItems()
    {
        return $this->line_items;
    }

    public function getGroupedTaxes()
    {
        return $this->tax_collection;
    }

    public function setGroupedTaxes($group_taxes)
    {
        $this->tax_collection = $group_taxes;

        return $this;
    }

    public function getSubTotal()
    {
        return $this->sub_total;
    }

    public function setSubTotal($value)
    {
        $this->sub_total = $value;
        return $this;
    }


    /**
     * Invoice Amount Discount
     *
     * The problem, when calculating invoice level discounts,
     * the tax collected changes.
     *
     * We need to synthetically reduce the line_total amounts
     * and recalculate the taxes and then pass back
     * the updated map
     */

    public function calcTaxesWithAmountDiscount()
    {
        $this->setGroupedTaxes(collect([]));

        $item_tax = 0;

        foreach ($this->line_items as $this->item) {
            if ($this->item->sub_total == 0) {
                continue;
            }

            $amount =
                $this->item->sub_total - ($this->item->sub_total * ($this->invoice->unit_discount / $this->sub_total));
            $item_tax_rate1_total = $this->calcAmountLineTax($this->item->unit_tax, $amount);

            $item_tax += $item_tax_rate1_total;

            if ($item_tax_rate1_total > 0) {
                $this->groupTax($this->item->tax_rate_name, $this->item->unit_tax, $item_tax_rate1_total);
            }
        }

        $this->setTotalTaxes($item_tax);
    }

    /**
     * Sets default values for the line_items
     * @return $this
     */
    private function cleanLineItem()
    {
        $invoice_item = (object)get_class_vars(InvoiceItem::class);
        unset($invoice_item->casts);

        foreach ($invoice_item as $key => $value) {
            if (!property_exists($this->item, $key) || !isset($this->item->{$key})) {
                $this->item->{$key} = $value;
                $this->item->{$key} = BaseSettings::castAttribute(InvoiceItem::$casts[$key], $value);
            }
        }


        return $this;
    }
}
