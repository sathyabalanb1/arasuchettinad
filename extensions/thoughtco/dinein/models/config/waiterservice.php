<?php

use Illuminate\Support\Carbon;

$list_buttons = [];

if ($location = AdminLocation::current()) {
    $list_buttons['create'] = [
        'label' => 'lang:admin::lang.button_new',
        'class' => 'btn btn-primary',
        'href' => '../'.$location->permalink_slug.'/menus/',
    ];
}

return [
    'list' => [
        'toolbar' => [
            'buttons' => $list_buttons,
        ],
		'filter' => [],
        'columns' => [
            'table_name' => [
                'label' => 'lang:thoughtco.dinein::default.column_table',
                'type' => 'text',
                'sortable' => TRUE,
            ],
            'stay_time' => [
                'type' => 'text',
                'label' => 'lang:thoughtco.dinein::default.column_stay',
                'sortable' => FALSE,
                'formatter' => function($record, $column, $value) {

                    $firstOrder = \Admin\Models\Orders_model::where([
                        'location_id' => AdminLocation::getId(),
                        'table_number' => $record->table_id,
                        'order_type' => 'waiter',
                    ])
                        ->select(['created_at'])
                        ->whereNotIn('status_id', setting('completed_order_status'))
                        ->orderBy('created_at', 'ASC')
                        ->first();

                    if (!$firstOrder)
                        return '';

                    return Carbon::now()->diff(Carbon::parse($firstOrder->created_at))->format('%H:%I');
                }
            ],
            'next_reservation' => [
                'type' => 'text',
                'label' => 'lang:thoughtco.dinein::default.column_reservation',
                'sortable' => FALSE,
                'formatter' => function($record, $column, $value) {

                    $firstReservation = \Admin\Models\Reservations_model::where([
                        'location_id' => AdminLocation::getId(),
                    ])
                        ->whereBetweenReservationDateTime(Carbon::now()->format('Y-m-d H:i'), Carbon::now()->addDay(1)->format('Y-m-d H:i'))
                        ->orderBy('reserve_date', 'ASC')
                        ->orderBy('reserve_time', 'ASC')
                        ->get()
                        ->first(function ($reservation) use ($record) {
                            return $reservation->tables->pluck('table_id')->contains($record->table_id);
                        });

                    if (!$firstReservation)
                        return '';

                    return Carbon::now()->diffAsCarbonInterval(Carbon::parse($firstOrder->date_added))->format('h:i d/m');
                }
            ],
            'table_id' => [
                'type' => 'text',
                'label' => '',
                'sortable' => FALSE,
                'formatter' => function($record, $column, $value) {

                    if (!AdminLocation::getId() AND !AdminLocation::hasOneLocation())
                        return '';

                    $location_id = AdminLocation::getId() ?? AdminLocation::getDefaultLocation();

                    $order_count = \Admin\Models\Orders_model::where([
                        'location_id' => $location_id,
                        'table_number' => $value,
                        'order_type' => 'waiter',
                    ])
                        ->whereNotIn('status_id', setting('completed_order_status'))
                        ->count();

                    if (!$order_count)
                        return '';

                    return '<a class="btn btn-primary" href="'.admin_url('thoughtco/dinein/waiterservice/close/'.$value).'">'.lang('thoughtco.dinein::default.btn_view_table').'</a>';
                }
            ],
        ],
    ],

    'form' => [
    ],
];
