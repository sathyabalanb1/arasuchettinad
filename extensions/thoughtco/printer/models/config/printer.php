<?php

return [
    'list' => [
        'toolbar' => [
            'buttons' => [
		        'create' => [
		            'label' => 'lang:admin::lang.button_new',
		            'class' => 'btn btn-primary',
		            'href' => 'thoughtco/printer/printers/create',
		        ],
                'delete' => ['label' => 'lang:admin::lang.button_delete', 'class' => 'btn btn-danger', 'data-request-form' => '#list-form', 'data-request' => 'onDelete', 'data-request-data' => "_method:'DELETE'", 'data-request-data' => "_method:'DELETE'", 'data-request-confirm' => 'lang:admin::lang.alert_warning_confirm'],
		        'dockets' => [
		            'label' => 'lang:thoughtco.printer::default.btn_dockets',
		            'class' => 'btn btn-default',
		            'href' => 'thoughtco/printer/dockets',
		        ],

			],
        ],
		'filter' => [
			'scopes' => [
				'is_enabled' => [
					'label' => 'lang:admin::lang.text_filter_status',
					'type' => 'switch',
					'conditions' => 'is_enabled = :filtered',
				],
			],
		],
        'columns' => [
            'edit' => [
                'type' => 'button',
                'iconCssClass' => 'fa fa-pencil',
                'attributes' => [
                    'class' => 'btn btn-edit',
                    'href' => 'thoughtco/printer/printers/edit/{id}',
                ],
            ],
            'label' => [
                'label' => 'lang:thoughtco.printer::default.column_label',
                'type' => 'text',
                'sortable' => TRUE,
            ],
			'is_enabled' => [
				'label' => 'lang:thoughtco.printer::default.column_status',
				'type' => 'switch',
				'sortable' => FALSE,
			],
            'id' => [
                'type' => 'text',
                'label' => '',
				'sortable' => FALSE,
                'formatter' => function($something, $column, $value){
					return '<a class="btn btn-primary" href="'.admin_url('thoughtco/printer/autoprint?location='.$value).'">'.lang('thoughtco.printer::default.btn_autoprint').'</a>';
                }
            ],
        ],
    ],

    'form' => [
        'toolbar' => [
            'buttons' => [
                'back' => ['label' => 'lang:admin::lang.button_icon_back', 'class' => 'btn btn-default', 'href' => 'thoughtco/printer/printers'],
                'save' => [
                    'label' => 'lang:admin::lang.button_save',
                    'class' => 'btn btn-primary',
                    'data-request' => 'onSave',
                ],
                'saveClose' => [
                    'label' => 'lang:admin::lang.button_save_close',
                    'class' => 'btn btn-default',
                    'data-request' => 'onSave',
                    'data-request-data' => 'close:1',
                ],
            ],
        ],
		'fields' => [
            'label' => [
                'tab' => 'lang:thoughtco.printer::default.tab_connection',
                'label' => 'lang:thoughtco.printer::default.label_label',
                'type' => 'text',
            ],
            'location_id' => [
                'tab' => 'lang:thoughtco.printer::default.tab_connection',
                'label' => 'lang:thoughtco.printer::default.label_location',
                'type' => 'select',
				'span' => 'left',
            ],
			'is_enabled' => [
				'tab' => 'lang:thoughtco.printer::default.tab_connection',
				'label' => 'lang:thoughtco.printer::default.label_status',
				'type' => 'switch',
				'span' => 'right',
			],
		],
        'tabs' => [
	        'fields' => [
	            'printer_settings[type]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => 'Type',
	                'type' => 'select',
	                'options' => [
		                'usb' => 'USB',
						//'bluetooth' => 'Bluetooth',
		                'ip' => 'IP/Network',
		                'ethernet' => 'Epson ePOS Webservice'
	                ]
	            ],
	            'printer_settings[usb_setup]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => '',
	                'type' => 'partial',
					'path' => 'extensions/thoughtco/printer/views/partials/usbsetup',
					'trigger' => [
		                'action' => 'show',
		                'field' => 'printer_settings[type]',
		                'condition' => 'value[usb]',
		            ],
				],
	            'printer_settings[bt_setup]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => '',
	                'type' => 'partial',
					'path' => 'extensions/thoughtco/printer/views/partials/btsetup',
					'trigger' => [
		                'action' => 'show',
		                'field' => 'printer_settings[type]',
		                'condition' => 'value[bluetooth]',
		            ],
				],
	            'printer_settings[ip_setup]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => 'lang:thoughtco.printer::default.label_ip_setup',
	                'type' => 'partial',
					'path' => 'extensions/thoughtco/printer/views/partials/ipsetup',
					'trigger' => [
		                'action' => 'show',
		                'field' => 'printer_settings[type]',
		                'condition' => 'value[ip]',
		            ],
	                'span' => 'left',
				],
	            'printer_settings[ssl]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => 'lang:thoughtco.printer::default.label_ssl',
	                'type' => 'switch',
	                'value' => 1,
					'trigger' => [
		                'action' => 'show',
		                'field' => 'printer_settings[type]',
		                'condition' => 'value[ip]',
		            ],
	                'span' => 'right',
	            ],
	            'printer_settings[ip_address]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => 'lang:thoughtco.printer::default.label_ip',
	                'type' => 'text',
	                'attributes' => [
		                'pattern' => '((^|\.)((25[0-5])|(2[0-4]\d)|(1\d\d)|([1-9]?\d))){4}$'
	                ],
		            'trigger' => [
		                'action' => 'hide',
		                'field' => 'printer_settings[type]',
		                'condition' => 'value[usb]|value[bluetooth]',
		            ],
	                'span' => 'left',
	            ],
	            'printer_settings[port]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => 'lang:thoughtco.printer::default.label_port',
	                'type' => 'text',
	                'attributes' => [
		                'maxlength' => 4,
		            ],
		            'trigger' => [
		                'action' => 'hide',
		                'field' => 'printer_settings[type]',
		                'condition' => 'value[usb]|value[bluetooth]',
		            ],
	                'span' => 'right',
	            ],
	            'printer_settings[device_name]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_connection',
	                'label' => 'lang:thoughtco.printer::default.device_name',
	                'type' => 'text',
		            'trigger' => [
		                'action' => 'show',
		                'field' => 'printer_settings[type]',
		                'condition' => 'value[ethernet]',
		            ],
	                'span' => 'left',
	            ],
	            'printer_settings[copies]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_settings',
	                'label' => 'lang:thoughtco.printer::default.label_copies',
	                'type' => 'text',
					'default' => 1,
	                'attributes' => [
		                'maxlength' => 4,
		            ],
	                'span' => 'left',
	            ],
	            'printer_settings[autocut]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_settings',
	                'label' => 'lang:thoughtco.printer::default.label_autocut',
	                'type' => 'select',
					'options' => \Thoughtco\Printer\Models\Printer::getCutOptions(),
	                'default' => 2,
	                'span' => 'right',
	            ],
				'printer_settings[characters_per_line]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_settings',
					'label' => 'lang:thoughtco.printer::default.label_characters_per_line',
					'type' => 'text',
					'default' => 48,
					'attributes' => [
						'maxlength' => 4,
					],
					'span' => 'left',
				],
				'printer_settings[encoding]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_settings',
					'label' => 'lang:thoughtco.printer::default.label_encoding',
					'type' => 'select',
					'options' => \Thoughtco\Printer\Models\Printer::getEncodingOptions(),
                    'default' => 'windows-1252',
					'span' => 'right',
		            'trigger' => [
		                'action' => 'hide',
		                'field' => 'printer_settings[type]',
		                'condition' => 'value[ethernet]',
		            ],
				],
				'printer_settings[codepage]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_settings',
					'label' => 'lang:thoughtco.printer::default.label_codepage',
					'type' => 'text',
					'default' => 16,
					'attributes' => [
						'maxlength' => 4,
					],
					'span' => 'left',
					'comment' => 'lang:thoughtco.printer::default.comment_codepage',
		            'trigger' => [
		                'action' => 'hide',
		                'field' => 'printer_settings[type]',
		                'condition' => 'value[ethernet]',
		            ],
				],
				'printer_settings[epson_language]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_settings',
					'label' => 'lang:thoughtco.printer::default.label_language',
					'type' => 'select',
					'options' => \Thoughtco\Printer\Models\Printer::getLanguageOptions(),
                    'default' => 'en',
					'span' => 'right',
		            'trigger' => [
		                'action' => 'show',
		                'field' => 'printer_settings[type]',
		                'condition' => 'value[ethernet]',
		            ],
				],

				'_default' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_default',
					'type' => 'section',
				],
				'printer_settings[font][default_vertical]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_vertical',
					'type' => 'number',
					'default' => 1,
					'attributes' => [
						'min' => 1,
						'max' => 7,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][default_horizontal]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_horizontal',
					'type' => 'number',
					'default' => 1,
					'attributes' => [
						'min' => 1,
						'max' => 7,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][default_line]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_line',
					'type' => 'number',
					'default' => 30,
					'attributes' => [
						'min' => 1,
						'max' => 255,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][default_bold]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_bold',
					'type' => 'switch',
					'default' => 0,
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'_heading1' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_heading1',
					'type' => 'section',
				],
				'printer_settings[font][heading1_vertical]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_vertical',
					'type' => 'number',
					'default' => 4,
					'attributes' => [
						'min' => 1,
						'max' => 7,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading1_horizontal]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_horizontal',
					'type' => 'number',
					'default' => 1,
					'attributes' => [
						'min' => 1,
						'max' => 7,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading1_line]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_line',
					'type' => 'number',
					'default' => 48,
					'attributes' => [
						'min' => 1,
						'max' => 255,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading1_bold]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_bold',
					'type' => 'switch',
					'default' => 1,
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'_heading2' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_heading2',
					'type' => 'section',
				],
				'printer_settings[font][heading2_vertical]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_vertical',
					'type' => 'number',
					'default' => 3,
					'attributes' => [
						'min' => 1,
						'max' => 7,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading2_horizontal]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_horizontal',
					'type' => 'number',
					'default' => 1,
					'attributes' => [
						'min' => 1,
						'max' => 7,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading2_line]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_line',
					'type' => 'number',
					'default' => 42,
					'attributes' => [
						'min' => 1,
						'max' => 255,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading2_bold]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_bold',
					'type' => 'switch',
					'default' => 1,
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'_heading3' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_heading3',
					'type' => 'section',
				],
				'printer_settings[font][heading3_vertical]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_vertical',
					'type' => 'number',
					'default' => 2,
					'attributes' => [
						'min' => 1,
						'max' => 7,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading3_horizontal]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_horizontal',
					'type' => 'number',
					'default' => 1,
					'attributes' => [
						'min' => 1,
						'max' => 7,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading3_line]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_line',
					'type' => 'number',
					'default' => 36,
					'attributes' => [
						'min' => 1,
						'max' => 255,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading3_bold]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_bold',
					'type' => 'switch',
					'default' => 1,
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'_heading4' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_heading4',
					'type' => 'section',
				],
				'printer_settings[font][heading4_vertical]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_vertical',
					'type' => 'number',
					'default' => 1,
					'attributes' => [
						'min' => 1,
						'max' => 7,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading4_horizontal]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_horizontal',
					'type' => 'number',
					'default' => 1,
					'attributes' => [
						'min' => 1,
						'max' => 7,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading4_line]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_line',
					'type' => 'number',
					'default' => 30,
					'attributes' => [
						'min' => 1,
						'max' => 255,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading4_bold]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_bold',
					'type' => 'switch',
					'default' => 1,
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'_heading5' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_heading5',
					'type' => 'section',
				],
				'printer_settings[font][heading5_vertical]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_vertical',
					'type' => 'number',
					'default' => 1,
					'attributes' => [
						'min' => 1,
						'max' => 7,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading5_horizontal]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_horizontal',
					'type' => 'number',
					'default' => 1,
					'attributes' => [
						'min' => 1,
						'max' => 7,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading5_line]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_line',
					'type' => 'number',
					'default' => 30,
					'attributes' => [
						'min' => 1,
						'max' => 255,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading5_bold]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_bold',
					'type' => 'switch',
					'default' => 1,
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'_heading6' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_heading6',
					'type' => 'section',
				],
				'printer_settings[font][heading6_vertical]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_vertical',
					'type' => 'number',
					'default' => 1,
					'attributes' => [
						'min' => 1,
						'max' => 7,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading6_horizontal]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_horizontal',
					'type' => 'number',
					'default' => 1,
					'attributes' => [
						'min' => 1,
						'max' => 7,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading6_line]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_line',
					'type' => 'number',
					'default' => 30,
					'attributes' => [
						'min' => 1,
						'max' => 255,
					],
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],
				'printer_settings[font][heading6_bold]' => [
					'tab' => 'lang:thoughtco.printer::default.tab_font',
					'label' => 'lang:thoughtco.printer::default.label_font_bold',
					'type' => 'switch',
					'default' => 1,
					'span' => 'left',
           			'cssClass' => 'flex-width',
				],

	            'printer_settings[manual_setstatus]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_manual',
	                'label' => 'lang:thoughtco.printer::default.label_setstatus_manual',
		            'type' => 'select',
		            'options' => \Thoughtco\Printer\Models\Printer::getStatusOptions(true),
	                'span' => 'left',
	            ],

	            'printer_settings[getstatus]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_autoprint',
	                'label' => 'lang:thoughtco.printer::default.label_getstatus',
		            'type' => 'selectlist',
		            'options' => \Thoughtco\Printer\Models\Printer::getStatusOptions(false),
	                'span' => 'left',
	            ],
	            'printer_settings[setstatus]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_autoprint',
	                'label' => 'lang:thoughtco.printer::default.label_setstatus',
		            'type' => 'select',
		            'options' => \Thoughtco\Printer\Models\Printer::getStatusOptions(true),
	                'span' => 'right',
	            ],
	            'printer_settings[autoprint_sameday]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_autoprint',
	                'label' => 'lang:thoughtco.printer::default.label_autoprint_sameday',
	                'type' => 'switch',
	                'default' => 1,
	                'span' => 'left',
	            ],
	            'printer_settings[autoprint_everywhere]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_autoprint',
	                'label' => 'lang:thoughtco.printer::default.label_autoprint_everywhere',
	                'type' => 'switch',
	                'default' => 0,
	                'span' => 'right',
	            ],
	            'printer_settings[autoprint_interval]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_autoprint',
	                'label' => 'lang:thoughtco.printer::default.label_autoprint_interval',
	                'type' => 'number',
	                'default' => 30,
					'span' => 'left',
				],
	            'printer_settings[autoprint_quantity]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_autoprint',
	                'label' => 'lang:thoughtco.printer::default.label_autoprint_quantity',
	                'type' => 'number',
	                'default' => 10,
					'span' => 'right',
				],
	            'printer_settings[autoprint_limit_users]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_autoprint',
	                'label' => 'lang:thoughtco.printer::default.label_autoprint_limit_users',
	                'type' => 'switch',
	                'default' => 0,
					'span' => 'left',
				],
	            'printer_settings[autoprint_users]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_autoprint',
	                'label' => 'lang:thoughtco.printer::default.label_autoprint_users',
	                'type' => 'selectlist',
	                'default' => '',
		            'options' => \Admin\Models\Staffs_model::getDropdownOptions(),
	                'span' => 'right',
		            'trigger' => [
		                'action' => 'show',
		                'field' => 'printer_settings[autoprint_limit_users]',
		                'condition' => 'checked',
		            ],
				],

		        '_dockets_choose' => [
	                'label' => '',
	                'tab' => 'lang:thoughtco.printer::default.tab_docket',
		            'type' => 'recordeditor',
		            'context' => ['edit', 'preview'],
		            'form' => 'docket',
		            'modelClass' => 'Thoughtco\Printer\Models\Docket',
		            'placeholder' => 'lang:thoughtco.printer::default.help_add_docket',
		            'formName' => 'lang:admin::lang.menu_options.text_option',
					'hideCreateButton' => true,
					'hideEditButton' => true,
					'hideDeleteButton' => true,
		            'addonRight' => [
		                'label' => '<i class="fa fa-long-arrow-down"></i> Add',
		                'tag' => 'button',
		                'attributes' => [
		                    'class' => 'btn btn-default',
		                    'data-control' => 'choose-record',
		                    'data-request' => 'onChooseDocket',
		                ],
		            ],
		        ],
	            'dockets' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_docket',
		            'type' => 'connector',
		            'partial' => 'form/dockets',
		            'nameFrom' => 'label',
		            'formName' => 'lang:thoughtco.printer::default.text_docket_printer_form_name',
		            'form' => 'printer_dockets',
		            'popupSize' => 'modal-lg',
		            'sortable' => TRUE,
		            'context' => ['edit', 'preview'],
	            ],
	        ]
        ]
    ],
];
