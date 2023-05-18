<?php

namespace Thoughtco\Printer\Controllers;

use AdminMenu;
use Admin\Facades\AdminLocation;
use ApplicationException;

/**
 * Dockets Admin Controller
 */
class Dockets extends \Admin\Classes\AdminController
{
    public $implement = [
        'Admin\Actions\FormController',
        'Admin\Actions\ListController',
    ];

    public $listConfig = [
        'list' => [
            'model' => 'Thoughtco\Printer\Models\Docket',
            'title' => 'lang:thoughtco.printer::default.text_docket_title',
            'emptyMessage' => 'lang:thoughtco.printer::default.text_docket_empty',
            'defaultSort' => ['id', 'DESC'],
            'configFile' => 'docket',
        ],
    ];

    public $formConfig = [
        'name' => 'lang:thoughtco.printer::default.text_docket_form_name',
        'model' => 'Thoughtco\Printer\Models\Docket',
        'create' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'thoughtco/printer/dockets/edit/{id}',
            'redirectClose' => 'thoughtco/printer/dockets',
        ],
        'edit' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'thoughtco/printer/dockets/edit/{id}',
            'redirectClose' => 'thoughtco/printer/dockets',
        ],
        'preview' => [
            'title' => 'lang:admin::lang.form.preview_title',
            'redirect' => 'thoughtco/printer/dockets',
        ],
        'delete' => [
            'redirect' => 'thoughtco/printer/dockets',
        ],
        'configFile' => 'docket',
    ];

    protected $requiredPermissions = 'Thoughtco.Printer.*';

    public function __construct()
    {
        parent::__construct();
        AdminMenu::setContext('tools', 'printer');
    }
}
