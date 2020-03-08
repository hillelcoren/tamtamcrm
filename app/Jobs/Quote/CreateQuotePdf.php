<?php

namespace App\Jobs\Quote;

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

class CreateQuotePdf implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NumberFormatter, MakesInvoiceHtml, PdfMaker;

    public $quote;

    public $account;

    public $contact;

    private $disk;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($quote, Account $account, ClientContact $contact = null)
    {

        $this->quote = $quote;

        $this->account = $account;

        $this->contact = $contact;

        $this->disk = $disk ?? config('filesystems.default');

    }

    public function handle()
    {


        $settings = $this->quote->customer->getMergedSettings();

        $this->quote->load('customer');

        if (!$this->contact) {
            $this->contact = $this->quote->customer->primary_contact()->first();
        }

        App::setLocale($this->contact->preferredLocale());

        $path = $this->quote->customer->quote_filepath();

        $design = Design::find($this->quote->customer->getSetting('quote_design_id'));

        $designer = new Designer($this->quote, $design, $this->quote->customer->getSetting('pdf_variables'), 'quote');

        //todo - move this to the client creation stage so we don't keep hitting this unnecessarily
        Storage::makeDirectory($path, 0755);

        //\Log::error($html);

        $all_pages_header = $settings->all_pages_header;
        $all_pages_footer = $settings->all_pages_footer;

        $quote_number = $this->quote->number;


        // if($all_pages_header && $all_pages_footer){
        // 	$all_pages_header = $designer->init()->getHeader()->getHtml();
        // 	$all_pages_footer = $designer->init()->getFooter()->getHtml();
        // 	$design_body = $designer->init()->getBody()->getHtml();
        // 	$quote_number = "header_and_footer";
        // }
        // elseif($all_pages_header){
        // 	$all_pages_header = $designer->init()->getHeader()->getHtml();
        // 	$design_body = $designer->init()->getBody()->getFooter()->getHtml();
        // 	$quote_number = "header_only";
        // }
        // elseif($all_pages_footer){
        // 	$all_pages_footer = $designer->init()->getFooter()->getHtml();
        // 	$design_body = $designer->init()->getHeader()->getBody()->getHtml();
        // 	$quote_number = "footer_only";
        // }
        // else{
        $design_body = $designer->build()->getHtml();


        //get invoice design
//		$html = $this->generateInvoiceHtml($design_body, $this->quote, $this->contact);
        $html = $this->generateEntityHtml($designer, $this->quote, $this->contact);

        $pdf = $this->makePdf($all_pages_header, $all_pages_footer, $html);
        $file_path = $path . $quote_number . '.pdf';

        $instance = Storage::disk($this->disk)->put($file_path, $pdf);

        //$instance= Storage::disk($this->disk)->path($file_path);
        //
        return $file_path;
    }
}
