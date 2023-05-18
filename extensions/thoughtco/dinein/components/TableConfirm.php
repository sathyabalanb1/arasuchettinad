<?php

namespace Thoughtco\Dinein\Components;

use Admin\Models\Locations_model;
use Admin\Models\Tables_model;
use App;
use Location;
use Session;

class TableConfirm extends \System\Classes\BaseComponent
{
    public function initialize()
    {
        $selectedLocation = Location::getId();

        $this->page['step'] = 1;

        if ($selected_table = request()->get('table', false)) {

            $foundTable = Tables_model::whereHasLocation($selectedLocation)
                ->where('table_status', 1)
                ->where('table_id', $selected_table)
                ->first();

            if (!$foundTable)
                abort(404);

            $this->page['step'] = 2;
            $this->page['table'] = $foundTable->table_id;

            if ($is_confirm = request()->get('confirm', false)) {

    			Session::put('thoughtco.dinein', [
                    'location' => $selectedLocation,
                    'table' => $selected_table
                ]);

                $orderType = request()->input('ordertype', 'dinein');
                if (!collect(['dinein', 'waiter'])->contains($orderType))
                    $orderType = 'dinein';

    			$locationModel = App::make('location');
    			$locationModel->updateOrderType($orderType);

                $this->page['redirect'] = config('app.url').'/'.$this->param('location').'/menus';
            }

        } else {

            $this->page['table'] = Tables_model::whereHasLocation($selectedLocation)
                ->where('table_status', 1)
                ->where('table_id', $this->param('tableid'))
                ->first();

            $this->page['tables'] = Tables_model::whereHasLocation($selectedLocation)
                ->where('table_status', 1)
                ->orderBy('priority')
                ->get();

        }
    }

    public function defineProperties()
    {
        return [
        ];
    }
}
