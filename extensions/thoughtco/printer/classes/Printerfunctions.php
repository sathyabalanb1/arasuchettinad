<?php

namespace Thoughtco\Printer\Classes;

use Admin\Models\Menus_model;
use Admin\Models\Menu_item_option_values_model;
use Admin\Models\Menu_option_values_model;
use DB;
use Event;
use Igniter\Flame\Support\PagicHelper;
use Igniter\Flame\Support\StringParser;
use Thoughtco\Printer\Models\Settings;

class Printerfunctions {

	public static function templateDir(){
		return 'extensions/thoughtco/printer/views/temp/';
	}

	// delete all files
	public static function clearTemplates()
	{
		$files = glob(self::templateDir().'*.php');
		foreach ($files as $file){
			if (is_file($file))
		    	unlink($file);
		}
	}

	public static function removeUnprintableCharacters($string, $encoding, $printerType)
	{
		if ($encoding == 'utf-8' || $printerType == 'ethernet')
		    return $string;

        return iconv('utf-8', $encoding.'//translit', $string);
	}

    // get data from sale assigned to variables
    public static function getSaleData($model, $limitCategories = [], $encoding = 'windows-1252', $printerType = 'ethernet')
    {
        $data = $model->mailGetData();

        $data['order_type'] = $model->order_type;
        $data['order_type_name'] = $model->order_type_name;

        $data['site_name'] = setting('site_name');
        $data['site_url'] = str_replace(array('https://','http://'), '', site_url(''));

        $data['first_name'] = self::removeUnprintableCharacters($data['first_name'], $encoding, $printerType);
        $data['last_name'] = self::removeUnprintableCharacters($data['last_name'], $encoding, $printerType);
        $data['customer_name'] = self::removeUnprintableCharacters($data['customer_name'], $encoding, $printerType);
        $data['email'] = self::removeUnprintableCharacters($data['email'], $encoding, $printerType);
        $data['telephone'] = self::removeUnprintableCharacters($data['telephone'], $encoding, $printerType);
        $data['order_comment'] = self::removeUnprintableCharacters($data['order_comment'], $encoding, $printerType);

		$data['order_payment_code'] = ($model->payment_method) ? $model->payment_method->code : 'none';

        $data['order_menus'] = [];
        $menus = $model->printer_menus ?? $model->getOrderMenus();

        // order menus by category order
        foreach ($menus as $idx=>$menu){
	        $menu->category_priority = 100;
	        $menuModel = Menus_model::with(['categories'])->where('menu_id', $menu->menu_id)->first();
	        if ($menuModel && $menuModel->categories && count($menuModel->categories) > 0){

		        // if we have a category limitation and this item is not in it, then remove
		        if (count($limitCategories) > 0 && count(array_intersect($limitCategories, $menuModel->categories->pluck('category_id')->toArray())) == 0){
			        unset($menus[$idx]);

			    // otherwise order by priority
			    } else {
		        	$menu->category_priority = $menuModel->categories[0]->priority;
		        }

	        }
        }

        $menus = $menus->toArray();
        uasort($menus, function($a, $b){
	        return $a->category_priority > $b->category_priority ? 1 : -1;
        });

		$orderMenuOptions = $model->printer_menu_options ?? $model->getOrderMenuOptions();
		foreach ($menus as $menu)
		{
			$categoryName = '';
			$optionData = [];

			// get menu model item
			$menuModelItem = Menus_model::with(['categories', 'menu_options', 'menu_options.option_values', 'menu_options.menu_option_values'])
			->where('menu_id', $menu->menu_id)
			->first();

			if ($menuModelItem) {

                if ($menuModelItem->categories) {
    				if ($category = $menuModelItem->categories->sortBy('priority')->first()) {
    					$categoryName = $category->name;
    				}
                }

				if ($orderMenuItemOptions = $orderMenuOptions->get($menu->order_menu_id))
				{
					foreach ($orderMenuItemOptions as $orderMenuItemOption) {
						if ($orderMenuItemOption->quantity > 0){

							$optionText = $orderMenuItemOption->order_option_name;

							// loop over menu options in the menu_model
							foreach ($menuModelItem->menu_options as $modelMenuOption)
							{
								// if menu option is the same as the one in the order
								if ($modelMenuOption->menu_option_id = $orderMenuItemOption->order_menu_option_id)
								{
									// loop over menu_item_option_values
									foreach ($modelMenuOption->menu_option_values as $modelMenuOptionItemValue)
									{
										// if item value id is the same as the value in our order
										if ($modelMenuOptionItemValue->menu_option_value_id == $orderMenuItemOption->menu_option_value_id)
										{
											// loop over the actual values
											foreach ($modelMenuOption->option_values as $modelMenuOptionValue)
											{
												if ($modelMenuOptionItemValue->option_value_id == $modelMenuOptionValue->option_value_id)
												{
													if ($modelMenuOptionValue->print_docket != '')
													{
														$optionText = $modelMenuOptionValue->print_docket;
													}
												}
											}
										}
									}
								}
							}

							$optionData[] = [
								'menu_option_quantity' => $orderMenuItemOption->quantity,
								'menu_option_linequantity' => $menu->quantity * $orderMenuItemOption->quantity,
								'menu_option_name' => $optionText,
								'menu_option_price' => currency_format($orderMenuItemOption->order_option_price),
								'menu_option_subtotal' => currency_format($orderMenuItemOption->quantity * $orderMenuItemOption->order_option_price),
								'menu_option_linetotal' => currency_format($menu->quantity * $orderMenuItemOption->quantity * $orderMenuItemOption->order_option_price),
							];

						}
					}
				}

			}

			$data['order_menus'][] = [
				'menu_name' => (isset($menuModelItem->print_docket) && $menuModelItem->print_docket != '' ? $menuModelItem->print_docket : $menu->name),
				'menu_quantity' => $menu->quantity,
				'menu_price' => currency_format($menu->price),
				'menu_subtotal' => currency_format($menu->subtotal),
				'menu_options' => $optionData,
				'menu_comment' => self::removeUnprintableCharacters($menu->comment, $encoding, $printerType),
				'menu_category_name' => $categoryName,
			];
		}

        $data['order_totals'] = [];
        $orderTotals = $model->printer_totals ?? $model->getOrderTotals();
        foreach ($orderTotals as $total) {
            $data['order_totals'][] = [
				'order_total_title' => htmlspecialchars_decode($total->title),
				'order_total_code' => $total->code,
				'order_total_value' => currency_format($total->value),
				'priority' => $total->priority,
            ];
        }

        $data['order_address'] = self::removeUnprintableCharacters($data['order_address'], $encoding, $printerType);

        if ($model->location) {
	        $address = $model->location->getAddress();
	        $address['format'] = '{address_1}, {address_2}, {city}, {postcode}';
            $data['location_address'] = str_replace(', , ', ', ', format_address($address, TRUE));
            $data['location_telephone'] = $model->location->location_telephone;
        }

        Event::fire('thoughtco.printer.orderData', [$model, &$data]);

        return $data;
    }

