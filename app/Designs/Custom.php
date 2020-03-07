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
    private $includes;

    private $header;

    private $body;

    private $product;

    private $task;

    private $footer;

    private $table_styles;

    public function __construct($design)
    {
        $this->includes = isset($design->includes) ? $design->includes : '';

        $this->header = isset($design->header) ? $design->header : '';

        $this->body = isset($design->body) ? $design->body : '';

        $this->product = isset($design->product) ? $design->product : '';

        $this->task = isset($design->task) ? $design->task : '';

        $this->footer = isset($design->footer) ? $design->footer : '';

        $this->table_styles = isset($design->table_styles) ? $design->table_styles : '';

    }

   public function includes()
	{
		return $this->includes;
	}

	public function header() 
	{

		return $this->header;
			
	}

	public function body() 
	{

		return $this->body;	

	}

	public function product() 
	{

		return $this->product;

	}

	public function task()
	{
		return $this->task;
	}

	public function footer() 
	{

		return $this->footer;
		
	}
}
