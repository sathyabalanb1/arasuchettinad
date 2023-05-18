(function(){

    // update sale so its no longer pending
    var updateSale = function(id){

	    fetch(
	    	location.href + '&updateSale=' + id,
	    	{
		    	method: 'GET'
			}
		)
		.then(function(response){ });

    }

    // not epson
    if (['usb', 'ip', 'bluetooth'].includes(PRINTER_SETTINGS.type)) {

    	var pollForSales = function(deviceObj){

		    fetch(
		    	location.href + '&getSales=1',
		    	{
			    	method: 'GET'
				}
			)
			.then(function(response){
				return response.json();
			})
			.then(function(response){

				for (responsei=0; responsei<response.length; responsei++){

					var order = response[responsei];

					try {

                        if (order.js !== false && order.js.length > 0){

							ESCPrint.sendText(
								deviceObj,
								PRINTER_SETTINGS,
								order.js
							);

							if (window.parent != window){
								window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: " + PRINTER_LANG_STRINGS.last_order_printed + order.id, 'printed');
							} else {
								document.querySelector('[data-lastid]').innerHTML = order.id;
							}

                        }

						updateSale(order.id);

					} catch (e){

						if (window.parent != window){
							window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: " + PRINTER_LANG_STRINGS.fail_connect, 'error');
						} else {
							console.error(PRINTER_SETTINGS.label + ": " + PRINTER_LANG_STRINGS.fail_connect);
						}
					}

				};

				// wait 30s and poll again
				window.setTimeout(pollForSales.bind(this, deviceObj), (PRINTER_SETTINGS.autoprint_interval * 1000));

			})
			.catch(function(error){

				if (window.parent != window){
					window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: " + PRINTER_LANG_STRINGS.fail_retrieve, 'printed');
				} else {

					if (confirm(PRINTER_SETTINGS.label + ": " + PRINTER_LANG_STRINGS.fail_retrieve)){
						pollForSales(deviceObj);
					}

				}

			});

		}

	    // usb
	    if (PRINTER_SETTINGS.type == 'usb'){

			(async function(){

				try {

					var devices = await ESCPrint.getDevices();
					if (devices.length){
						pollForSales(devices[0]);
					} else {

						if (window.parent != window){
							window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: " + PRINTER_LANG_STRINGS.fail_connect_retry, 'connect');
						} else {
							if (confirm(PRINTER_SETTINGS.label + ": " + PRINTER_LANG_STRINGS.fail_connect_retry)){
								location.reload();
							}
						}
					}

				} catch (e){

					if (window.parent != window){
						window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: " + PRINTER_LANG_STRINGS.fail_connect_retry, 'connect');
					} else {
						if (confirm(PRINTER_SETTINGS.label + ": " + PRINTER_LANG_STRINGS.fail_connect_retry)){
							location.reload();
						}
					}
				}

			}());

		// bluetooth
		} else if (PRINTER_SETTINGS.type == 'bluetooth'){

			(async function(){

				try {
					
					var devices = await ESCPrint.getDevicesBluetooth();
					
					for (const device of devices){
						
						var abortController = new AbortController();
							
						device.addEventListener('advertisementreceived', async (event) => {
							
							abortController.abort();
							
							pollForSales(event.device);
							
						});
						
						await device.watchAdvertisements({ signal: abortController.signal });
						return;
						
					}					

					throw new Error('no device found');

				} catch (e){

					if (window.parent != window){
						window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: " + PRINTER_LANG_STRINGS.fail_connect_retry, 'connect');
					} else {
						if (confirm(PRINTER_SETTINGS.label + ": " + PRINTER_LANG_STRINGS.fail_connect_retry)){
							location.reload();
						}
					}
				}

			}());

		// ip
		} else if (PRINTER_SETTINGS.type == 'ip'){

			// Create WebSocket connection.
			let socket = new WebSocket((PRINTER_SETTINGS.ssl == 1 ? 'wss://' : 'ws://') + PRINTER_SETTINGS.ip_address + ':' + PRINTER_SETTINGS.port);

			socket.onclose = function (event) { };

			socket.onerror = function (event) {

				if (window.parent != window){
					window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: " + PRINTER_LANG_STRINGS.fail_connect, 'error');
				} else {
					console.error(PRINTER_SETTINGS.label + ": " + PRINTER_LANG_STRINGS.fail_connect);
				}

			};

			// Connection opened
			socket.onopen = function (event) {
				pollForSales(socket);
			};

	    }

	// epson
    } else {

    	var PRINT_QUEUE = [];

		// get new sales from server
    	var pollForSales = function(deviceObj){

		    fetch(
		    	location.href + '&getSales=1',
		    	{
			    	method: 'GET'
				}
			)
			.then(function(response){
				return response.json();
			})
			.then(function(response){

                if (response.length < 1){
				    window.setTimeout(pollForSales.bind(this, deviceObj), (PRINTER_SETTINGS.autoprint_interval * 1000));
                    return;
                }

                var initPrinting = PRINT_QUEUE.length == 0;
				for (i=0; i<response.length; i++){

					var order = response[i];
                    if (order.js !== false)
                        PRINT_QUEUE.push(order);

				};

                if (initPrinting)
                    printFromQueue(deviceObj);

			})
			.catch(function(error){

				if (window.parent != window){
					window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: " + PRINTER_LANG_STRINGS.fail_retrieve, 'error');
				} else {
					console.error(PRINTER_SETTINGS.label + ": " + PRINTER_LANG_STRINGS.fail_retrieve);
				}

				pollForSales(deviceObj);

			});

		};

		// print the next item in the queue
        var printFromQueue = function(deviceObj){

            if (PRINT_QUEUE.length < 1) {
				window.setTimeout(pollForSales.bind(this, deviceObj), (PRINTER_SETTINGS.autoprint_interval * 1000));
                return;
            }

            var order = PRINT_QUEUE[0];
			for (j=0; j<PRINTER_SETTINGS.copies; j++){

				deviceObj.addFeedLine(PRINTER_SETTINGS.lines_before);
				order.js.forEach(function(js){
					eval(js); // nasty but we control it
				});
				deviceObj.addFeedLine(PRINTER_SETTINGS.lines_after);

				if (PRINTER_SETTINGS.autocut){
					deviceObj.addCut();
				}

				deviceObj.send();

			}

			if (window.parent != window){
				window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: " + PRINTER_LANG_STRINGS.last_order_printed + order.id, 'printed');
			} else {
				document.querySelector('[data-lastid]').innerHTML = order.id;
			}

        }

		var ePosDev = new epson.ePOSDevice();
		ePosDev.connect(
			PRINTER_SETTINGS.ip_address,
			PRINTER_SETTINGS.port,
			function(connectionResult){

				var deviceId = PRINTER_SETTINGS.device_name;
				var options = {'crypto': true, 'buffer': false};

				if ((connectionResult == 'OK') || (connectionResult == 'SSL_CONNECT_OK')){

					//Retrieves the Printer object
					ePosDev.createDevice(
						deviceId,
						ePosDev.DEVICE_TYPE_PRINTER,
						options,
						function(deviceObj, errorCode){

							// no device
							if (deviceObj === null){
								if (window.parent != window){
									window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: " + PRINTER_LANG_STRINGS.fail_connect, 'connect');
								} else {
									if (confirm(PRINTER_SETTINGS.label + ": " + PRINTER_LANG_STRINGS.fail_connect)){
										location.reload();
									}
								}
							}

							if (errorCode == 'OK'){

								deviceObj.onerror = function(error){
									deviceObj = null;

									if (window.parent != window){
										window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: " + PRINTER_LANG_STRINGS.fail_try, 'error');
									} else {
										console.error(PRINTER_SETTINGS.label + ": " + error.message + ". " + PRINTER_LANG_STRINGS.fail_try);
									}

                                    printFromQueue(this);

								}

                                deviceObj.onreceive = function(response){

                                    // remove order from queue
                                    // if we fail then we'll pick it up on the next ajax call as status wont have been changed
                                    var order = PRINT_QUEUE.shift();
                                    if (response.success)
					                    updateSale(order.id);

                                    printFromQueue(this);

                                }

								window.addEventListener('beforeUnload', function(){
									ePosDev.disconnect();
								});

								pollForSales(deviceObj);

						    }

						}
					);

				} else {
					if (window.parent != window){
						window.parent.app.thoughtco_printer.updateStatusBar("<strong>" + PRINTER_SETTINGS.label + "</strong>: " + PRINTER_LANG_STRINGS.fail_connect, 'connect');
					} else {
						if (confirm(PRINTER_SETTINGS.label + ": " + PRINTER_LANG_STRINGS.fail_connect)){
							location.reload();
						}
					}
				}

			}
		);

	}

})();
