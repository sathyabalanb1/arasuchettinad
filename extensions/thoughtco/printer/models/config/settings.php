<?php

/**
 * Model configuration options for settings model.
 */

$config = [
    'form' => [
        'toolbar' => [
            'buttons' => [
                'save' => ['label' => 'lang:admin::lang.button_save', 'class' => 'btn btn-primary', 'data-request' => 'onSave'],
                'saveClose' => [
                    'label' => 'lang:admin::lang.button_save_close',
                    'class' => 'btn btn-default',
                    'data-request' => 'onSave',
                    'data-request-data' => 'close:1',
                ],
            ],
        ],
        'tabs' => [],
        'fields' => []
    ],
];


$config['form']['tabs'] = [
    'fields' => [
        'output_format' => [
            'tab' => 'lang:thoughtco.printer::default.output_format',
            'type' => 'markdowneditor',
        ],
        'lines_before' => [
            'tab' => 'lang:thoughtco.printer::default.output_format',
            'label' => 'lang:thoughtco.printer::default.lines_before',
            'type' => 'number',
            'attributes' => [
	            'step' => 1,
	            'min' => 0
            ]
        ],
        'lines_after' => [
            'tab' => 'lang:thoughtco.printer::default.output_format',
            'label' => 'lang:thoughtco.printer::default.lines_after',
            'type' => 'number',
            'attributes' => [
	            'step' => 1,
	            'min' => 0
            ]
        ],
        'encoding' => [
            'tab' => 'lang:thoughtco.printer::default.output_format',
            'label' => 'lang:thoughtco.printer::default.label_encoding',
            'type' => 'select',
            'options' => \Thoughtco\Printer\Models\Printer::getEncodingOptions(),
            'default' => 'windows-1252',
        ],
        'variables' => [
            'tab' => 'lang:system::lang.mail_templates.text_variables',
            'type' => 'partial',
            'path' => 'extensions/thoughtco/printer/views/partials/variables',
            'disabled' => TRUE,
        ],
    ],
];

return $config;
