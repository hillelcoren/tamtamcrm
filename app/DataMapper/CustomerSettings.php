<?php

namespace App\DataMapper;

use App\DataMapper\CompanySettings;
use App\Customer;
use stdClass;

/**
 * ClientSettings
 *
 * Client settings are built as a superset of Company Settings
 *
 * If no client settings is specified, the default company setting is used.
 *
 * Client settings are passed down to the entity level where they can be further customized and then saved
 * into the settings column of the entity, so there is no need to create additional entity level settings handlers.
 *
 */
class CustomerSettings extends BaseSettings
{
    /**
     * Settings which which are unique to client settings
     */
    public $industry_id;
    public $size_id;
    public static $casts = [
        'industry_id' => 'string',
        'size_id' => 'string',
        'counter_padding' => 'int',
        'invoice_number_pattern' => 'string',
        'quote_number_pattern' => 'string',
        'quote_number_applied' => 'string',
        'invoice_number_applied' => 'string',
        'quote_number_counter' => 'int',
        'invoice_number_counter' => 'int'

    ];

    /**
     * Cast object values and return entire class
     * prevents missing properties from not being returned
     * and always ensure an up to date class is returned
     *
     * @return stdClass
     */
    public function __construct($obj)
    {
        parent::__construct($obj);
    }

    /**
     *
     * Default Client Settings scaffold
     *
     * @return stdClass
     *
     */
    public static function defaults(): stdClass
    {
        $data = (object)[
            'entity' => (string)Customer::class,
            'industry_id' => '',
            'size_id' => '',
            'counter_padding' => 6,
            'invoice_number_pattern' => '{$clientCounter}',
            'quote_number_pattern' => '{$clientCounter}',
            'quote_number_applied' => 'when_saved',
            'invoice_number_applied' => 'when_saved',
            'quote_number_counter' => 1,
            'invoice_number_counter' => 1,
        ];
        return self::setCasts($data, self::$casts);
    }

    /**
     * Merges settings from Company to Client
     *
     * @param stdClass $company_settings
     * @param stdClass $client_settings
     * @return stdClass of merged settings
     */
    public static function buildCustomerSettings($company_settings, $client_settings)
    {
        if (!$client_settings) {
            return $company_settings;
        }

        foreach ($company_settings as $key => $value) {
            /* pseudo code
            if the property exists and is a string BUT has no length, treat it as TRUE
            */
            if (((property_exists($client_settings, $key) && is_string($client_settings->{$key}) &&
                    (iconv_strlen($client_settings->{$key}) < 1))) ||
                !isset($client_settings->{$key}) && property_exists($company_settings, $key)) {
                $client_settings->{$key} = $company_settings->{$key};
            }
        }

        return $client_settings;
    }
}
