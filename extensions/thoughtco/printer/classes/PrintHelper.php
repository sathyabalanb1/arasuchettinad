<?php

namespace Thoughtco\Printer\Classes;

use Illuminate\Support\Collection;
use Thoughtco\Printer\Models\Printer;

class PrintHelper {

    public function getJavascript() : Array
    {
        return [
            'extensions/thoughtco/printer/assets/js/encoding-indexes.js',
            'extensions/thoughtco/printer/assets/js/encoding.js',
            'extensions/thoughtco/printer/assets/js/epos-2.14.0.js',
            'extensions/thoughtco/printer/assets/js/escprint-1.0.7.js',
        ];
    }

    public function print(Printer $printer, Array $settings, String $output) : String
    {

		$settings = array_merge([
			'lines_before' => 0,
			'lines_after' => 0,
			'copies' => 1,
			'autocut' => $printer->printer_settings['autocut'],
			'characters_per_line' => $printer->printer_settings['characters_per_line'] ?? 48,
			'codepage' => $printer->printer_settings['codepage'] ?? 16,
			'encoding' => $printer->printer_settings['type'] == 'ethernet' ? 'utf-8' : $printer->printer_settings['encoding'],
			'font' => $printer->printer_settings['font'],
		], $settings);

		$renderHtml = '
            try {
        ';

		// usb
		if ($printer->printer_settings['type'] == 'usb'){

			$renderHtml .= '
			(async function(){

				try {

					var devices = await ESCPrint.getDevices();

					if (devices.length){

						await ESCPrint.sendText(devices[0], '.json_encode($settings).', `'.$output.'`);
						return;

					} else {

						alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_connect').'");
						return;
					}

				} catch (e){
					console.error(e);
					alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_connect').'");
				}

			}());
			';

		// bluetooth
		} else if ($printer->printer_settings['type'] == 'bluetooth'){

			$renderHtml .= '
			(async function(){

				try {

					var devices = await ESCPrint.getDevicesBluetooth();

					for (const device of devices){

						var abortController = new AbortController();

						device.addEventListener(\'advertisementreceived\', async (event) => {

							abortController.abort();

							ESCPrint.sendText(event.device, '.json_encode($settings).', `'.$output.'`);
							return;


						});

						await device.watchAdvertisements({ signal: abortController.signal });
						return;

					}

					alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_connect').'");
					return;

				} catch (e){
					console.error(e);
					alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_connect').'");
				}

			}());
			';

		// ip
		} else if ($printer->printer_settings['type'] == 'ip'){

			$socketUrl = $printer->printer_settings['ssl'] == 1 ? 'wss://' : 'ws://';
			$socketUrl .= $printer->printer_settings['ip_address'];
			$socketUrl .= ':'.$printer->printer_settings['port'];

			$renderHtml .= '
			(async function(){

				// Create WebSocket connection.
				let socket = new WebSocket("'.$socketUrl.'");

				socket.onclose = function (event) {
					//alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_connect').'");
					return;
				};

				socket.onerror = function (event) {
					alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_connect').'");
					return;
				};

				// Connection opened
				socket.onopen = async function (event) {
					await ESCPrint.sendText(socket, '.json_encode($settings).', `'.$output.'`);
					return;
				};

			}());
			';

		// ethernet
		} else {

			// turn our output into epson friendly js
			$outputFormat = Printerfunctions::orderToEthernetJs($output, $printer->printer_settings);

			// build a single print
			$printData = '';
			$printData .= ($settings['lines_before'] > 0 ? 'deviceObj.addFeedLine('.$settings['lines_before'].');' : '');
			$printData .= implode("\r\n", $outputFormat);
			$printData .= ($settings['lines_after'] > 0 ? 'deviceObj.addFeedLine('.$settings['lines_after'].');' : '');
			if ($printer->printer_settings['autocut'] != 0) $printData .= 'deviceObj.addCut();';

			// copies
			$finalPrintData = '';
			for ($i=0; $i<$settings['copies']; $i++){
				$finalPrintData .= $printData;
			}

	    	// html to output
	    	$renderHtml .= '
			var ePosDev = new epson.ePOSDevice();
			ePosDev.connect(
				"'.$printer->printer_settings['ip_address'].'",
				'.$printer->printer_settings['port'].',
				(connectionResult) => {

					var deviceId = \''.$printer->printer_settings['device_name'].'\';
					var options = {\'crypto\': true, \'buffer\': false};

					if ((connectionResult == \'OK\') || (connectionResult == \'SSL_CONNECT_OK\')){

						//Retrieves the Printer object
						ePosDev.createDevice(
							deviceId,
							ePosDev.DEVICE_TYPE_PRINTER,
							options,
							(deviceObj, errorCode) => {

								// no device
								if (deviceObj === null){
									alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_connect').'");
									return;
								}

								if (errorCode == \'OK\'){

									'.$finalPrintData.'
									deviceObj.send();
									ePosDev.disconnect();

								}

							}
						);

					} else {

						alert("'.$printer->label.': '.lang('thoughtco.printer::default.fail_connect').'");

					}

				}
			);
	    	;';

    	}

        $renderHtml .= '
            } catch (e){
                console.error(e);
            }
        ';

        return $renderHtml;
    }

}

?>
