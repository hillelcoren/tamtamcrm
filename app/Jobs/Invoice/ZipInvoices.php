<?php

namespace App\Jobs\Invoice;

use App\Mail\DownloadInvoices;
use App\Jobs\Utils\UnlinkFile;
use App\Account;
use App\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use ZipStream\Option\Archive;
use ZipStream\ZipStream;

class ZipInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $invoices;

    private $account;

    private $email;

    /**
     * @return void
     * @deprecated confirm to be deleted
     * Create a new job instance.
     *
     */
    public function __construct($invoices, Account $account, $email)
    {
        $this->invoices = $invoices;
        $this->account = $account;
        $this->email = $email;
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
        Mail::to($this->email)->send(new DownloadInvoices(Storage::disk(config('filesystems.default'))->url($path .
            $file_name), $this->account));

        UnlinkFile::dispatch(config('filesystems.default'), $path . $file_name)->delay(now()->addHours(1));

    }
}
