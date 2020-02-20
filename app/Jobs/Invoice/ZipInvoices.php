<?php

namespace App\Jobs\Invoice;

use App\Mail\DownloadInvoices;
use App\Account;
use App\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use ZipStream\Option\Archive;
use ZipStream\ZipStream;
use Illuminate\Support\Facades\Mail;

class ZipInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $invoices;

    private $account;

    /**
     * @return void
     * @deprecated confirm to be deleted
     * Create a new job instance.
     *
     */
    public function __construct($invoices, Account $account)
    {
        $this->invoices = $invoices;

        $this->account = $account;
    }

    /**
     * Execute the job.
     *
     *
     * @return void
     */
    public function handle()
    {

        $tempStream = fopen('php://memory', 'w+');

        $options = new Archive();
        $options->setOutputStream($tempStream);

        # create a new zipstream object
        $file_name = date('Y-m-d') . '_' . str_replace(' ', '_', trans('texts.invoices')) . ".zip";

        $path = $this->invoices->first()->customer->invoice_filepath();

        $zip = new ZipStream($file_name, $options);

        foreach ($this->invoices as $invoice) {
            $zip->addFileFromPath(basename($invoice->pdf_file_path()), public_path($invoice->pdf_file_path()));
        }

        $zip->finish();

        Storage::disk(config('filesystems.default'))->put($path . $file_name, $tempStream);

        fclose($tempStream);

        //fire email here
        Mail::to(config('taskmanager.contact.ninja_official_contact'))
            ->send(new DownloadInvoices(Storage::disk(config('filesystems.default'))->url($path . $file_name)));

    }
}
