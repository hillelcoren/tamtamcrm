<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/api/tasks/deal',
        '/api/invoice/bulk',
        '/api/tasks/form',
        '/api/tasks/*',
        '/api/categories/products/*'
    ];

}
