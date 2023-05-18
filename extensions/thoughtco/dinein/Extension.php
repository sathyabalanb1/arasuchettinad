<?php

namespace Thoughtco\Dinein;

use Admin\Controllers\Locations;
use Admin\Controllers\Menus;
use Admin\Controllers\Orders;
use Admin\Controllers\Tables;
use Admin\Models\Locations_model;
use Admin\Models\Menus_model;
use Admin\Models\Orders_model;
use Admin\Models\Tables_model;
use Admin\Widgets\Form;
use Event;
use Igniter\Flame\Location\OrderTypes;
use Igniter\Local\Classes\Location;
use System\Classes\BaseExtension;

class Extension extends BaseExtension
{
    public function boot()
    {
        $this->extendLocations();
        $this->extendOrders();
        $this->extendTables();

        Event::listen('thoughtco.printer.orderData', function($model, &$data) {
            if (isset($model->table_number) AND $model->table_number != '')
                $data['table_number'] = ($table = Tables_model::find($model->table_number)) ? $table->table_name : '';
        });

        Event::listen('location.orderType.updated', function($locationManager, $code, $oldOrderType) {
            if (in_array($code, ['dinein', 'waiter']))
                $locationManager->updateScheduleTimeSlot(null, true);
        });
    }

    public function register()
    {
        $this->registerOrderTypes();
    }

