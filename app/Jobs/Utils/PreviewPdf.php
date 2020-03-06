<?php

namespace App\Jobs\Utils;

use App\Account;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\MakesInvoiceHtml;
use App\Traits\NumberFormatter;
use App\Traits\Pdf\PdfMaker;

class PreviewPdf implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NumberFormatter, MakesInvoiceHtml, PdfMaker;

    public $invoice;

    public $company;

    public $contact;

    private $disk;

    public $design_string;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($design_string, Account $company)
    {

        $this->company = $company;

        $this->design_string = $design_string;

        $this->disk = $disk ?? config('filesystems.default');

    }

    public function handle()
    {


        $path = $this->company->company_key;

//Storage::makeDirectory($path, 0755);

        $file_path = $path . '/stream.pdf';

        $pdf = $this->makePdf(null, null, $this->design_string);

        $instance = Storage::disk('local')->put($file_path, $pdf);

        return storage_path('app') . '/' . $file_path;
    }


}
