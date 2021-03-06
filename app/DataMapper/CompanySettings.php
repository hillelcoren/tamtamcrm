<?php

namespace App\DataMapper;

use App\DataMapper\CompanySettings;
use App\DataMapper\EmailTemplateDefaults;
use App\Models\Company;
use stdClass;

/**
 * CompanySettings
 */
class CompanySettings extends BaseSettings
{
    /*Group settings based on functionality*/
    /*Invoice*/
    public $auto_archive_invoice = false;
    public $auto_archive_lead = false;
    public $email_subject_payment_partial = '';
    public $email_template_payment_partial = '';
    public $lock_sent_invoices = false;
    public $update_products = true;
    public $fill_products = true;
    public $default_quantity = true;
    public $convert_products = true;
    public $show_product_quantity = true;
    public $show_cost = true;
    public $enable_client_portal_tasks = false;
    public $enable_client_portal_password = false;
    public $enable_client_portal = true; //implemented
    public $enable_client_portal_dashboard = true; //implemented
    public $signature_on_pdf = false;
    public $document_email_attachment = false;
    public $send_portal_password = false;
    public $timezone_id = '';
    public $portal_design_id = '1';
    public $date_format_id = '';
    public $military_time = false;
    public $language_id = '';
    public $show_currency_code = false;
    public $company_gateway_ids = '';
    public $currency_id = '1';
    public $custom_value1 = '';
    public $custom_value2 = '';
    public $custom_value3 = '';
    public $custom_value4 = '';
    public $default_task_rate = 0;
    public $payment_terms = 1;
    public $send_reminders = false;
    public $custom_message_dashboard = '';
    public $custom_message_unpaid_invoice = '';
    public $custom_message_paid_invoice = '';
    public $custom_message_unapproved_quote = '';
    public $auto_archive_quote = false;
    public $auto_convert_quote = true;
    public $auto_email_invoice = true;
    public $inclusive_taxes = false;
    public $quote_footer = '';
    public $credit_footer = '';
    public $credit_terms = '';
    public $translations;
    public $counter_number_applied = 'when_saved'; // when_saved , when_sent , when_paid
    public $quote_number_applied = 'when_saved'; // when_saved , when_sent
    /* Counters */
    public $invoice_number_pattern = '';
    public $invoice_number_counter = 1;
    public $quote_number_pattern = '';
    public $quote_number_counter = 1;
    public $client_number_pattern = '';
    public $client_number_counter = 1;
    public $credit_number_pattern = '';
    public $credit_number_counter = 1;
    public $task_number_pattern = '';
    public $task_number_counter = 1;
    public $expense_number_pattern = '';
    public $expense_number_counter = 1;
    public $vendor_number_pattern = '';
    public $vendor_number_counter = 1;
    public $ticket_number_pattern = '';
    public $ticket_number_counter = 1;
    public $payment_number_pattern = '';
    public $payment_number_counter = 1;
    public $shared_invoice_quote_counter = false;
    public $recurring_number_prefix = 'R';
    public $reset_counter_frequency_id = '0';
    public $reset_counter_date = '';
    public $counter_padding = 4;
    public $design = 'views/pdf/design1.blade.php';
    public $invoice_terms = '';
    public $quote_terms = '';
    public $invoice_taxes = 0;
    public $enabled_item_tax_rates = 0;
    public $invoice_design_id = '1';
    public $quote_design_id = '1';
    public $credit_design_id = '1';
    public $invoice_footer = '';
    public $invoice_labels = '';
    public $tax_name1 = '';
    public $tax_rate1 = 0;
    public $tax_name2 = '';
    public $tax_rate2 = 0;
    public $tax_name3 = '';
    public $tax_rate3 = 0;
    public $type_id = '1';
    public $invoice_fields = '';
    public $show_accept_invoice_terms = false;
    public $show_accept_quote_terms = false;
    public $require_invoice_signature = false;
    public $require_quote_signature = false;

    //email settings
    public $email_sending_method = 'default'; //enum 'default','gmail'
    public $gmail_sending_user_id = '';

