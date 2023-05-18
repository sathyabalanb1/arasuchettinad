## Dine-in / Waiter service

Enables the addition of two new order types:
- order from tables (dine-in)
- order by staff (waiter-service)

## Core version
:warning: This extension requires the **stable** version of TastyIgniter 3.0.4 to work. Do not install on earlier beta versions. :warning:

## Dine-in
After installation you can enable Dine-in as an order type on any location using the "Enable Dine-in" field under the location settings. This will add a new order type button on the location page.

The extension assumes your Dine-in hours are the same as your opening hours.

## Waiter Service
You can "Enable Waiter Service" under the location settings. This will add a new order type button on the location page, optionally only when logged into the admin panel. For speed of ordering you can also set default values for the customer fields on the checkout page.

The extension assumes your Waiter Service hours are the same as your opening hours.

## Components

To enable the table selector field on checkout copy extensions/cart/components/checkout/form.blade.php to your child theme as _partials/checkout/form.blade.php and add:


```blade
@component('tables')
```

Also, remember to add the component to the page `checkout/checkout`

To add a component: Design > Themes > (click the sheet under active child themes) > Select Pages under Template (left dropdown), and the page `checkout/checkout`. Now click on the big plus and select from the dropdown the Tables Component.

## Select table URL
If you want to enable table selection by QR code, copy `views/_pages/dinein/choose_table.blade.php` to `views/_pages` in your child theme folder.

Once installed a new location url is available in the format `{location}/table/{id}`, e.g. `/default/table/22`. You should direct customers to this through the use of a QR code or visible URL on the table.

This information will be stored the session under the key `thoughtco.dinein`, which you can access within your templates as follows:

```php
$tableData = Session::get('thoughtco.dinein');
// ['location' => xxx, 'id' => xxx]
```

You can optionally pass an URL parameter `ordertype` to set the order type (`dinein` or `waiter`). Defaults to `dinein`.

## Printer
This extension adds a new variable to printer dockets `$table_number`, which can be used if an order has been processed by Dine-in or Waiter-Service. It should be used wrapped in an @isset as follows.

```blade
@isset($table_number)
TABLE: {{ $table_number }}
@endisset
```

## Locations_model functions
This extension adds some new functions to `Locations_model`, which may be useful for theming.

`hasDinein`
whether or not dine-in is available

`dineinSchedule`
the WorkingSchedule for dine-in (should be the same as opening hours)

`hasWaiterService`
whether or not waiter service is available (requires the user to have an active admin panel session.

`waiterServiceSchedule`
the WorkingSchedule for waiter-service (should be the same as opening hours)

## Orders_model functions
This extension adds some new functions to `Orders_model`, which may be useful for theming.

`isDineinType`
whether or not the order is a dine-in order

`isWaiterServiceType`
whether or not the order is a waiter-service order

`getTableNumber`
the table number/label associated with the order



