<?php

return [

    'btn_close_table' => 'Close table',
    'btn_print' => 'Print order',
    'btn_view_table' => 'View table',
    'btn_make_payment_table' => 'Make Payment',
    'button_print' => '<i class="fa fa-print"></i>&nbsp;&nbsp;Print',

    'choose_table' => [
        'btn_yes' => 'Yes, is my table',
        'btn_no' => 'No, I\'m at another table',
        'btn_select' => 'Select table',
        'btn_continue' => 'Continue to ordering',

        'text_select_table' => 'Please select your table',
        'text_title' => 'Are you at table: :table?',
        'text_allergies' => 'Allergies',
        'text_allergies_more' => 'Please let a member of staff know about any allergies or special requirements you have before you place an order.',
    ],

    'column_reservation' => 'Next reservation time',
    'column_stay' => 'First order time',
    'column_table' => 'Table number',

    'label_table_count' => 'Table occupants',
	'label_table_number' => 'Table number',
    'label_dinein' => 'Dine-in only',
    'label_qrcode' => 'QR Codes',
    'label_choose_payment' => 'Select the payment method',


    'label_table_component' => 'Tables',
    'label_table_component_hide' => 'Hide field from view',
    'label_table_component_hide_comment' => 'If hidden, you must ensure table is set by session variable / URL',

    'label_tableconfirm_component' => 'Table Confirmation',

    'label_waiter' => 'Waiter only',

    'locations' => [
        'text_tab_dinein' => 'Dine-in options',

        'label_offer_dinein' => 'Offer Dine-in?',
        'label_offer_waiter' => 'Offer Waiter Service?',
        'label_dinein_time_interval' => 'Dine-in Time Interval',
        'label_dinein_lead_time' => 'Dine-in Lead Time',
        'label_dinein_time_restriction' => 'Dine-in Time Restriction',
        'label_waiter_time_restriction' => 'Waiter Time Restriction',

        'label_waiter_staff_only' => 'Only available to staff users',
        'label_waiter_autofill_firstname' => 'Autofilled checkout first name value',
        'label_waiter_autofill_lastname' => 'Autofilled checkout last name value',
        'label_waiter_autofill_email' => 'Autofilled checkout email value',
        'label_waiter_autofill_telephone' => 'Autofilled checkout telephone value',

        'help_dinein_time_interval' => 'Set the minutes between each dine-in order time available to your customer.',
        'help_dinein_lead_time' => 'Set in minutes the average time it takes an order to be ready for dine-in after being placed',
        'help_dinein_time_restriction' => 'Whether your customers can only place ASAP dine-in orders, schedule dine-in orders for later or both.',
        'help_waiter_time_restriction' => 'Whether your staffs can only place ASAP waiter orders, schedule waiter orders for later or both.',
    ],

    'page_choose_title' => 'Dine-in table selection',

    'text_dinein' => 'Dine-in',
    'text_dinein_time_info' => 'Dine-in %s',
    'text_dinein_time' => 'Dine-in Time',
    'text_dinein_only' => 'Dine-in only',
    'text_dinein_is_unavailable' => 'Dine-in is not available.',

    'text_empty' => 'No tables found',
    'text_list_title' => 'Waiter Service',

    'text_nav_link' => 'Waiter Service',

    'text_qr_download' => 'Download',

    'text_table_component' => 'Display a tables selector to the checkout',
    'text_tableconfirm_component' => 'Confirm that the selected table is correct when using QR codes',

    'text_waiter' => 'Waiter Service',
    'text_waiter_time_info' => 'Waiter Service %s',
    'text_waiter_time' => 'Waiter Service Time',
    'text_waiter_only' => 'Waiter Service only',
    'text_waiter_is_unavailable' => 'Waiter Service is not available.',
    'text_waiter_permissions' => 'Manage Waiter Service orders',

    'waiter' => [
        'text_payment_title' => 'Waiter Service',
        'text_payment_desc' => 'Order taken by waiter/staff',
    ],
];
