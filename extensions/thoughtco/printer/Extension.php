<?php

namespace Thoughtco\Printer;

use Admin\Facades\AdminAuth;
use Admin\Facades\AdminLocation;
use Admin\Widgets\Form;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use System\Classes\BaseExtension;
use System\Facades\Assets;
use Thoughtco\Printer\Models\Docket;
use Thoughtco\Printer\Models\Printer;

class Extension extends BaseExtension
{
    public function register()
    {
        $this->app->singleton('printhelper', Classes\PrintHelper::class);
        $aliasLoader = AliasLoader::getInstance();
        $aliasLoader->alias('PrintHelper', Facades\PrintHelper::class);
    }

    public function boot()
    {
        // add autoprint everywhere
        $this->addAutoprintEverywhere();

		// add print button to form fields
        $this->extendActionFormFields();

		// add print button to list fields
		$this->extendListColumns();

		$this->addPrintButtonToOrderView();

        // extend option values model
		\Admin\Models\Menu_option_values_model::extend(function ($model){
			$model->fillable(array_merge($model->getFillable(), ["print_docket"]));
		});
    }

    public function registerPermissions()
    {
        return [
            'Thoughtco.Printer.Manage' => [
                'description' => 'Create, modify and delete printers',
                'group' => 'module',
            ],
            'Thoughtco.Printer.View' => [
                'description' => 'Print and autoprint dockets',
                'group' => 'module',
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'tools' => [
                'child' => [
                    'printer' => [
                        'priority' => 10,
                        'class' => 'printer',
                        'href' => admin_url('thoughtco/printer/printers'),
                        'title' => lang('thoughtco.printer::default.text_title'),
                        'permission' => 'Thoughtco.Printer.*',
                    ],
                ],
            ],
        ];
    }

    protected function addAutoprintEverywhere()
    {
	    // enable autoprint everywhere in the admin panel
        Event::listen('admin.controller.beforeResponse', function ($controller, $params){

			// only show if logged in
			if (!AdminAuth::isLogged()) return;

	        // not on the autoprint page
	        if (!($controller instanceof \Thoughtco\Printer\Controllers\Autoprint || $controller instanceof \Thoughtco\Printer\Controllers\Printdocket)){

		        // build list of printers the user is allowed to access by location
		        $printerList = [];
		        Printer::where(['is_enabled' => true])
				->each(function($printer) use (&$printerList) {
			        if (isset($printer->printer_settings['autoprint_everywhere']) AND $printer->printer_settings['autoprint_everywhere']) {
						if (AdminLocation::getId() === NULL || AdminLocation::getId() == $printer->location_id) {
							$autoprintUsers = Arr::get($printer->printer_settings, 'autoprint_users', []);
							if (!Arr::get($printer->printer_settings, 'autoprint_limit_users', false) OR in_array(AdminAuth::getId(), $autoprintUsers)) {
				        		$printerList[] = $printer->id;
							}
				        }
			        }
		        });

		        if (count($printerList)){

			        // make printer list available
			        Assets::putJsVars(['thoughtco_printer' => $printerList, 'thoughtco_printer_base' => admin_url('thoughtco/printer/autoprint?location=')]);

			        // add autoprint everywhere js
			        $controller->addJs('extensions/thoughtco/printer/assets/js/autoprint-everywhere-1.0.1.js', 'thoughtco-printer');
			        $controller->addCss('extensions/thoughtco/printer/assets/css/autoprint-everywhere.css', 'thoughtco-printer');

		        }

	        }

        });
    }

	protected function addPrintButtonToOrderView()
	{
		Event::listen('admin.toolbar.extendButtons', function (&$widget) {

			if ($widget->getController() instanceof \Admin\Controllers\Orders) {

				if ($widget->getContext() == 'edit') {

					$form = $widget->getController()->widgets['form'];

					$printerList = Printer::where([
						'is_enabled' => true,
						'location_id' => $form->model->location_id,
					])
					->get()
					->map(function($printer) {
						return (object)[
							'id' => $printer->id,
							'location' => $printer->location->location_id,
							'label' => $printer->label,
						];
					});

					$widget->addButton('print', [
            			'label' => 'lang:thoughtco.printer::default.btn_print',
            			'partial' => '$/thoughtco/printer/views/partials/button',
						'model' => $form->model,
            			'printerList' => $printerList,
            			'class' => 'btn btn-primary',
					]);

				}

			}

		});
	}

    protected function extendActionFormFields()
    {

	    // this flag is necessary to stop menu_options_model firing multiple times
	    $isExtended = false;

        Event::listen('admin.form.extendFieldsBefore', function (Form $form) use (&$isExtended) {

	        if ($isExtended) return;

	        // if its an menu form
            if ($form->model instanceof \Admin\Models\Menus_model) {

				$form->tabs['fields']['print_docket'] = [
				 	 'label' => 'Docket text',
			         'type' => 'text',
			         'span' => 'left'
			    ];

			    $isExtended = true;

			}

	        // if its an menu options form
            if ($form->model instanceof \Admin\Models\Menu_options_model) {

				$form->fields['option_values']['form']['fields']['print_docket'] = [
				 	 'label' => 'Docket text',
			         'type' => 'text'
			    ];

			    $isExtended = true;

			}

        });

    }

	// extend order list to add print button
	protected function extendListColumns(){

		Event::listen('admin.list.extendColumns', function (&$widget) {

			if ($widget->getController() instanceof \Admin\Controllers\Orders){

				$printerList = Printer::where(['is_enabled' => true])
				->get()
				->map(function($printer) {
					return (object)[
						'id' => $printer->id,
						'location' => $printer->location->location_id,
						'label' => $printer->label,
					];
				});

				$widget->addColumns(['print_me' => [
					'invisible' => false,
					'label' => lang('thoughtco.printer::default.btn_print'),
					'type' => 'text',
					'valueFrom' => 'order_id',
					'defaults' => 1,
					'formatter' => function($row, $column, $value) use ($printerList) {

						$printers = $printerList->filter(function($printer) use ($row){
							return $row->location_id == $printer->location;
						});

						if (count($printers) <= 1) {

							return '<a class="btn btn-primary" href="'.admin_url('thoughtco/printer/printdocket?sale='.$value).'">'.lang('thoughtco.printer::default.btn_print').'</a>';

						} else {

							return '
							<div class="dropdown">
							  <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">'.lang('thoughtco.printer::default.btn_print').'</button>
							  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
							    <a class="dropdown-item" href="'.admin_url('thoughtco/printer/printdocket?sale='.$value).'">'.lang('thoughtco.printer::default.btn_print_all').'</a>
								'.($printers->map(function($printer) use ($value) {
									return '<a class="dropdown-item" href="'.admin_url('thoughtco/printer/printdocket?sale='.$value.'&printer='.$printer->id).'">'.$printer->label.'</a>';
								})->join(' ')).'
							  </div>
							</div>
							';
						}
					}
				]]);

			}

			if ($widget->getController() instanceof \Thoughtco\Printer\Controllers\Printers){

                if (!AdminAuth::user()->hasPermission('Thoughtco.Printer.Manage'))
                {
                    $widget->removeColumn('edit');
                }

			}

		});

		Event::listen('admin.toolbar.extendButtons', function (&$widget) {

			if ($widget->getController() instanceof \Thoughtco\Printer\Controllers\Printers){

                if (!AdminAuth::user()->hasPermission('Thoughtco.Printer.Manage'))
                {
                    $widget->getController()->widgets['toolbar']->removeButton('create');
                    $widget->getController()->widgets['toolbar']->removeButton('delete');
                }

			}
		});

	}
}
