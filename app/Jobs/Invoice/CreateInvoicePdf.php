<?php
namespace App\Jobs\Invoice;

use App\Designs\Designer;
use App\Designs\Modern;
use App\Invoice;
use App\Account;
use App\Payment;
use App\Repositories\InvoiceRepository;
use App\Traits\NumberFormatter;
use App\Traits\MakesInvoiceHtml;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class CreateInvoicePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NumberFormatter, MakesInvoiceHtml;
    public $invoice;
    private $account;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice, Account $account)
    {
        $this->invoice = $invoice;
        $this->account = $account;
    }

    public function handle()
    {

        $path = 'public/' . $this->invoice->customer->id . '/invoices/';
        $file_path = $path . $this->invoice->number . '.pdf';

        $modern = new Modern();
        $designer = new Designer($modern, $this->invoice->customer->getSetting('invoice_variables'));

        //get invoice design
        $html = $this->generateInvoiceHtml($designer->build($this->invoice)->getHtml(), $this->invoice);

        //todo - move this to the client creation stage so we don't keep hitting this unnecessarily
        Storage::makeDirectory($path, 0755);
        //create pdf
        $pdf = $this->makePdf(null, null, $html);
        
        $response = Storage::put($file_path, $pdf);

        echo $file_path;
        die;

        return $file_path;
    }

    /**
     * Returns a PDF stream
     *
     * @param  string $header Header to be included in PDF
     * @param  string $footer Footer to be included in PDF
     * @param  string $html The HTML object to be converted into PDF
     *
     * @return string        The PDF string
     */
    private function makePdf($header, $footer, $html)
    {

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($html);
        return $pdf->stream();
    }
}
