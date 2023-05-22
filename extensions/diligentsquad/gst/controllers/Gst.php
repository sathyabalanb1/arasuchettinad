<?php

namespace Diligentsquad\Gst\Controllers;

use Admin\Facades\AdminMenu;

/**
 * Gst Admin Controller
 */
class Gst extends \Admin\Classes\AdminController
{
    public $implement = [
        \Admin\Actions\FormController::class,
    ];

    public $formConfig = [
        'name'       => 'Gst',
        'model'      => 'Diligentsquad\Gst\Models\GstModel',
        'create'     => [
            'title'         => 'lang:admin::lang.form.create_title',
            'redirect'      => 'diligentsquad/gst/gst/edit/{id}',
            'redirectClose' => 'diligentsquad/gst/gst',
            'redirectNew'   => 'diligentsquad/gst/gst/create',
        ],
        'configFile' => 'gst_model',
    ];

    protected $requiredPermissions = 'Diligentsquad.Gst';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('gst', 'diligentsquad.gst');
    }
}
