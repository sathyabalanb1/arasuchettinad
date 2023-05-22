<?php

return [
    'form' => [
        'toolbar' => [
            'buttons' => [
                'save' => ['label' => 'lang:admin::lang.button_save', 'class' => 'btn btn-primary', 'data-request' => 'onSave']
            ],
        ],
        'fields' => [
            'tax_cgst' => [
                'label' => 'lang:diligentsquad.gst::default.label_tax_cgst',
                'tab' => 'lang:system::lang.settings.text_tab_title_taxation',
                'type' => 'switch',
                'default' => FALSE,
                'comment' => 'lang:system::lang.settings.help_tax_mode',
            ],
            'tax_cgst_percentage' => [
                'label' => 'lang:diligentsquad.gst::default.label_tax_cgst_percentage',
                'tab' => 'lang:system::lang.settings.text_tab_title_taxation',
                'type' => 'number',
                'default' => 0,
                'comment' => 'lang:system::lang.settings.help_tax_percentage',
            ],
            'tax_sgst' => [
                'label' => 'lang:diligentsquad.gst::default.label_tax_sgst',
                'tab' => 'lang:system::lang.settings.text_tab_title_taxation',
                'type' => 'switch',
                'default' => FALSE,
                'comment' => 'lang:system::lang.settings.help_tax_mode',
            ],
            'tax_sgst_percentage' => [
                'label' => 'lang:diligentsquad.gst::default.label_tax_sgst_percentage',
                'tab' => 'lang:system::lang.settings.text_tab_title_taxation',
                'type' => 'number',
                'default' => 0,
                'comment' => 'lang:system::lang.settings.help_tax_percentage',
            ],
            'tax_menu_price' => [
                'label' => 'lang:system::lang.settings.label_tax_menu_price',
                'type' => 'select',
                'options' => [
                    'lang:system::lang.settings.text_menu_price_include_tax',
                    'lang:system::lang.settings.text_apply_tax_on_menu_price',
                ],
                'comment' => 'lang:system::lang.settings.help_tax_menu_price',
            ],
        ],
    ],
];