    //email settings
    public $reply_to_email = '';
    public $bcc_email = '';
    public $pdf_email_attachment = false;
    public $ubl_email_attachment = false;
    public $email_style = 'plain'; //plain, light, dark, custom
    public $email_style_custom = ''; //the template itself
    public $email_subject_invoice = '';
    public $email_subject_quote = '';
    public $email_subject_payment = '';
    public $email_subject_order = '';
    public $email_subject_statement = '';
    public $email_template_invoice = '';
    public $email_template_quote = '';
    public $email_template_payment = '';
    public $email_template_order = '';
    public $email_template_statement = '';
    public $email_subject_reminder1 = '';
    public $email_subject_reminder2 = '';
    public $email_subject_reminder3 = '';
    public $email_subject_reminder_endless = '';
    public $email_template_reminder1 = '';
    public $email_template_reminder2 = '';
    public $email_template_reminder3 = '';
    public $email_template_reminder_endless = '';
    public $email_signature = '';
    public $enable_email_markup = true;
    public $email_subject_custom1 = '';
    public $email_subject_custom2 = '';
    public $email_subject_custom3 = '';
    public $email_template_custom1 = '';
    public $email_template_custom2 = '';
    public $email_template_custom3 = '';
    public $enable_reminder1 = false;
    public $enable_reminder2 = false;
    public $enable_reminder3 = false;
    public $num_days_reminder1 = 0;
    public $num_days_reminder2 = 0;
    public $num_days_reminder3 = 0;
    public $schedule_reminder1 = ''; // (enum: after_invoice_date, before_due_date, after_due_date)
    public $schedule_reminder2 = ''; // (enum: after_invoice_date, before_due_date, after_due_date)
    public $schedule_reminder3 = ''; // (enum: after_invoice_date, before_due_date, after_due_date)
    public $reminder_send_time = 32400; //number of seconds from UTC +0 to send reminders
    public $late_fee_amount1 = 0;
    public $late_fee_amount2 = 0;
    public $late_fee_amount3 = 0;
    public $endless_reminder_frequency_id = '0';
    public $client_online_payment_notification = true;
    public $client_manual_payment_notification = true;

    /* Company Meta data that we can use to build sub companies*/
    public $name = '';
    public $company_logo = '';
    public $website = '';
    public $address1 = '';
    public $address2 = '';
    public $city = '';
    public $state = '';
    public $postal_code = '';
    public $phone = '';
    public $email = '';
    public $country_id;
    public $vat_number = '';
    public $id_number = '';
    public $page_size = 'A4';
    public $font_size = 9;
    public $primary_font = 'Roboto';
    public $secondary_font = 'Roboto';
    public $hide_paid_to_date = false;
    public $embed_documents = false;
    public $all_pages_header = true;
    public $all_pages_footer = true;

    public $pdf_variables = [];

