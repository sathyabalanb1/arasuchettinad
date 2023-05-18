<?php namespace Thoughtco\Printer\Models;

use Model;
use Thoughtco\Printer\Classes\Printerfunctions;

class Settings extends Model
{
    public $implement = ['System\Actions\SettingsModel'];

    // A unique code
    public $settingsCode = 'thoughtco_printer_settings';

    // Reference to field configuration
    public $settingsFieldsConfig = 'settings';

    // on save
    public function save(?array $options = NULL, $sessionKey = NULL)
    {
	    Printerfunctions::clearTemplates();
	    return parent::save($options, $sessionKey);
    }

	// get variables
    public function getVariablesAttribute($value)
    {
        return [
            'General' => [
	            ['var' => '{{ $site_name }}', 'name' => 'Site name'],
                ['var' => '{{ $site_url }}', 'name' => 'Site URL'],

                ['var' => '{{ $location_name }}', 'name' => 'Location name'],
                ['var' => '{{ $location_telephone }}', 'name' => 'Location telephone'],
                ['var' => '{{ $location_email }}', 'name' => 'Location email'],
                ['var' => '{{ $location_address }}', 'name' => 'Location address'],
            ],
            'Customer' => [
                ['var' => '{{ $first_name }}', 'name' => 'Customer first name'],
                ['var' => '{{ $last_name }}', 'name' => 'Customer last name'],
                ['var' => '{{ $email }}', 'name' => 'Customer email address'],
                ['var' => '{{ $telephone }}', 'name' => 'Customer telephone address'],
            ],
            'Order' => [
                ['var' => '{{ $customer_name }}', 'name' => 'Customer full name'],
                ['var' => '{{ $order_number }}', 'name' => 'Order number'],
                ['var' => '{{ $order_view_url }}', 'name' => 'Order view URL'],
                ['var' => '{{ $order_type }}', 'name' => 'Order type ex. delivery/pick-up'],
                ['var' => '{{ $order_time }}', 'name' => 'Order delivery/pick-up time'],
                ['var' => '{{ $order_date }}', 'name' => 'Order delivery/pick-up date'],
                ['var' => '{{ $order_address }}', 'name' => 'Customer address for delivery order'],
                ['var' => '{{ $order_payment }}', 'name' => 'Order payment method'],
                ['var' => '{{ $order_payment_code }}', 'name' => 'Order payment method code'],
                ['var' => '{{ $order_menus }}', 'name' => 'Order menus (array)'],
                ['var' => '{{ $order_totals }}', 'name' => 'Order total pairs (array)'],
                ['var' => '{{ $order_comment }}', 'name' => 'Order comment'],
            ],
            'Order menus' => [
                ['var' => '{{ $menu_name }}', 'name' => 'Order menu name'],
                ['var' => '{{ $menu_category_name }}', 'name' => 'Order menu category name'],
                ['var' => '{{ $menu_quantity }}', 'name' => 'Order menu quantity'],
                ['var' => '{{ $menu_price }}', 'name' => 'Order menu price'],
                ['var' => '{{ $menu_subtotal }}', 'name' => 'Order menu subtotal'],
                ['var' => '{{ $menu_options }}', 'name' => 'Order menu items (array)'],
                ['var' => '{{ $menu_comment }}', 'name' => 'Order menu comment'],
			],
            'Order menu options' => [
                ['var' => '{{ $menu_option_name }}', 'name' => 'Order menu option name'],
                ['var' => '{{ $menu_option_quantity }}', 'name' => 'Order menu option quantity'],
                ['var' => '{{ $menu_option_linequantity }}', 'name' => 'Order menu option line quantity'],
                ['var' => '{{ $menu_option_price }}', 'name' => 'Order menu option price'],
                ['var' => '{{ $menu_option_subtotal }}', 'name' => 'Order menu option subtotal'],
                ['var' => '{{ $menu_option_linetotal }}', 'name' => 'Order menu option linetotal'],
            ],
            'Order totals' => [
                ['var' => '{{ $order_total_title }}', 'name' => 'Order total title'],
                ['var' => '{{ $order_total_code }}', 'name' => 'Order total code'],
                ['var' => '{{ $order_total_value }}', 'name' => 'Order total value'],
            ],
            'Status' => [
                ['var' => '{{ $status_name }}', 'name' => 'Status name'],
                ['var' => '{{ $status_comment }}', 'name' => 'Status comment'],
            ],
        ];
    }

}
