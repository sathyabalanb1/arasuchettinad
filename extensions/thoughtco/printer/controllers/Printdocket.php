<?php

namespace Thoughtco\Printer\Controllers;

use AdminMenu;
use ApplicationException;
use DB;
use PrintHelper;
use Request;
use Thoughtco\Printer\Models\Printer;
use Thoughtco\Printer\Models\Settings;
use Thoughtco\Printer\Classes\Printerfunctions;

/**
 * Automation Admin Controller
 */
class Printdocket extends \Admin\Classes\AdminController
{

    protected $requiredPermissions = 'Thoughtco.Printer.*';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('tools', 'printer');
        $this->addJs('extensions/thoughtco/printer/assets/js/escprint-1.0.7.js', 'thoughtco-printer');

    }

    public function index()
    {
        
    }

    public function renderPrintdocket($sale = false)
    {
	    $previousPage = $_SERVER['HTTP_REFERER'] ?? '/admin/orders';

	    $renderHtml = '<h1>'.lang('thoughtco.printer::default.printing_docket').'</h1>'.PHP_EOL;
	    $renderHtml .= '<p><br /><a class="btn btn-secondary" href="'.$previousPage.'">'.lang('thoughtco.printer::default.btn_back').'</a></p>'.PHP_EOL;

        $renderHtml .= '<script type="text/javascript">
        window.addEventListener("DOMContentLoaded", function(){'.PHP_EOL;

        // get the sale
        if ($saleId = Request::get('sale') OR $sale)
        {
	    	if (!$sale)
                $sale = \Admin\Models\Orders_model::where('order_id', $saleId)->first();

	    	// valid sale
	    	if ($sale !== NULL){

                $queryParams = Request::get('printer', false) ? ['id' => Request::get('printer', 1)] : ['location_id' => $sale->location_id, 'is_enabled' => true];

	    		// loop over all printers for this location
		    	Printer::where($queryParams)
		    	->each(function($printer) use(&$renderHtml, $sale, $previousPage){

			    	// valid printer
			    	if ($printer !== NULL){

				    	// redirect means success
				    	if (Request::get('redirect'))
                            return;

						$encoding = 'windows-1252';
						$outputFormat = '';

						// legacy support
						if (isset($printer->printer_settings['usedefault'])) {

					    	// use default format
					    	if ($printer->printer_settings['usedefault'] == 1) {

						    	$linesBefore = Settings::get('lines_before', 0);
						    	$linesAfter = Settings::get('lines_after', 0);
								$encoding = Settings::get('encoding', 'windows-1252');

						    // use custom format
					    	} else {

						    	$linesBefore = $printer->printer_settings['lines_before'];
						    	$linesAfter = $printer->printer_settings['lines_after'];
								$encoding = $printer->printer_settings['encoding'];

					    	}

					    	if ($linesBefore == '') $linesBefore = 0;
					    	if ($linesAfter == '') $linesAfter = 0;

					    	// turn sale data into variables
					    	$variables = Printerfunctions::getSaleData($sale, $printer->printer_settings['categories'] ?? [], $encoding, $printer->printer_settings['type']);

					    	// if we have something to print
					    	if (count($variables['order_menus']) > 0){

								// render the blade or pagic template
								$outputFormat = Printerfunctions::renderTemplate($printer->id, (object)$printer->printer_settings, $variables);

							}

						// v2 and up: multiple dockets
						} else {

					    	$linesBefore = 0;
					    	$linesAfter = 0;
							$encoding = $printer->printer_settings['type'] == 'ethernet' ? 'utf-8' : $printer->printer_settings['encoding'];

							foreach ($printer->dockets->sortBy('printer_docket_id')->sortBy('priority') as $docket){

								// all or print docket
								if (in_array($docket->settings['contexts'], [0,1])) {

					    			// turn sale data into variables
					    			$variables = Printerfunctions::getSaleData($sale, $docket->settings['categories'] ?? [], $encoding, $printer->printer_settings['type']);

									// copies
									$copies = $docket->settings['copies'] ?? 1;
									$copies = (int)$copies;
									if ($copies < 1) $copies = 1;

							    	// if we have something to print
							    	if (count($variables['order_menus']) > 0){

										$docketSettings = $docket->docket->docket_settings;
										$settings = (object)$printer->printer_settings;
										$settings = clone $settings;
										$settings->format = $docketSettings['format'];

										// render the blade or pagic template
										$output = Printerfunctions::renderTemplate($docket->docket->id, $settings, $variables);

										if ($docketSettings['lines_before'] > 0) {
											$output = str_repeat("\r\n", $docketSettings['lines_before']).$output;
										}

										if ($docketSettings['lines_after'] > 0) {
											$output .= str_repeat("\r\n", $docketSettings['lines_after']);
										}

										for ($i=1; $i<=$copies; $i++) {
											if ($outputFormat != '' && $printer->printer_settings['autocut'] == 2) {
												$outputFormat .= "\r\n>>>>>\r\n";
											}
											$outputFormat .= $output;
										}

							    	}

								}

							}

						}

						if ($outputFormat != '') {

							$settings = [
								'lines_before' => $linesBefore,
								'lines_after' => $linesAfter,
							];

                            $renderHtml .= PrintHelper::print($printer, $settings, $outputFormat);

				    	}

			    	} else {

		        		$renderHtml .= 'alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_printer').'");';

			    	}

			    	// if we set status
					$manualSetStatus = array_get($printer->printer_settings, 'manual_setstatus', -1);
			    	if ($manualSetStatus != -1){
				    	$sale->updateOrderStatus($manualSetStatus);
				    }

		    	});

	    	} else {

	        	$renderHtml .= 'alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_sale').'");';

	    	}

        } else {

	        $renderHtml .= 'alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_sale').'");';

        }

        // where do we go from here?
        $redirectTo = Request::get('redirect') ?? $_SERVER['REQUEST_URI'].(stripos($_SERVER['REQUEST_URI'], '?') === false ? '?' : '').'&redirect='.urlencode($previousPage);

        // redirect
        if (Request::get('redirect')) {
	        header('Location: '.$redirectTo);
	        exit();
        }

		$renderHtml .= '});

		// after 6 seconds redirect to confirm page
		window.setTimeout(function(){
			location.href="'.$redirectTo.'";
		}, 6000);

		</script>';

        return $renderHtml;

    }

}