    public static $casts = [
        'email_subject_payment_partial' => 'string',
        'email_template_payment_partial' => 'string',
        'portal_design_id' => 'string',
        'fill_products' => 'bool',
        'update_products' => 'bool',
        'default_quantity' => 'bool',
        'convert_products' => 'bool',
        'show_product_quantity' => 'bool',
        'show_cost' => 'bool',
        'auto_email_invoice' => 'bool',
        'reminder_send_time' => 'int',
        'email_sending_method' => 'string',
        'gmail_sending_user_id' => 'string',
        'currency_id' => 'string',
        'counter_number_applied' => 'string',
        'quote_number_applied' => 'string',
        'email_subject_custom1' => 'string',
        'email_subject_custom2' => 'string',
        'email_subject_custom3' => 'string',
        'email_template_custom1' => 'string',
        'email_template_custom2' => 'string',
        'email_template_custom3' => 'string',
        'enable_reminder1' => 'bool',
        'enable_reminder2' => 'bool',
        'enable_reminder3' => 'bool',
        'num_days_reminder1' => 'int',
        'num_days_reminder2' => 'int',
        'num_days_reminder3' => 'int',
        'schedule_reminder1' => 'string', // (enum: after_invoice_date, before_due_date, after_due_date)
        'schedule_reminder2' => 'string', // (enum: after_invoice_date, before_due_date, after_due_date)
        'schedule_reminder3' => 'string', // (enum: after_invoice_date, before_due_date, after_due_date)
        'late_fee_amount1' => 'float',
        'late_fee_amount2' => 'float',
        'late_fee_amount3' => 'float',
        'endless_reminder_frequency_id' => 'integer',
        'client_online_payment_notification' => 'bool',
        'client_manual_payment_notification' => 'bool',
        'document_email_attachment' => 'bool',
        'enable_client_portal_password' => 'bool',
        'enable_email_markup' => 'bool',
        'enable_client_portal_dashboard' => 'bool',
        'enable_client_portal' => 'bool',
        'email_template_statement' => 'string',
        'email_subject_statement' => 'string',
        'signature_on_pdf' => 'bool',
        'send_portal_password' => 'bool',
        'quote_footer' => 'string',
        'page_size' => 'string',
        'font_size' => 'int',
        'primary_font' => 'string',
        'secondary_font' => 'string',
        'hide_paid_to_date' => 'bool',
        'embed_documents' => 'bool',
        'all_pages_header' => 'bool',
        'all_pages_footer' => 'bool',
        'task_number_pattern' => 'string',
        'task_number_counter' => 'int',
        'expense_number_pattern' => 'string',
        'expense_number_counter' => 'int',
        'vendor_number_pattern' => 'string',
        'vendor_number_counter' => 'int',
        'ticket_number_pattern' => 'string',
        'ticket_number_counter' => 'int',
        'payment_number_pattern' => 'string',
        'payment_number_counter' => 'int',
        'reply_to_email' => 'string',
        'bcc_email' => 'string',
        'pdf_email_attachment' => 'bool',
        'ubl_email_attachment' => 'bool',
        'email_style' => 'string',
        'email_style_custom' => 'string',
        'company_gateway_ids' => 'string',
        'address1' => 'string',
        'address2' => 'string',
        'city' => 'string',
        'company_logo' => 'string',
        'country_id' => 'string',
        'client_number_pattern' => 'string',
        'client_number_counter' => 'integer',
        'credit_number_pattern' => 'string',
        'credit_number_counter' => 'integer',
        'custom_value1' => 'string',
        'custom_value2' => 'string',
        'custom_value3' => 'string',
        'custom_value4' => 'string',
        'custom_message_dashboard' => 'string',
        'custom_message_unpaid_invoice' => 'string',
        'custom_message_paid_invoice' => 'string',
        'custom_message_unapproved_quote' => 'string',
        'default_task_rate' => 'float',
        'email_signature' => 'string',
        'email_subject_invoice' => 'string',
        'email_subject_quote' => 'string',
        'email_subject_payment' => 'string',
        'email_subject_order' => 'string',
        'email_template_invoice' => 'string',
        'email_template_quote' => 'string',
        'email_template_payment' => 'string',
        'email_template_order' => 'string',
        'email_subject_reminder1' => 'string',
        'email_subject_reminder2' => 'string',
        'email_subject_reminder3' => 'string',
        'email_subject_reminder_endless' => 'string',
        'email_template_reminder1' => 'string',
        'email_template_reminder2' => 'string',
        'email_template_reminder3' => 'string',
        'email_template_reminder_endless' => 'string',
        'inclusive_taxes' => 'bool',
        'invoice_number_pattern' => 'string',
        'invoice_number_counter' => 'integer',
        'invoice_design_id' => 'string',
        'credit_design_id' => 'string',
        'invoice_fields' => 'string',
        'invoice_taxes' => 'int',
        'enabled_item_tax_rates' => 'int',
        'invoice_footer' => 'string',
        'credit_footer' => 'string',
        'credit_terms' => 'string',
        'invoice_labels' => 'string',
        'invoice_terms' => 'string',
        'name' => 'string',
        'payment_terms' => 'integer',
        'type_id' => 'string',
        'phone' => 'string',
        'postal_code' => 'string',
        'quote_design_id' => 'string',
        'quote_number_pattern' => 'string',
        'quote_number_counter' => 'integer',
        'quote_terms' => 'string',
        'recurring_number_prefix' => 'string',
        'reset_counter_frequency_id' => 'integer',
        'reset_counter_date' => 'string',
        'require_invoice_signature' => 'bool',
        'require_quote_signature' => 'bool',
        'state' => 'string',
        'email' => 'string',
        'vat_number' => 'string',
        'id_number' => 'string',
        'tax_name1' => 'string',
        'tax_name2' => 'string',
        'tax_name3' => 'string',
        'tax_rate1' => 'float',
        'tax_rate2' => 'float',
        'tax_rate3' => 'float',
        'show_accept_quote_terms' => 'bool',
        'show_accept_invoice_terms' => 'bool',
        'timezone_id' => 'string',
        'date_format_id' => 'string',
        'military_time' => 'bool',
        'language_id' => 'string',
        'show_currency_code' => 'bool',
        'send_reminders' => 'bool',
        'enable_client_portal_tasks' => 'bool',
        'lock_sent_invoices' => 'bool',
        'auto_archive_invoice' => 'bool',
        'auto_archive_lead' => 'bool',
        'auto_archive_quote' => 'bool',
        'auto_convert_quote' => 'bool',
        'shared_invoice_quote_counter' => 'bool',
        'counter_padding' => 'integer',
        'design' => 'string',
        'website' => 'string',
        'pdf_variables' => 'array',
    ];
    /**
     * Array of variables which
     * cannot be modified client side
     */
    public static $protected_fields = [
        //	'credit_number_counter',
        //	'invoice_number_counter',
        //	'quote_number_counter',
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
        //	parent::__construct($obj);
    }

