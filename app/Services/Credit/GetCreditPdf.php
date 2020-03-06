<?php

namespace App\Services\Credit;

use App\ClientContact;
use App\Credit;
use App\Jobs\Credit\CreateCreditPdf;
use App\Services\AbstractService;
use Illuminate\Support\Facades\Storage;

class GetCreditPdf extends AbstractService
{
    private $contact;
    private $credit;

    public function __construct(Credit $credit, ClientContact $contact = null)
    {
        $this->contact = $contact;
        $this->credit = $credit;
    }

    public function run()
    {
        if (!$this->contact) {
            $this->contact = $this->credit->customer->primary_contact()->first();
        }

        $path = $this->credit->customer->credit_filepath();
        $file_path = $path . $this->credit->number . '.pdf';
        $disk = config('filesystems.default');
        $file = Storage::disk($disk)->exists($file_path);

        if (!$file) {
            $file_path = CreateCreditPdf::dispatchNow($this->credit, $this->credit->account, $this->contact);
        }

        //return Storage::disk($disk)->path($file_path);
        return $file_path;
    }

}
