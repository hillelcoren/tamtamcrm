<?php

namespace App\Jobs\Credit;

use App\Designs\Custom;
use App\Designs\Designer;
use App\ClientContact;
use App\Account;
use App\Design;
use App\Traits\Pdf\PdfMaker;
use App\Traits\MakesInvoiceHtml;
use App\Traits\NumberFormatter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class CreateCreditPdf implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NumberFormatter, MakesInvoiceHtml, PdfMaker;

    public $credit;

    public $account;

    public $contact;

    private $disk;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($credit, Account $account, ClientContact $contact = null)
    {
        $this->credit = $credit;
        $this->account = $account;
        $this->contact = $contact;
        $this->disk = $disk ?? config('filesystems.default');

    }

    public function handle()
    {
        if (!$this->contact) {
            $this->contact = $this->credit->customer->primary_contact()->first();
        }

        App::setLocale($this->contact->preferredLocale());

        $path = $this->credit->customer->credit_filepath();

        $file_path = $path . $this->credit->number . '.pdf';

        $design = Design::find($this->credit->customer->getSetting('credit_design_id'));

        $designer =
            new Designer($this->credit, $design, $this->credit->customer->getSetting('pdf_variables'), 'credit');

        //get invoice design
        $html = $this->generateEntityHtml($designer, $this->credit, $this->contact);

        //todo - move this to the client creation stage so we don't keep hitting this unnecessarily
        Storage::makeDirectory($path, 0755);

        //\Log::error($html);
        $pdf = $this->makePdf(null, null, $html);

        $instance = Storage::disk($this->disk)->put($file_path, $pdf);

        //$instance= Storage::disk($this->disk)->path($file_path);
        //
        return $file_path;
    }

}
