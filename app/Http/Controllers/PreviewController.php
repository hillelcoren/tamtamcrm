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

namespace App\Http\Controllers;

use App\Designs\Custom;
use App\Designs\Designer;
use App\Jobs\Utils\PreviewPdf;
use App\Traits\MakesInvoiceHtml;
use Illuminate\Support\Facades\Storage;


class PreviewController extends Controller
{
    use MakesInvoiceHtml;

    public function __construct()
    {
    }

    /**
     * Returns a template filled with entity variables
     *
     * @return \Illuminate\Http\Response
     *
     */
    public function show()
    {

        if (request()->has('entity') && request()->has('entity_id') && request()->has('design')) {

            $invoice_design = new Custom((object)request()->input('design'));

            $entity = ucfirst(request()->input('entity'));

            $class = "App\\$entity";

            $pdf_class = "App\Jobs\\$entity\\Create{$entity}Pdf";

            $entity_obj = $class::whereId(request()->input('entity_id'))->first();

            if (!$entity_obj) {
                return $this->blankEntity();
            }

            $entity_obj->load('customer');

            $designer = new Designer($entity_obj, $invoice_design, $entity_obj->customer->getSetting('pdf_variables'),
                lcfirst($entity));

            $html = $this->generateInvoiceHtml($designer->build()->getHtml(), $entity_obj);

            $file_path = PreviewPdf::dispatchNow($html, auth()->user()->account_user()->account);

            return response()->json(['data' => base64_encode(file_get_contents($file_path))]);

        }

        return $this->blankEntity();

    }

    private function blankEntity()
    {
        $client = factory(\App\Customer::class)->create([
            'user_id' => auth()->user()->id,
            'account_id' => auth()->user()->account_user()->account_id,
        ]);

        $contact = factory(\App\ClientContact::class)->create([
            'user_id' => auth()->user()->id,
            'account_id' => auth()->user()->account_user()->account_id,
            'customer_id' => $client->id,
            'is_primary' => 1,
            'send_email' => true,
        ]);

        $invoice = factory(\App\Invoice::class)->create([
            'user_id' => auth()->user()->id,
            'account_id' => auth()->user()->account_user()->account_id,
            'customer_id' => 3,
        ]);

        $invoice_design = new Custom((object)request()->input('design'));

        $designer = new Designer($invoice, $invoice_design, $invoice->customer->getSetting('pdf_variables'),
            lcfirst(request()->has('entity')));

        $html = $this->generateInvoiceHtml($designer->build()->getHtml(), $invoice);

        $file_path = PreviewPdf::dispatchNow($html, auth()->user()->account_user()->account);

        $invoice->forceDelete();
        $contact->forceDelete();
        $client->forceDelete();
        return response()->json(['data' => base64_encode(file_get_contents($file_path))]);


    }

}
