<?php
$config['form']['fields'] = [
    'printer_docket_id' => [
        'type' => 'hidden',
    ],
    'printer_id' => [
        'type' => 'hidden',
    ],
    'priority' => [
        'type' => 'hidden',
    ],
    'docket_id' => [
        'type' => 'hidden',
    ],
    'settings[copies]' => [
        'tab' => 'lang:thoughtco.printer::default.tab_settings',
        'label' => 'lang:thoughtco.printer::default.label_copies',
        'type' => 'text',
		'default' => 1,
        'attributes' => [
            'maxlength' => 4,
        ],
        'span' => 'left',
    ],
    'settings[categories]' => [
        'label' => 'lang:thoughtco.printer::default.label_categories',
        'type' => 'selectlist',
        'options' => \Thoughtco\Printer\Models\Printer_dockets::getCategoryOptions(),
		'comment' => 'lang:thoughtco.printer::default.comment_categories',
        'default' => [],
    ],
    'settings[contexts]' => [
        'label' => 'lang:thoughtco.printer::default.label_contexts',
        'type' => 'select',
        'default' => '0',
        'options' => \Thoughtco\Printer\Models\Printer_dockets::getContextOptions(),
    ],
];

return $config;
