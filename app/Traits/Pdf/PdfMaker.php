<?php


namespace App\Traits\Pdf;

use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\App;

trait PdfMaker
{

    /**
     * Returns a PDF stream
     *
     * @param string $header Header to be included in PDF
     * @param string $footer Footer to be included in PDF
     * @param string $html The HTML object to be converted into PDF
     *
     * @return string        The PDF string
     */
    public function makePdf($header, $footer, $html)
    {
//        echo $html;
//        die;

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($html);
        return $pdf->stream();

//        return Browsershot::html($html)
        //->showBrowserHeaderAndFooter()
        //->headerHtml($header)
        //->footerHtml($footer)
//            ->deviceScaleFactor(1)
//            ->showBackground()
//            ->waitUntilNetworkIdle(true)->pdf();
        //->margins(10,10,10,10)
        //->savePdf('test.pdf');
    }

}
