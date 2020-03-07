<?php

namespace App\Traits;

use App\Designs\Designer;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Factory;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

/**
 * Class MakesInvoiceHtml.
 */

trait MakesInvoiceHtml
{

    /**
     * Generate the HTML invoice parsing variables
     * and generating the final invoice HTML
     *
     * @param  string $design either the path to the design template, OR the full design template string
     * @param  Collection $invoice  The invoice object
     *
     * @return string           The invoice string in HTML format
     */
    public function generateInvoiceHtml($design, $invoice, $contact = null) :string
    {
        //$variables = array_merge($invoice->makeLabels(), $invoice->makeValues());
        //$design = str_replace(array_keys($variables), array_values($variables), $design);
        $invoice->load('client');

        $client = $invoice->client;

        App::setLocale($client->preferredLocale());

        $labels = $invoice->makeLabels();
        $values = $invoice->makeValues($contact);

        $design = str_replace(array_keys($labels), array_values($labels), $design);
        $design = str_replace(array_keys($values), array_values($values), $design);

        $data['invoice'] = $invoice;
        $data['lang'] = $client->preferredLocale();

        return $this->renderView($design, $data);

        //return view($design, $data)->render();
    }

    /**
     * @param Designer $designer
     * @param $entity
     * @param null $contact
     * @return string
     */
    public function generateEntityHtml(Designer $designer, $entity, $contact = null) :string
    {

        $entity->load('customer');
        
        $client = $entity->customer;

        App::setLocale($client->preferredLocale());

        $labels = $entity->makeLabels();
        $values = $entity->makeValues($contact);

        $css_url = url('').'/css/design/'.$designer->design_name.'.css';
        $css_url = "<link href=\"{$css_url}\" rel=\"stylesheet\">";

        $data = [];
        $data['entity'] = $entity;
        $data['lang'] = $client->preferredLocale();
        $data['includes'] = $this->parseLabelsAndValues($labels, $values, $designer->init()->getIncludes()->getHtml());
        $data['includes'] = str_replace('$css_url', $css_url, $data['includes']);
        $data['header'] = $this->parseLabelsAndValues($labels, $values, $designer->init()->getHeader()->getHtml());
        $data['body'] = $this->parseLabelsAndValues($labels, $values, $designer->init()->getBody()->getHtml());
        $data['product'] = $this->parseLabelsAndValues($labels, $values, $designer->init()->getProductTable());
        $data['task'] = $this->parseLabelsAndValues($labels, $values, $designer->init()->getTaskTable());
        $data['footer'] = $this->parseLabelsAndValues($labels, $values, $designer->init()->getFooter()->getHtml());



        return view('pdf.stub', $data)->render();
    }

    private function parseLabelsAndValues($labels, $values, $section) :string
    {
        $section = str_replace(array_keys($labels), array_values($labels), $section);
        $section = str_replace(array_keys($values), array_values($values), $section);
        return $section;
    }

    /**
     * Parses the blade file string and processes the template variables
     *
     * @param  string $string The Blade file string
     * @param  array $data   The array of template variables
     * @return string         The return HTML string
     *
     */
    public function renderView($string, $data = []) :string
    {

        $data['__env'] = app(\Illuminate\View\Factory::class);

        $php = Blade::compileString($string);

        $obLevel = ob_get_level();
        ob_start();
        extract($data, EXTR_SKIP);

        try {
            eval('?' . '>' . $php);
        } catch (\Exception $e) {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }

            throw $e;
        } catch (\Throwable $e) {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }

            throw new FatalThrowableError($e);
        }

        return ob_get_clean();
    }
}
