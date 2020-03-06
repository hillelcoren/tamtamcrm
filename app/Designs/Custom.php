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

namespace App\Designs;

class Custom extends AbstractDesign
{
    private $include;

    private $header;

    private $body;

    private $product_table;

    private $footer;

    private $table_styles;

    public function __construct($design)
    {
        $this->include = isset($design->include) ? $design->include : '';

        $this->header = isset($design->header) ? $design->header : '';

        $this->body = isset($design->body) ? $design->body : '';

        $this->product_table = isset($design->product_table) ? $design->product_table : '';

        $this->task_table = isset($design->task_table) ? $design->task_table : '';

        $this->footer = isset($design->footer) ? $design->footer : '';

        $this->table_styles = isset($design->table_styles) ? $design->table_styles : '';

    }

    public function include()
    {
        return $this->include;
    }

    public function header()
    {

        return $this->header;

    }

    public function body()
    {

        return $this->body;

    }

    public function table_styles()
    {

        return $this->table_styles;

    }

    public function task_table()
    {

    }

    public function product_table()
    {

        return $this->product_table;

    }

    public function footer()
    {

        return $this->footer;

    }

}
