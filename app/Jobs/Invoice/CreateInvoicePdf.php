<?php

namespace App\Jobs\Invoice;

use App\Designs\Custom;
use App\Designs\Designer;
use App\Traits\Pdf\PdfMaker;
use App\Design;
use App\Account;
use App\Traits\NumberFormatter;
use App\Traits\MakesInvoiceHtml;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use App\ClientContact;

class CreateInvoicePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NumberFormatter, MakesInvoiceHtml, PdfMaker;

    public $invoice;

    private $disk;

    private $account;

    public $contact;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($invoice, Account $account, ClientContact $contact = null, $disk = 'public')
    {
        $this->invoice = $invoice;
        $this->account = $account;
        $this->contact = $contact;
        $this->disk = $disk ?? config('filesystems.default');
    }

    public function handle()
    {

        $this->invoice->load('customer');

        if (!$this->contact) {
            $this->contact = $this->invoice->customer->primary_contact()->first();
        }

        App::setLocale($this->contact->preferredLocale());

        $path = $this->invoice->customer->invoice_filepath();

        $file_path = $path . $this->invoice->number . '.pdf';

        $design = Design::find($this->invoice->customer->getSetting('invoice_design_id'));

        $designer =
            new Designer($this->invoice, $design, $this->invoice->customer->getSetting('pdf_variables'), 'invoice');

        //get invoice design
        //$html = $this->generateInvoiceHtml($designer->build()->getHtml(), $this->invoice, $this->contact);
        $html = $this->generateEntityHtml($designer, $this->invoice, $this->contact);

        //todo - move this to the client creation stage so we don't keep hitting this unnecessarily
        Storage::makeDirectory($path, 0755);

        //\Log::error($html);
        $pdf = $this->makePdf(null, null, $html);

        $instance = Storage::disk($this->disk)->put($file_path, $pdf);

        return $file_path;

    }
}