    public function registerComponents()
    {
        return [
            'Thoughtco\Dinein\Components\Tables' => [
                'code' => 'tables',
                'name' => 'thoughtco.dinein::default.label_table_component',
                'description' => 'thoughtco.dinein::default.text_table_component',
            ],
            'Thoughtco\Dinein\Components\TableConfirm' => [
                'code' => 'tableConfirm',
                'name' => 'thoughtco.dinein::default.label_tableconfirm_component',
                'description' => 'thoughtco.dinein::default.text_tableconfirm_component',
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'sales' => [
                'child' => [
                    'waiter' => [
                        'priority' => 10,
                        'class' => 'waiter-service',
                        'href' => admin_url('thoughtco/dinein/waiterservice'),
                        'title' => lang('thoughtco.dinein::default.text_nav_link'),
                        'permission' => 'Thoughtco.Dinein.WaiterService',
                    ],
                ],
            ],
        ];
    }

    public function registerOrderTypes()
    {
        OrderTypes::registerCallback(function ($manager) {
            $manager->registerOrderTypes([
                \Thoughtco\Dinein\OrderTypes\Dinein::class => [
                    'code' => 'dinein',
                    'name' => 'lang:thoughtco.dinein::default.text_dinein',
                ],
                \Thoughtco\Dinein\OrderTypes\Waiter::class => [
                    'code' => 'waiter',
                    'name' => 'lang:thoughtco.dinein::default.text_waiter',
                ],
            ]);
        });
    }

    public function registerPaymentGateways()
    {
        return [
            'Thoughtco\Dinein\Payments\Waiter' => [
                'code' => 'waiter',
                'name' => 'thoughtco.dinein::default.waiter.text_payment_title',
                'description' => 'lang:thoughtco.dinein::default.waiter.text_payment_desc',
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'Thoughtco.Dinein.WaiterService' => [
                'description' => 'lang:thoughtco.dinein::default.text_waiter_permissions',
                'group' => 'module',
            ],
        ];
    }

    private function extendLocations()
    {
        Locations::extendFormFields(function (Form $form, $model, $context) {
            if (!$model instanceof Locations_model)
                return;

            if (!isset($form->tabs['fields']['location_name']))
                return;

            $form->addTabFields([
                'dinein' => [
                    'label' => 'thoughtco.dinein::default.locations.text_tab_dinein',
                    'tab' => 'lang:thoughtco.dinein::default.text_dinein',
                    'type' => 'section',
                ],
                'options[offer_dinein]' => [
                    'label' => 'thoughtco.dinein::default.locations.label_offer_dinein',
                    'tab' => 'lang:thoughtco.dinein::default.text_dinein',
                    'span' => 'left',
                    'default' => 1,
                    'type' => 'switch',
                ],
                'options[dinein_time_restriction]' => [
                    'label' => 'thoughtco.dinein::default.locations.label_dinein_time_restriction',
                    'tab' => 'lang:thoughtco.dinein::default.text_dinein',
                    'type' => 'radiotoggle',
                    'span' => 'right',
                    'comment' => 'thoughtco.dinein::default.locations.help_dinein_time_restriction',
                    'options' => [
                        'lang:admin::lang.text_none',
                        'lang:admin::lang.locations.text_asap_only',
                        'lang:admin::lang.locations.text_later_only',
                    ],
                    'trigger' => [
                        'action' => 'enable',
                        'field' => 'options[offer_dinein]',
                        'condition' => 'checked',
                    ],
                ],
                'options[dinein_time_interval]' => [
                    'label' => 'thoughtco.dinein::default.locations.label_dinein_time_interval',
                    'tab' => 'lang:thoughtco.dinein::default.text_dinein',
                    'default' => 15,
                    'type' => 'number',
                    'span' => 'left',
                    'comment' => 'thoughtco.dinein::default.locations.help_dinein_time_interval',
                    'trigger' => [
                        'action' => 'enable',
                        'field' => 'options[offer_dinein]',
                        'condition' => 'checked',
                    ],
                ],
                'options[dinein_lead_time]' => [
                    'label' => 'thoughtco.dinein::default.locations.label_dinein_lead_time',
                    'tab' => 'lang:thoughtco.dinein::default.text_dinein',
                    'default' => 45,
                    'type' => 'number',
                    'span' => 'right',
                    'comment' => 'thoughtco.dinein::default.locations.help_dinein_lead_time',
                    'trigger' => [
                        'action' => 'enable',
                        'field' => 'options[offer_dinein]',
                        'condition' => 'checked',
                    ],
                ],

                'options[offer_waiter]' => [
                    'label' => 'thoughtco.dinein::default.locations.label_offer_waiter',
                    'tab' => 'lang:thoughtco.dinein::default.text_dinein',
                    'default' => 1,
                    'type' => 'switch',
                    'span' => 'left',
                ],
                'options[waiter_time_restriction]' => [
                    'label' => 'thoughtco.dinein::default.locations.label_waiter_time_restriction',
                    'tab' => 'lang:thoughtco.dinein::default.text_dinein',
                    'type' => 'radiotoggle',
                    'span' => 'right',
                    'comment' => 'thoughtco.dinein::default.locations.help_waiter_time_restriction',
                    'options' => [
                        'lang:admin::lang.text_none',
                        'lang:admin::lang.locations.text_asap_only',
                        'lang:admin::lang.locations.text_later_only',
                    ],
                    'trigger' => [
                        'action' => 'enable',
                        'field' => 'options[offer_waiter]',
                        'condition' => 'checked',
                    ],
                ],

                'options[waiter_staff_only]' => [
                    'label' => 'thoughtco.dinein::default.locations.label_waiter_staff_only',
                    'tab' => 'lang:thoughtco.dinein::default.text_dinein',
                    'default' => 1,
                    'type' => 'switch',
                ],

                'options[waiter_autofill_firstname]' => [
                    'label' => 'thoughtco.dinein::default.locations.label_waiter_autofill_firstname',
                    'tab' => 'lang:thoughtco.dinein::default.text_dinein',
                    'default' => '',
                    'type' => 'text',
                    'span' => 'left',
                    'trigger' => [
                        'action' => 'enable',
                        'field' => 'options[offer_waiter]',
                        'condition' => 'checked',
                    ],
                ],
                'options[waiter_autofill_lastname]' => [
                    'label' => 'thoughtco.dinein::default.locations.label_waiter_autofill_lastname',
                    'tab' => 'lang:thoughtco.dinein::default.text_dinein',
                    'default' => '',
                    'type' => 'text',
                    'span' => 'right',
                    'trigger' => [
                        'action' => 'enable',
                        'field' => 'options[offer_waiter]',
                        'condition' => 'checked',
                    ],
                ],
                'options[waiter_autofill_email]' => [
                    'label' => 'thoughtco.dinein::default.locations.label_waiter_autofill_email',
                    'tab' => 'lang:thoughtco.dinein::default.text_dinein',
                    'default' => '',
                    'type' => 'text',
                    'span' => 'left',
                    'trigger' => [
                        'action' => 'enable',
                        'field' => 'options[offer_waiter]',
                        'condition' => 'checked',
                    ],
                ],
                'options[waiter_autofill_telephone]' => [
                    'label' => 'thoughtco.dinein::default.locations.label_waiter_autofill_telephone',
                    'tab' => 'lang:thoughtco.dinein::default.text_dinein',
                    'default' => '',
                    'type' => 'text',
                    'span' => 'right',
                    'trigger' => [
                        'action' => 'enable',
                        'field' => 'options[offer_waiter]',
                        'condition' => 'checked',
                    ],
                ],

            ]);
        });

        Locations_model::extend(function($model) {
            $model->addDynamicMethod('hasDinein', function() use ($model) {
                return $model->options['offer_dinein'] ?? false;
            });

            $model->addDynamicMethod('dineinSchedule', function() use ($model) {
                return $model->workingSchedule(Locations_model::OPENING);
            });

            $model->addDynamicMethod('hasWaiterService', function() use ($model) {
                return $model->options['offer_waiter'] ?? false;
            });

            $model->addDynamicMethod('waiterServiceSchedule', function() use ($model) {
                return $model->workingSchedule(Locations_model::OPENING);
            });
        });

		Event::listen('system.formRequest.extendValidator', function ($formRequest, $dataHolder) {
		    if ($formRequest instanceof \Admin\Requests\Location) {
                $dataHolder->rules[] = ['options.offer_dinein', 'thoughtco.dinein::default.locations.label_offer_dinein', 'boolean'];
		    	$dataHolder->rules[] = ['options.dinein_lead_time', 'thoughtco.dinein::default.locations.label_dinein_lead_time', 'integer'];
                $dataHolder->rules[] = ['options.dinein_time_interval', 'thoughtco.dinein::default.locations.label_dinein_time_interval', 'integer'];
			}
		});
    }

    private function extendOrders()
    {
        Orders::extendFormFields(function (Form $form, $model, $context) {
            if (!$model instanceof Orders_model)
                return;

            if (!isset($form->tabs['fields']['order_menus']))
                return;

            $form->addTabFields([
				'table_number_fake' => [
		            'tab' => 'lang:thoughtco.dinein::default.text_dinein',
		            'label' => 'lang:thoughtco.dinein::default.label_table_number',
		            'type' => 'text',
					'disabled' => TRUE,
                    'span' => 'left',
                    'default' => $model->getTableNumber(),
		        ],
            ]);
        });

		Orders_model::extend(function ($model) {
			$model->fillable(array_merge($model->getFillable(), ['table_number', 'table_closed_at', 'table_count']));

            $model->addDynamicMethod('isDineinType', function() use ($model) {
                return $model->order_type == 'dinein';
            });

            $model->addDynamicMethod('isWaiterServiceType', function() use ($model) {
                return $model->order_type == 'waiter';
            });

            $model->addDynamicMethod('getTableNumber', function() use ($model) {
                if ($table = Tables_model::find($model->table_number)) {
                    return $table->table_name;
                }
                return '';
            });
		});

    }

    private function extendTables()
    {
        Tables::extendFormFields(function (Form $form, $model, $context) {
            if (!$model instanceof Tables_model)
                return;

            if (!isset($form->fields['table_name']))
                return;

            $form->addFields([
				'_qrcodes' => [
		            'type' => 'partial',
					'disabled' => TRUE,
                    'path' => '$/thoughtco/dinein/views/_partials/qr_code',
                    'label' => 'lang:thoughtco.dinein::default.label_qrcode',
		        ],
            ]);
        });

    }

}

?>
