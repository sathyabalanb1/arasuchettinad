<?php

namespace Thoughtco\Printer\Controllers;

use AdminAuth;
use AdminMenu;
use Admin\Facades\AdminLocation;
use ApplicationException;
use Thoughtco\Printer\Models\Docket;
use Thoughtco\Printer\Models\Printer;

/**
 * Printers Admin Controller
 */
class Printers extends \Admin\Classes\AdminController
{
    public $implement = [
        'Admin\Actions\FormController',
        'Admin\Actions\ListController',
    ];

    public $listConfig = [
        'list' => [
            'model' => 'Thoughtco\Printer\Models\Printer',
            'title' => 'lang:thoughtco.printer::default.text_title',
            'emptyMessage' => 'lang:thoughtco.printer::default.text_empty',
            'defaultSort' => ['id', 'DESC'],
            'configFile' => 'printer',
        ],
    ];

    public $formConfig = [
        'name' => 'lang:thoughtco.printer::default.text_form_name',
        'model' => 'Thoughtco\Printer\Models\Printer',
        'create' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'thoughtco/printer/printers/edit/{id}',
            'redirectClose' => 'thoughtco/printer/printers',
        ],
        'edit' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'thoughtco/printer/printers/edit/{id}',
            'redirectClose' => 'thoughtco/printer/printers',
        ],
        'preview' => [
            'title' => 'lang:admin::lang.form.preview_title',
            'redirect' => 'thoughtco/printer/printers',
        ],
        'delete' => [
            'redirect' => 'thoughtco/printer/printers',
        ],
        'configFile' => 'printer',
    ];

    protected $requiredPermissions = 'Thoughtco.Printer.*';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('tools', 'printer');
        $this->addJs('extensions/thoughtco/printer/assets/js/escprint-1.0.7.js', 'thoughtco-printer');

    }

    public function index()
    {
        $this->asExtension('ListController')->index();
    }

    public function create()
    {
        if (!AdminAuth::user()->hasPermission('Thoughtco.Printer.Manage'))
            throw new ApplicationException('Permission denied');

        return parent::create();
    }

    public function edit($id, $id2)
    {
        if (!AdminAuth::user()->hasPermission('Thoughtco.Printer.Manage'))
            throw new ApplicationException('Permission denied');

        return parent::edit($id, $id2);
    }

    public function listExtendQuery($query)
    {
        if ($locationId = $this->getLocationId()){
            $query->where('location_id', $locationId);
        }
    }

    public function formExtendQuery($query)
    {
        if ($locationId = $this->getLocationId()){
            $query->where('location_id', $locationId);
        }
    }

    public function edit_onChooseDocket($context, $recordId)
    {
        $docketId = post('Printer._dockets_choose');
        if (!$docket = Docket::find($docketId))
            throw new ApplicationException('Please select a docket to attach');

        $model = $this->asExtension('FormController')->formFindModelObject($recordId);

        $docketPrinterModel = $model->dockets()->create([
            'docket_id' => $docketId,
            'settings' => [
                'copies' => 1,
                'categories' => [],
                'contexts' => '0',
            ]
        ]);

        $model->reload();
        $this->asExtension('FormController')->initForm($model, $context);

        flash()->success(sprintf(lang('admin::lang.alert_success'), 'Docket attached'))->now();

        $formField = $this->widgets['form']->getField('dockets');

        return [
            '#notification' => $this->makePartial('flash'),
            '#'.$formField->getId('group') => $this->widgets['form']->renderField($formField, [
                'useContainer' => FALSE,
            ]),
        ];

    }

}