    // render function
    public static function renderTemplate($printerId, $settings, $variables){

	    // what template do we use?
	    $template = isset($settings->usedefault) && $settings->usedefault ? Settings::get('output_format') : $settings->format;

		// characters per row
		$variables['charsPerRow'] = $settings->characters_per_line ?? 48;

    	// assume blade
    	if (!isset($settings->usedefault) OR stripos($template, '{{') !== false){

	    	// what template do we use?
	    	$printerTemplate = isset($settings->usedefault) && $settings->usedefault ? 'default' : 'docket'.$printerId;

	    	// create full path
	    	$fullPath = self::templateDir().$printerTemplate.'.blade.php';

	    	// if file doesn't exist then make it
	    	if (!file_exists($fullPath))
		    	file_put_contents($fullPath, $template);

	    	$render = view('thoughtco.printer::temp.'.$printerTemplate, $variables)->render();

    	// not blade
    	} else {

	    	// render our string with variables
			$render = PagicHelper::parse($template, $variables);
			$render = (new StringParser)->parse($template, $variables);

		}

		//var_dump($render); exit();

		return $render;

    }

    // output to epos ethernet format
    public static function orderToEthernetJs($output, $settings){

        $cmd = [];

        $settings = (array)$settings;

        $output = str_replace(["\r\n", "\r"], ["\n", "\n"], $output);
        $output = explode("\n", $output);

        $foundAlignment = '';
		$lastFontSize = 'p';

		$cmd[] = 'deviceObj.addTextSmooth(true);';
		$cmd[] = 'deviceObj.addTextLang("'.($settings['epson_language'] ?? 'en').'");';
		$cmd[] = 'deviceObj.addTextLineSpace('.array_get($settings, 'font.default_line', 30).');';

		foreach ($output as $o){

			$o = trim($o);

			// alignments
			if (stripos($o, '|>') === 0 || stripos($o, '<|') === 0 || stripos($o, '||') === 0){

				if (stripos($o, '|>') === 0){
					$foundAlignment = 'right';
				} else if (stripos($o, '||') === 0){
					$foundAlignment = 'center';
				} else {
					$foundAlignment = 'left';
				}

				$cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_'.strtoupper($foundAlignment).');';
				$o = substr($o, 2);

			}

            $o = str_replace('"', '', $o);

			// h6
			if (stripos($o, '###### ') === 0){

				// get string after #
				$o = str_replace(['###### '], '', $o);

				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_CENTER);';

				if ($lastFontSize != 'h6') {
					$cmd[] = 'deviceObj.addTextStyle(undefined, undefined, '.(array_get($settings, 'font.heading6_bold', 1) == 1 ? 'true' : 'false').', undefined);';
		        	$cmd[] = 'deviceObj.addTextLineSpace('.array_get($settings, 'font.heading6_line', 30).');';
					$cmd[] = 'deviceObj.addTextSize('.array_get($settings, 'font.heading6_horizontal', 1).', '.array_get($settings, 'font.heading6_vertical', 1).');';
					$lastFontSize = 'h6';
				}

				$cmd[] = 'deviceObj.addText("'.$o.'\r\n");';

				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_LEFT);';

