<?php

namespace Diligentsquad\Touchfreewaitercustomization\Controllers;

use Admin\Facades\AdminMenu;

/**
 * Customization Admin Controller
 */
class Customization extends \Admin\Controllers\Orders
{
    public $implement = [
        \Admin\Actions\FormController::class,
        \Admin\Actions\ListController::class
    ];

    public $listConfig = [
        'list' => [
            'model'        => 'Diligentsquad\Customizationext\Models\Customization',
            'title'        => 'Customization',
            'emptyMessage' => 'lang:admin::lang.list.text_empty',
            'defaultSort'  => ['id', 'DESC'],
            'configFile'   => 'customization',
        ],
    ];

    public $formConfig = [
        'name'       => 'Customization',
        'model'      => 'Diligentsquad\Customizationext\Models\Customization',
        'create'     => [
            'title'         => 'lang:admin::lang.form.create_title',
            'redirect'      => 'diligentsquad/customizationext/customization/edit/{id}',
            'redirectClose' => 'diligentsquad/customizationext/customization',
            'redirectNew'   => 'diligentsquad/customizationext/customization/create',
        ],
        'edit'       => [
            'title'         => 'lang:admin::lang.form.edit_title',
            'redirect'      => 'diligentsquad/customizationext/customization/edit/{id}',
            'redirectClose' => 'diligentsquad/customizationext/customization',
            'redirectNew'   => 'diligentsquad/customizationext/customization/create',
        ],
        'preview'    => [
            'title'    => 'lang:admin::lang.form.preview_title',
            'redirect' => 'diligentsquad/customizationext/customization',
        ],
        'configFile' => 'customization',
    ];

    protected $requiredPermissions = 'Diligentsquad.Customizationext';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('customization', 'diligentsquad.customizationext');
    }
}
