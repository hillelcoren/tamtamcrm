<?php

namespace App\Transformations;

use App\Payment;
use App\Paymentable;

class PaymentableTransformer
{
    public function transform(Paymentable $paymentable)
    {
        $entity_key = 'invoice_id';
        if ($paymentable->paymentable_type == Credit::class) {
            $entity_key = 'credit_id';
        }

        $prop = new Paymentable;

        $prop->id = $paymentable->id;
        $prop->{$entity_key} = $paymentable->paymentable_id;
        $prop->amount = $paymentable->amount;
        $prop->refunded = (float)$paymentable->refunded;

        return $prop;
    }
}