			// h5
			} else if (stripos($o, '##### ') === 0){

				// get string after #
				$o = str_replace(['##### '], '', $o);

				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_CENTER);';

				if ($lastFontSize != 'h5') {
					$cmd[] = 'deviceObj.addTextStyle(undefined, undefined, '.(array_get($settings, 'font.heading5_bold', 1) == 1 ? 'true' : 'false').', undefined);';
		        	$cmd[] = 'deviceObj.addTextLineSpace('.array_get($settings, 'font.heading5_line', 30).');';
					$cmd[] = 'deviceObj.addTextSize('.array_get($settings, 'font.heading5_horizontal', 1).', '.array_get($settings, 'font.heading5_vertical', 1).');';
					$lastFontSize = 'h5';
				}

				$cmd[] = 'deviceObj.addText("'.$o.'\r\n");';
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_LEFT);';

			// h4
			} else if (stripos($o, '#### ') === 0){

				// get string after #
				$o = str_replace(['#### '], '', $o);

				// centre align but standard size
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_CENTER);';

				if ($lastFontSize != 'h4') {
					$cmd[] = 'deviceObj.addTextStyle(undefined, undefined, '.(array_get($settings, 'font.heading4_bold', 1) == 1 ? 'true' : 'false').', undefined);';
		        	$cmd[] = 'deviceObj.addTextLineSpace('.array_get($settings, 'font.heading4_line', 30).');';
					$cmd[] = 'deviceObj.addTextSize('.array_get($settings, 'font.heading4_horizontal', 1).', '.array_get($settings, 'font.heading4_vertical', 1).');';
					$lastFontSize = 'h4';
				}

				$cmd[] = 'deviceObj.addText("'.$o.'\r\n");';
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_LEFT);';

			// h3
			} else if (stripos($o, '### ') === 0){

				// get string after #
				$o = str_replace(['### '], '', $o);

				// centre align but standard size
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_CENTER);';

				if ($lastFontSize != 'h3') {
					$cmd[] = 'deviceObj.addTextStyle(undefined, undefined, '.(array_get($settings, 'font.heading3_bold', 1) == 1 ? 'true' : 'false').', undefined);';
		        	$cmd[] = 'deviceObj.addTextLineSpace('.array_get($settings, 'font.heading3_line', 36).');';
					$cmd[] = 'deviceObj.addTextSize('.array_get($settings, 'font.heading3_horizontal', 1).', '.array_get($settings, 'font.heading3_vertical', 2).');';
 					$lastFontSize = 'h3';
				}

				$cmd[] = 'deviceObj.addText("'.$o.'\r\n");';
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_LEFT);';

			// h2
			} else if (stripos($o, '## ') === 0){

				// get string after #
				$o = str_replace(['## '], '', $o);

				// centre align but standard size
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_CENTER);';

				if ($lastFontSize != 'h2') {
					$cmd[] = 'deviceObj.addTextStyle(undefined, undefined, '.(array_get($settings, 'font.heading2_bold', 1) == 1 ? 'true' : 'false').', undefined);';
		        	$cmd[] = 'deviceObj.addTextLineSpace('.array_get($settings, 'font.heading2_line', 42).');';
					$cmd[] = 'deviceObj.addTextSize('.array_get($settings, 'font.heading2_horizontal', 1).', '.array_get($settings, 'font.heading2_vertical', 3).');';
					$lastFontSize = 'h2';
				}

				$cmd[] = 'deviceObj.addText("'.$o.'\r\n");';
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_LEFT);';

			// h1
			} else if (stripos($o, '# ') === 0){

				// get string after #
				$o = str_replace('# ', '', $o);

				// centre align but standard size
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_CENTER);';

				if ($lastFontSize != 'h1') {
					$cmd[] = 'deviceObj.addTextStyle(undefined, undefined, '.(array_get($settings, 'font.heading1_bold', 1) == 1 ? 'true' : 'false').', undefined);';
		        	$cmd[] = 'deviceObj.addTextLineSpace('.array_get($settings, 'font.heading1_line', 48).');';
					$cmd[] = 'deviceObj.addTextSize('.array_get($settings, 'font.heading1_horizontal', 1).', '.array_get($settings, 'font.heading1_vertical', 4).');';
					$lastFontSize = 'h1';
				}

				$cmd[] = 'deviceObj.addText("'.$o.'\r\n");';
				if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_LEFT);';

			// hr
			} else if (stripos($o, '*****') === 0 || stripos($o, '-----') === 0){

				// centre align but standard size
				//$cmd[] = 'deviceObj.addFeedLine(1);';
				$cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_CENTER);';

				if ($lastFontSize != 'p') {
					$cmd[] = 'deviceObj.addTextStyle(undefined, undefined, '.(array_get($settings, 'font.default_bold', 1) == 1 ? 'true' : 'false').', undefined);';
		        	$cmd[] = 'deviceObj.addTextLineSpace('.array_get($settings, 'font.default_line', 30).');';
					$cmd[] = 'deviceObj.addTextSize('.array_get($settings, 'font.default_horizontal', 1).', '.array_get($settings, 'font.default_vertical', 1).');';
					$lastFontSize = 'p';
				}

				$horizontalSize = array_get($settings, 'font.default_horizontal', 1);

				$cmd[] = 'deviceObj.addText("-".repeat('.floor($settings['characters_per_line']/$horizontalSize).') + "\r\n");';
				$cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_LEFT);';
				//$cmd[] = 'deviceObj.addFeedLine(1);';

			// cut line
			} else if (stripos($o, '>>>>>') === 0){

				// cut
				$cmd[] = 'deviceObj.addCut(deviceObj.CUT_FEED);';

			// image
			} else if (stripos($o, '[img') === 0){

				$o = str_replace('[img', '', $o);
				$o = str_replace(']', '', $o);
				$o = trim($o);
				$o = explode(',', $o);

				if (count($o) == 2){
					if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_CENTER);';
					$cmd[] = 'deviceObj.addLogo('.trim($o[0]).','.trim($o[1]).');';
					if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_LEFT);';
				}

			// qrcode
			} else if (stripos($o, '[qrcode') === 0){

				$o = str_replace('[qrcode', '', $o);
				$o = str_replace(']', '', $o);
				$o = trim($o);
				$o = explode(',', $o);

				if (count($o) == 2){
					if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_CENTER);';
					$cmd[] = 'deviceObj.addSymbol("'.trim($o[1]).'", deviceObj.SYMBOL_QRCODE_MODEL_2, deviceObj.LEVEL_M, '.$o[0].', 1, 1);';
					if ($foundAlignment == '') $cmd[] = 'deviceObj.addTextAlign(deviceObj.ALIGN_LEFT);';
				}

			// new line
			} else if (trim($o) == ''){

				// centre align but standard size
				$cmd[] = 'deviceObj.addFeedLine(1);';

			// standard text
			} else {

				if ($lastFontSize != 'p') {
					$cmd[] = 'deviceObj.addTextStyle(undefined, undefined, '.(array_get($settings, 'font.default_bold', 1) == 1 ? 'true' : 'false').', undefined);';
		        	$cmd[] = 'deviceObj.addTextLineSpace('.array_get($settings, 'font.default_line', 30).');';
					$cmd[] = 'deviceObj.addTextSize('.array_get($settings, 'font.default_horizontal', 1).', '.array_get($settings, 'font.default_vertical', 1).');';
					$lastFontSize = 'p';
				}

				$cmd[] = 'deviceObj.addText("'.$o.'\r\n");';

			}

		}

		return $cmd;

    }

	// get variables
    public static function getVariables()
    {
        $vars = [
            'General' => [
                ['var' => '{{ $site_name }}', 'name' => 'Site name'],
                ['var' => '{{ $site_url }}', 'name' => 'Site URL'],

                ['var' => '{{ $location_name }}', 'name' => 'Location name'],
                ['var' => '{{ $location_telephone }}', 'name' => 'Location telephone'],
                ['var' => '{{ $location_email }}', 'name' => 'Location email'],
                ['var' => '{{ $location_address }}', 'name' => 'Location address'],
            ],
            'Customer' => [
                ['var' => '{{ $first_name }}', 'name' => 'Customer first name'],
                ['var' => '{{ $last_name }}', 'name' => 'Customer last name'],
                ['var' => '{{ $email }}', 'name' => 'Customer email address'],
                ['var' => '{{ $telephone }}', 'name' => 'Customer telephone address'],
            ],
            'Order' => [
                ['var' => '{{ $customer_name }}', 'name' => 'Customer full name'],
                ['var' => '{{ $order_number }}', 'name' => 'Order number'],
                ['var' => '{{ $order_view_url }}', 'name' => 'Order view URL'],
                ['var' => '{{ $order_type }}', 'name' => 'Order type ex. delivery/pick-up'],
                ['var' => '{{ $order_time }}', 'name' => 'Order delivery/pick-up time'],
                ['var' => '{{ $order_date }}', 'name' => 'Order delivery/pick-up date'],
                ['var' => '{{ $order_address }}', 'name' => 'Customer address for delivery order'],
                ['var' => '{{ $order_payment }}', 'name' => 'Order payment method'],
                ['var' => '{{ $order_payment_code }}', 'name' => 'Order payment method code'],
                ['var' => '{{ $order_menus }}', 'name' => 'Order menus (array)'],
                ['var' => '{{ $order_totals }}', 'name' => 'Order total pairs (array)'],
                ['var' => '{{ $order_comment }}', 'name' => 'Order comment'],
            ],
            'Order menus' => [
                ['var' => '{{ $menu_name }}', 'name' => 'Order menu name'],
                ['var' => '{{ $menu_category_name }}', 'name' => 'Order menu category name'],
                ['var' => '{{ $menu_quantity }}', 'name' => 'Order menu quantity'],
                ['var' => '{{ $menu_price }}', 'name' => 'Order menu price'],
                ['var' => '{{ $menu_subtotal }}', 'name' => 'Order menu subtotal'],
                ['var' => '{{ $menu_options }}', 'name' => 'Order menu items (array)'],
                ['var' => '{{ $menu_comment }}', 'name' => 'Order menu comment'],
            ],
            'Order menu options' => [
                ['var' => '{{ $menu_option_name }}', 'name' => 'Order menu option name'],
                ['var' => '{{ $menu_option_quantity }}', 'name' => 'Order menu option quantity'],
                ['var' => '{{ $menu_option_linequantity }}', 'name' => 'Order menu option line quantity'],
                ['var' => '{{ $menu_option_price }}', 'name' => 'Order menu option price'],
                ['var' => '{{ $menu_option_subtotal }}', 'name' => 'Order menu option subtotal'],
                ['var' => '{{ $menu_option_linetotal }}', 'name' => 'Order menu option linetotal'],
            ],
            'Order totals' => [
                ['var' => '{{ $order_total_title }}', 'name' => 'Order total title'],
                ['var' => '{{ $order_total_code }}', 'name' => 'Order total code'],
                ['var' => '{{ $order_total_value }}', 'name' => 'Order total value'],
            ],
            'Status' => [
                ['var' => '{{ $status_name }}', 'name' => 'Status name'],
                ['var' => '{{ $status_comment }}', 'name' => 'Status comment'],
            ],
        ];

        $all = [];
        foreach ($vars as $group=>$var) {
            array_push($all, ...$var);
        }
        $vars['All'] = $all;

		return $vars;

    }

}

?>
