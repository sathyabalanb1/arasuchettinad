<?php

namespace Thoughtco\Printer\Controllers;

use AdminMenu;
use Admin\Models\Orders_model;
use ApplicationException;
use DB;
use PrintHelper;
use Request;
use Template;
use Thoughtco\Printer\Models\Printer;
use Thoughtco\Printer\Models\Settings;
use Thoughtco\Printer\Classes\Printerfunctions;

/**
 * Autoprint Admin Controller
 */
class Autoprint extends \Admin\Classes\AdminController
{

    protected $requiredPermissions = 'Thoughtco.Printer.*';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('tools', 'printer');
        Template::setTitle(lang('thoughtco.printer::default.btn_autoprint'));

    }

    public function index()
    {

		$checkInterval = 30;

	    // get sales not yet printed
        if (($printerId = Request::get('location'))){

	        $settings = $this->printerSettings($printerId);

		    // update sale status
		    if (($saleId = Request::get('updateSale'))){

		    	$sale = Orders_model::where('order_id', $saleId)->first();

		    	// valid sale
		    	if ($sale !== NULL){

		       		if ($settings !== false){

				    	// if we set status
				    	if ($settings->setstatus != -1){
					    	$sale->updateOrderStatus($settings->setstatus);
					    }

				    }

			    }

				echo 1;
				exit();

		    }

		    // get sales
		    if (Request::get('getSales')){

		        $return = [];

		        if ($settings !== false){

					$sales = Orders_model::where(function($query) use ($settings){

						$getStatuses = $settings->getstatus ?? setting('default_order_status', 1);
						if (!is_array($getStatuses))
							$getStatuses = [$getStatuses];

						$query = $query
							->where('location_id', '=', $settings->location_id)
							->whereIn('status_id', $getStatuses);

						if ($settings->autoprint_sameday){
							$query = $query->where('order_date', '=', date('Y-m-d'));
						}

						return $query;
					})
					->orderBy('order_id', 'asc')
					->limit($settings->autoprint_quantity ?? 10)
					->get();

					foreach ($sales as $sale){

				    	$js = false;

						// legacy support
						if (isset($settings->output_format)){

					    	// turn sale data into variables
					    	$variables = Printerfunctions::getSaleData($sale, $settings->categories ?? [], $settings->encoding, $settings->type);

					    	// if we have something to print
					    	if (count($variables['order_menus']) > 0){

								// render the blade or pagic template
								$output = Printerfunctions::renderTemplate($printerId, (object)$settings, $variables);

								$js = $settings->type == 'ethernet' ? Printerfunctions::orderToEthernetJS($output, $settings) : $output;

					    	}

						// v2 and up supports multiple dockets
						} else {

							foreach ($settings->dockets as $docket){

								// all or autoprint
								if (in_array($docket->settings['contexts'], [0,2])) {

					    			// turn sale data into variables
					    			$variables = Printerfunctions::getSaleData($sale, $docket->settings['categories'] ?? [], $settings->encoding, $settings->type);

							    	// if we have something to print
							    	if (count($variables['order_menus']) > 0){

										$docketSettings = $docket->docket->docket_settings;
										$settings->format = $docketSettings['format'];

										// render the blade or pagic template
										$output = Printerfunctions::renderTemplate($docket->docket->id, $settings, $variables);

										if ($js === false) {
											$js = $settings->type == 'ethernet' ? [] : '';
										} else {
											if ($settings->autocut == 2)
												$output = "\r\n>>>>>\r\n".$output;
										}

										if ($docketSettings['lines_before'] > 0) {
											$output = str_repeat("\r\n", $docketSettings['lines_before']).$output;
										}

										if ($docketSettings['lines_after'] > 0) {
											$output .= str_repeat("\r\n", $docketSettings['lines_after']);
										}

										$output = $settings->type == 'ethernet' ? Printerfunctions::orderToEthernetJS($output, $settings) : $output;

										$copies = $docket->settings['copies'] ?? 1;
										$copies = (int)$copies;
										if ($copies < 1) $copies = 1;

                                        if ($settings->type == 'ethernet') {

                							for ($i=1; $i<=$copies; $i++)
    											array_push($js, ...$output);

                                        } else {

                							for ($i=1; $i<=$copies; $i++)
    											$js .= $output;

                                        }

							    	}

								}

							}

						}

						// turn our output into epson friendly js or esc/p
				    	$return[] = [
							'js' => $js,
							'id' => $sale->order_id
				    	];

					}

				}

				echo json_encode($return);
				exit();

			}

			if (isset($settings->autoprint_interval))
				$checkInterval = $settings->autoprint_interval;

	    }

		$this->vars['checkInterval'] = $checkInterval;

        // add the js library
        foreach (PrintHelper::getJavascript() as $jsfile)
            $this->addJs($jsfile, 'thoughtco-printer');

		$this->addJs('extensions/thoughtco/printer/assets/js/autoprint-1.0.1.js', 'thoughtco-printer');

    }

    public function printerSettings($printerId)
    {

    	$printer = Printer::where('id', $printerId)->first();

    	// valid printer
    	if ($printer !== NULL){

	    	$settings = (object)$printer->printer_settings;
	    	$settings->location_id = $printer->location_id;
	    	$settings->label = $printer->label;

			// legacy support
			if (isset($printer->printer_settings['usedefault'])) {

		    	// use default format
		    	if ($printer->printer_settings['usedefault'] == 1) {

			    	$settings->output_format = Settings::get('output_format', '');
			    	$settings->lines_before = Settings::get('lines_before', 0);
			    	$settings->lines_after = Settings::get('lines_after', 0);
					$settings->encoding = Settings::get('encoding', 'windows-1252');

			    // use custom format
		    	} else {

			    	$settings->output_format = $printer->printer_settings['format'];
			    	$settings->lines_before = $printer->printer_settings['lines_before'];
			    	$settings->lines_after = $printer->printer_settings['lines_after'];
					$settings->encoding = $printer->printer_settings['encoding'];

		    	}

		    	if ($settings->lines_before == '') $settings->lines_before = 0;
		    	if ($settings->lines_after == '') $settings->lines_after = 0;

			// v2 and up
			} else {

				$settings->encoding = $printer->printer_settings['type'] == 'ethernet' ? 'utf-8' : $printer->printer_settings['encoding'];
				$settings->lines_before = 0;
				$settings->lines_after = 0;
				$settings->dockets = $printer->dockets->sortBy('printer_docket_id')->sortBy('priority');

			}

			// how many copies to print?
			$copies = intval($printer->printer_settings['copies']);
			if ($copies < 1) $copies = 1;
	    	$settings->copies = $copies;
	    	$settings->autocut = $printer->printer_settings['autocut'];

			return $settings;
	    }

	    return false;

	}

    public function renderAutoprint()
    {

        // location
        if (($printerId = Request::get('location'))){

	    	$settings = $this->printerSettings((int)$printerId);

	    	// valid printer
	    	if ($settings !== false){

		    	// JS doesnt need this
		    	unset($settings->output_format);
		    	unset($settings->dockets);

				$langStrings = [
					'fail_retrieve' => lang('thoughtco.printer::default.fail_retrieve'),
					'fail_connect' => lang('thoughtco.printer::default.fail_connect'),
					'fail_connect_retry' => lang('thoughtco.printer::default.fail_connect_retry'),
					'fail_try' => lang('thoughtco.printer::default.fail_try'),
					'last_order_printed' => lang('thoughtco.printer::default.last_order_printed'),
				];

				return '<script>PRINTER_SETTINGS='.json_encode($settings).'; PRINTER_LANG_STRINGS='.json_encode($langStrings).';</script>';

		    }

	    }

	    return false;

    }

}
