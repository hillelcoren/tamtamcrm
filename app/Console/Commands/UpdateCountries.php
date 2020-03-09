<?php

namespace App\Console\Commands;

//use App\Models\Account;
use App\Country;
use App\Invoice;
use App\Repositories\InvoiceRepository;
use App\Services\InvoiceService;
use DateTime;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Auth;
use Exception;
use App\Libraries\Utils;
use App\Jobs\Cron\RecurringInvoicesCron;

/**
 * Class SendRecurringInvoices.
 */
class UpdateCountries extends Command
{

    /**
     * @var string
     */
    protected $name = 'update-countries';

    /**
     * @var string
     */
    protected $description = 'update countries';


    public function handle()
    {

        $countries = [
            'AR' => [
                'swap_postal_code' => true,
            ],
            'AT' => [ // Austria
                      'swap_postal_code' => true,
                      'swap_currency_symbol' => true,
            ],
            'BE' => [
                'swap_postal_code' => true,
            ],
            'BG' => [ // Belgium
                      'swap_currency_symbol' => true,
            ],
            'CA' => [
                'thousand_separator' => ',',
                'decimal_separator' => '.',
            ],
            'CH' => [
                'swap_postal_code' => true,
            ],
            'CZ' => [ // Czech Republic
                      'swap_currency_symbol' => true,
            ],
            'DE' => [ // Germany
                      'swap_postal_code' => true,
                      'swap_currency_symbol' => true,
            ],
            'DK' => [
                'swap_postal_code' => true,
            ],
            'EE' => [ // Estonia
                      'swap_currency_symbol' => true,
                      'thousand_separator' => ' ',
            ],
            'ES' => [ // Spain
                      'swap_postal_code' => true,
                      'swap_currency_symbol' => true,
            ],
            'FI' => [ // Finland
                      'swap_postal_code' => true,
                      'swap_currency_symbol' => true,
            ],
            'FR' => [ // France
                      'swap_postal_code' => true,
                      'swap_currency_symbol' => true,
            ],
            'GR' => [ // Greece
                      'swap_currency_symbol' => true,
            ],
            'HR' => [ // Croatia
                      'swap_currency_symbol' => true,
            ],
            'HU' => [ // Hungary
                      'swap_currency_symbol' => true,
            ],
            'GL' => [
                'swap_postal_code' => true,
            ],
            'IE' => [ // Ireland
                      'thousand_separator' => ',',
                      'decimal_separator' => '.',
            ],
            'IL' => [
                'swap_postal_code' => true,
            ],
            'IS' => [ // Iceland
                      'swap_postal_code' => true,
                      'swap_currency_symbol' => true,
            ],
            'IT' => [ // Italy
                      'swap_postal_code' => true,
                      'swap_currency_symbol' => true,
            ],
            'JP' => [ // Japan
                      'swap_postal_code' => true,
                      'swap_currency_symbol' => true,
            ],
            'LT' => [ // Lithuania
                      'swap_currency_symbol' => true,
            ],
            'LU' => [
                'swap_postal_code' => true,
            ],
            'MT' => [
                'thousand_separator' => ',',
                'decimal_separator' => '.',
            ],
            'MY' => [
                'swap_postal_code' => true,
            ],
            'MX' => [
                'swap_postal_code' => true,
            ],
            'NL' => [
                'swap_postal_code' => true,
            ],
            'PL' => [ // Poland
                      'swap_postal_code' => true,
                      'swap_currency_symbol' => true,
            ],
            'PT' => [ // Portugal
                      'swap_postal_code' => true,
                      'swap_currency_symbol' => true,
            ],
            'RO' => [ // Romania
                      'swap_currency_symbol' => true,
            ],
            'SE' => [ // Sweden
                      'swap_postal_code' => true,
                      'swap_currency_symbol' => true,
            ],
            'SI' => [ // Slovenia
                      'swap_currency_symbol' => true,
            ],
            'SK' => [ // Slovakia
                      'swap_currency_symbol' => true,
            ],
            'US' => [
                'thousand_separator' => ',',
                'decimal_separator' => '.',
            ],
            'SR' => [ // Suriname
                      'swap_currency_symbol' => true,
            ],
            'UY' => [
                'swap_postal_code' => true,
            ],
        ];

        foreach ($countries as $key => $data) {

            $country = Country::where('iso', '=', $key)->first();

            if (isset($data['swap_postal_code'])) {
                $country->swap_postal_code = true;
            }
            if (isset($data['swap_currency_symbol'])) {
                $country->swap_currency_symbol = true;
            }
            if (isset($data['thousand_separator'])) {
                $country->thousand_separator = $data['thousand_separator'];
            }
            if (isset($data['decimal_separator'])) {
                $country->decimal_separator = $data['decimal_separator'];
            }
            $country->save();

        }

        return true;
    }
}
