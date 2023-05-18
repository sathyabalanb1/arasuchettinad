<?php

return [
    'list' => [
        'toolbar' => [
            'buttons' => [
		        'create' => [
		            'label' => 'lang:admin::lang.button_new',
		            'class' => 'btn btn-primary',
		            'href' => 'thoughtco/printer/dockets/create',
		        ],
                'delete' => ['label' => 'lang:admin::lang.button_delete', 'class' => 'btn btn-danger', 'data-request-form' => '#list-form', 'data-request' => 'onDelete', 'data-request-data' => "_method:'DELETE'", 'data-request-data' => "_method:'DELETE'", 'data-request-confirm' => 'lang:admin::lang.alert_warning_confirm'],
		        'printers' => [
		            'label' => 'lang:thoughtco.printer::default.btn_printers',
		            'class' => 'btn btn-default',
		            'href' => 'thoughtco/printer/printers',
		        ],
            
			],
        ],
		'filter' => [],
        'columns' => [
            'edit' => [
                'type' => 'button',
                'iconCssClass' => 'fa fa-pencil',
                'attributes' => [
                    'class' => 'btn btn-edit',
                    'href' => 'thoughtco/printer/dockets/edit/{id}',
                ],
            ],
            'label' => [
                'label' => 'lang:thoughtco.printer::default.column_label',
                'type' => 'text',
                'sortable' => TRUE,
            ],
        ],
    ],

    'form' => [
        'toolbar' => [
            'buttons' => [
                'back' => ['label' => 'lang:admin::lang.button_icon_back', 'class' => 'btn btn-default', 'href' => 'thoughtco/printer/dockets'],
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
				'span' => 'left',
            ],
		],
        'tabs' => [
	        'fields' => [
	            'docket_settings[format]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_format',
	                'type' => 'textarea',
					'attributes' => [
						'rows' => 40,
					],
		            'trigger' => [
		                'action' => 'hide',
		                'field' => 'printer_settings[usedefault]',
		                'condition' => 'checked',
		            ],
	            ],
	            'docket_settings[lines_before]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_options',
	                'label' => 'lang:thoughtco.printer::default.lines_before',
		            'type' => 'number',
					'default' => 0,
	                'span' => 'left',
		            'attributes' => [
			            'step' => 1,
			            'min' => 0
		            ],
		            'trigger' => [
		                'action' => 'hide',
		                'field' => 'printer_settings[usedefault]',
		                'condition' => 'checked',
		            ],
	            ],
	            'docket_settings[lines_after]' => [
	                'tab' => 'lang:thoughtco.printer::default.tab_options',
	                'label' => 'lang:thoughtco.printer::default.lines_after',
		            'type' => 'number',
					'default' => 0,
	                'span' => 'right',
		            'attributes' => [
			            'step' => 1,
			            'min' => 0
		            ],
		            'trigger' => [
		                'action' => 'hide',
		                'field' => 'printer_settings[usedefault]',
		                'condition' => 'checked',
		            ],
	            ],
	        ]
        ]
    ],
];
