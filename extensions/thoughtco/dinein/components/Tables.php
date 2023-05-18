<?php

namespace Thoughtco\Dinein\Components;

use Admin\Models\Tables_model;
use Location;
use Session;

class Tables extends \System\Classes\BaseComponent
{
    public function initialize()
    {
        $selectedLocation = Location::getId();

        $this->page['tables'] = Tables_model::whereHasLocation($selectedLocation)
            ->select(['table_id', 'table_name'])
            ->dropdown('table_name');

        $this->page['selectedTable'] = -1;
        if ($data = Session::get('thoughtco.dinein') AND $data['location'] == $selectedLocation)
            $this->page['selectedTable'] = $data['table'];

        if (Location::orderType() == 'waiter' AND ($locationModel = Location::current())) {
            $this->page['autofillData'] = [
                'first_name' => $locationModel->options['waiter_autofill_firstname'] ?? '',
                'last_name' => $locationModel->options['waiter_autofill_lastname'] ?? '',
                'email' => $locationModel->options['waiter_autofill_email'] ?? '',
                'telephone' => $locationModel->options['waiter_autofill_telephone'] ?? '',
            ];
        }

        $this->page['hideField'] = $this->property('hideField') ?? false;

    }

    public function defineProperties()
    {
        return [
            'hideField' => [
                'label' => 'lang:thoughtco.dinein::default.label_table_component_hide',
                'comment' => 'lang:thoughtco.dinein::default.label_table_component_hide_comment',
                'type' => 'switch',
                'default' => FALSE,
                'validationRule' => 'required|boolean',
            ],
        ];
    }
}