    /**
     * Provides class defaults on init
     * @return object
     */
    public static function defaults(): stdClass
    {
        $data = (object)get_class_vars(CompanySettings::class);

        unset($data->casts);
        unset($data->protected_fields);
        $data->timezone_id = (string)config('taskmanager.i18n.timezone_id');
        $data->currency_id = (string)config('taskmanager.i18n.currency_id');
        $data->language_id = (string)config('taskmanager.i18n.language_id');
        $data->payment_terms = (int)config('taskmanager.i18n.payment_terms');
        $data->military_time = (bool)config('taskmanager.i18n.military_time');
        $data->date_format_id = (string)config('taskmanager.i18n.date_format_id');
        $data->country_id = (string)config('taskmanager.i18n.country_id');
        $data->translations = (object)[];
        $data->pdf_variables = (array)self::getEntityVariableDefaults();

        /* $data->email_subject_invoice = EmailTemplateDefaults::emailInvoiceSubject();
        $data->email_template_invoice = EmailTemplateDefaults:: emailInvoiceTemplate();
        $data->email_subject_quote = EmailTemplateDefaults::emailQuoteSubject();
        $data->email_subject_payment = EmailTemplateDefaults::emailPaymentSubject();
        $data->email_subject_statement = EmailTemplateDefaults::emailStatementSubject();
        $data->email_template_quote = EmailTemplateDefaults::emailQuoteTemplate();
        $data->email_template_payment = EmailTemplateDefaults::emailPaymentTemplate();
        $data->email_template_statement = EmailTemplateDefaults::emailStatementTemplate();
        $data->email_subject_reminder1 = EmailTemplateDefaults::emailReminder1Subject();
        $data->email_subject_reminder2 = EmailTemplateDefaults::emailReminder2Subject();
        $data->email_subject_reminder3 = EmailTemplateDefaults::emailReminder3Subject();
        $data->email_subject_reminder_endless = EmailTemplateDefaults::emailReminderEndlessSubject();
        $data->email_template_reminder1 = EmailTemplateDefaults::emailReminder1Template();
        $data->email_template_reminder2 = EmailTemplateDefaults::emailReminder2Template();
        $data->email_template_reminder3 = EmailTemplateDefaults::emailReminder3Template();
        $data->email_template_reminder_endless = EmailTemplateDefaults::emailReminderEndlessTemplate();
        */

        return self::setCasts($data, self::$casts);
    }

    /**
     * In case we update the settings object in the future we
     * need to provide a fallback catch on old settings objects which will
     * set new properties to the object prior to being returned.
     *
     * @param object $data The settings object to be checked
     */
    public static function setProperties($settings): stdClass
    {
        $company_settings = (object)get_class_vars(CompanySettings::class);
        foreach ($company_settings as $key => $value) {
            if (!property_exists($settings, $key)) {
                $settings->{$key} = self::castAttribute($key, $company_settings->{$key});
            }

        }
        return $settings;
    }

    public static function notificationDefaults()
    {

        $notification = new \stdClass;
        $notification->email = ['all_notifications'];

        return $notification;
    }

    public static function getEntityVariableDefaults()
    {
        $variables = [
            'client_details' => [
                'name',
                'id_number',
                'vat_number',
                'address1',
                'address2',
                'city_state_postal',
                'country',
                'email',
            ],
            'company_details' => [
                'company_name',
                'id_number',
                'vat_number',
                'website',
                'email',
                'phone',
            ],
            'company_address' => [
                'address1',
                'address2',
                'city_state_postal',
                'country',
            ],
            'invoice_details' => [
                'invoice_number',
                'po_number',
                'date',
                'due_date',
                'balance_due',
                'invoice_total',
            ],
            'quote_details' => [
                'quote_number',
                'po_number',
                'date',
                'valid_until',
                'balance_due',
                'quote_total',
            ],
            'credit_details' => [
                'credit_number',
                'po_number',
                'date',
                'credit_balance',
                'credit_amount',
            ],
            'product_columns' => [
                'product_key',
                'notes',
                'cost',
                'quantity',
                'discount',
                'tax_name1',
                'line_total'
            ],
            'task_columns' => [
                'product_key',
                'notes',
                'cost',
                'quantity',
                'discount',
                'tax_name1',
                'line_total'
            ],
        ];

        return $variables;
    }
}
