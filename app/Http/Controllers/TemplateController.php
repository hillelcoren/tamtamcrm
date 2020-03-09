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
use League\CommonMark\CommonMarkConverter;


class TemplateController extends Controller
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
        if (request()->has('entity') && request()->has('entity_id')) {
            $class = 'App\\' . ucfirst(request()->input('entity'));
            $entity_obj = $class::whereId(request()->input('entity_id'))->first();
        }

        $subject = request()->input('subject') ?: '';
        $body = request()->input('body') ?: '';

        $converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $data = [
            'subject' => request()->input('subject'),
            'body' => $converter->convertToHtml($body),
        ];

        return response()->json($data, 200);

    }
}
