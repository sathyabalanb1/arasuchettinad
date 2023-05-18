// reference: http://px-download.s3.amazonaws.com/SDK/ESCPOS_Command_Manual.pdf
// https://www.epson-biz.com/modules/ref_escpos/index.php
ESCPrint = {

	constants: {
		ESC: 0x1b,
		FS: 0x1c,
		GS: 0x1d,
		LF: 0x0a,
	},

	_stringToBytes: function(encoder, str){
		str = [].slice.call(encoder.encode(str));
		return str;
	},

	alignmentStyle: function(style){

		let v = 0x00;

		if (style == 'center') {
			v = 0x01
		}

		if (style == 'right') {
			v = 0x02
		}

		return [ESCPrint.constants.ESC, 0x61, v];

	},

	characterStyle: function(style){

		let v = 0;

		if (style.smallFont) {
			v |= 1 << 0;
		}

		if (style.normalFont) {
			v |= 1 << 1;
		}

		if (style.normalFontAlternate) {
			v |= 1 << 2;
		}

		if (style.emphasized) {
			v |= 1 << 3;
		}

		if (style.doubleHeight) {
			v |= 1 << 4;
		}

		if (style.doubleWidth) {
			v |= 1 << 5;
		}

		if (style.six) {
			v |= 1 << 6;
		}

		if (style.underline) {
			v |= 1 << 7;
		}

		return [ESCPrint.constants.ESC, 0x21, v];

	},

	claimInterface: async function(device){

		if (device.opened === false){
			await device.open();
			if (device.configuration === null){
				await device.selectConfiguration(1);
			}
		}

		for (const config of device.configurations) {
	    	for (const iface of config.interfaces) {
				if (!iface.claimed) {
	        		await device.claimInterface(iface.interfaceNumber);
					return true;
	      		}
	    	}
	  	}

	  	return false;
	},

	getDevices: async function(){
		const devices = await navigator.usb.getDevices();

        var filteredDevices = [];
        for (var i = 0; i < devices.length; ++i){
            if (devices[i].usbVersionMajor > 0){
                filteredDevices.push(devices[i]);
            }
        }

		return filteredDevices;
	},

	getDevicesBluetooth: async function(){
		return await navigator.bluetooth.getDevices();
	},

	requestPermission: function(){

		navigator.usb
		.requestDevice({
			filters: []
		})
		.then(async(device) => {
			await ESCPrint.claimInterface(device);
			ESCPrint.sendText(device, { copies: 1, lines_after: 10, lines_before: 4, autocut: 0, }, 'Success!');
		});

	},

	requestPermissionBluetooth: function(){
		navigator.bluetooth.requestDevice({
          //filters: [{
          //  services: ['000018f0-0000-1000-8000-00805f9b34fb']
          //}]
		  acceptAllDevices: true,
        })
        .then(device => device.gatt.connect())
		.then(server => server.getPrimaryService("000018f0-0000-1000-8000-00805f9b34fb"))
        .then(service => service.getCharacteristic("00002af1-0000-1000-8000-00805f9b34fb"))
		.then(async(characteristic) => {
			await ESCPrint.sendText(characteristic, { copies: 1, lines_after: 10, lines_before: 4, autocut: 0, }, 'Success!');
			characteristic.service.device.gatt.disconnect()
		});
	},

	selectEndpoint: function(device, direction){

		const endpoint = device.configuration
		.interfaces[0]
		.alternate
		.endpoints.find(ep => ep.direction == direction);

		if (endpoint == null)
			throw new Error(`Endpoint ${direction} not found in device interface.`);

		return endpoint
	},

	sendText: async function(device, settings, str){

		var endpoint;

		// usb
		if (device.transferOut){
			await ESCPrint.claimInterface(device);
			endpoint = ESCPrint.selectEndpoint(device, 'out');
		}

		// bluetooth
		//if (device.gatt){
		//	device = await device.gatt.connect();
		//	device = await device.getPrimaryService("000018f0-0000-1000-8000-00805f9b34fb");
		//	device = await device.getCharacteristic("00002af1-0000-1000-8000-00805f9b34fb");
		//}

		let receiptArray = [];

        // codepage
        if (settings.codepage === undefined || !settings.codepage){
            settings.codepage = 16;
        }

        if (settings.encoding === undefined || !settings.encoding){
            settings.encoding = 'windows-1252';
        }

		// reset buffer
		receiptArray.push(ESCPrint.constants.ESC, 0x40);

		// set codepage
		receiptArray.push(ESCPrint.constants.ESC, 0x74, settings.codepage);

		// encoder
		let encoder = new TextEncoder(settings.encoding, { NONSTANDARD_allowLegacyEncoding: true });

		// lines before
		for (var i=0; i<settings.lines_before; i++){
			receiptArray.push(ESCPrint.constants.LF);
		}

		if (!settings.characters_per_line){
			settings.characters_per_line = 48;
		}

		// justify left
		receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));

		// set default font style
		receiptArray = receiptArray.concat(ESCPrint.characterStyle({ normalFont: true }));

		// turn on text smoothing
		receiptArray.push(ESCPrint.constants.ESC, 0x62, 1);

        // set default line spacing
        // https://www.epson-biz.com/modules/ref_escpos/index.php?content_id=20
		receiptArray.push(ESCPrint.constants.ESC, 0x33, settings.font ? settings.font.default_line : 30);

		str = str.split("\n");

		// have we found an alignment command?
		let foundAlignment = '';
		let lastFontSize = 'p';
		let spaceScalingFactor = 1.5;
		let horizontalSize = settings.font ? settings.font.default_horizontal : 1;

		for (var i=0; i<str.length; i++){

			var o = str[i].trim();

			// alignments
			if (o.indexOf('|>') == 0 || o.indexOf('<|') == 0 || o.indexOf('||') == 0){

				// right align
				if (o.indexOf('|>') == 0){
					foundAlignment = 'right';
				} else if (o.indexOf('||') == 0){
					foundAlignment = 'center';
				} else {
					foundAlignment = 'left';
				}

				receiptArray = receiptArray.concat(ESCPrint.alignmentStyle(foundAlignment));

				o = o.substr(2);
			}

			// h6
			if (o.indexOf('######') == 0){

				o = o.replace('###### ', '').trim();

				// justify center
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));

				if (lastFontSize != 'h6') {
					receiptArray.push(ESCPrint.constants.ESC, 0x45, settings.font ? (settings.font.heading6_bold == 1 ? 1 : 0) : 1);
					receiptArray = receiptArray.concat(ESCPrint.textSize(settings.font ? settings.font.heading6_vertical : 1, settings.font ? settings.font.heading6_horizontal : 1));
		        	receiptArray.push(ESCPrint.constants.ESC, 0x33, Math.floor(spaceScalingFactor * (settings.font ? settings.font.heading6_line : 30)));
					lastFontSize = 'h6';
				}

				// output string
				receiptArray = receiptArray.concat(ESCPrint._stringToBytes(encoder, o));

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

				// justify left
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));

			// h5
			} else if (o.indexOf('#####') == 0){

				// get string after #
				o = o.replace('#####', '').trim();

				if (lastFontSize != 'h5') {
					receiptArray.push(ESCPrint.constants.ESC, 0x45, settings.font ? (settings.font.heading5_bold == 1 ? 1 : 0) : 1);
					receiptArray = receiptArray.concat(ESCPrint.textSize(settings.font ? settings.font.heading5_vertical : 1, settings.font ? settings.font.heading5_horizontal : 1));
		        	receiptArray.push(ESCPrint.constants.ESC, 0x33, Math.floor(spaceScalingFactor * (settings.font ? settings.font.heading5_line : 30)));
					lastFontSize = 'h5';
				}

				// justify center
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));

				// output string
				receiptArray = receiptArray.concat(ESCPrint._stringToBytes(encoder, o));

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

				// justify left
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));

			// h4
			} else if (o.indexOf('####') == 0){

				// get string after #
				o = o.replace('####', '').trim();

				if (lastFontSize != 'h4') {
					receiptArray.push(ESCPrint.constants.ESC, 0x45, settings.font ? (settings.font.heading4_bold == 1 ? 1 : 0) : 1);
					receiptArray = receiptArray.concat(ESCPrint.textSize(settings.font ? settings.font.heading4_vertical : 1, settings.font ? settings.font.heading4_horizontal : 1));
		        	receiptArray.push(ESCPrint.constants.ESC, 0x33, Math.floor(spaceScalingFactor * (settings.font ? settings.font.heading4_line : 30)));
					lastFontSize = 'h4';
				}

				// justify center
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));

				// output string
				receiptArray = receiptArray.concat(ESCPrint._stringToBytes(encoder, o));

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

				// justify left
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));

			// h3
			} else if (o.indexOf('###') == 0){

				// get string after #
				o = o.replace('###', '').trim();

				if (lastFontSize != 'h3') {
					receiptArray.push(ESCPrint.constants.ESC, 0x45, settings.font ? (settings.font.heading3_bold == 1 ? 1 : 0) : 1);
					receiptArray = receiptArray.concat(ESCPrint.textSize(settings.font ? settings.font.heading3_vertical : 2, settings.font ? settings.font.heading3_horizontal : 1));
		        	receiptArray.push(ESCPrint.constants.ESC, 0x33, Math.floor(spaceScalingFactor * (settings.font ? settings.font.heading3_line : 68)));
					lastFontSize = 'h3';
				}

				// justify center
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));

				// output string
				receiptArray = receiptArray.concat(ESCPrint._stringToBytes(encoder, o));

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

				// justify left
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));


			// h2
			} else if (o.indexOf('##') == 0){

				// get string after #
				o = o.replace('##', '').trim();

				if (lastFontSize != 'h2') {
					receiptArray.push(ESCPrint.constants.ESC, 0x45, settings.font ? (settings.font.heading2_bold == 1 ? 1 : 0) : 1);
					receiptArray = receiptArray.concat(ESCPrint.textSize(settings.font ? settings.font.heading2_vertical : 3, settings.font ? settings.font.heading2_horizontal : 1));
		        	receiptArray.push(ESCPrint.constants.ESC, 0x33, Math.floor(spaceScalingFactor * (settings.font ? settings.font.heading2_line : 42)));
					lastFontSize = 'h2';
				}

				// justify center
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));

				// output string
				receiptArray = receiptArray.concat(ESCPrint._stringToBytes(encoder, o));

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

				// justify left
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));

			// h1
			} else if (o.indexOf('#') == 0){

				// get string after #
				o = o.replace('#', '').trim();

				if (lastFontSize != 'h1') {
					receiptArray.push(ESCPrint.constants.ESC, 0x45, settings.font ? (settings.font.heading1_bold == 1 ? 1 : 0) : 1);
					receiptArray = receiptArray.concat(ESCPrint.textSize(settings.font ? settings.font.heading1_vertical : 4, settings.font ? settings.font.heading1_horizontal : 1));
		        	receiptArray.push(ESCPrint.constants.ESC, 0x33, Math.floor(spaceScalingFactor * (settings.font ? settings.font.heading1_line : 48)));
					lastFontSize = 'h1';
				}

				// justify center
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));

				// add string
				receiptArray = receiptArray.concat(ESCPrint._stringToBytes(encoder, o));

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

				// justify left
				if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));

			// hr
			} else if (o.indexOf('*****') === 0 || o.indexOf('-----') === 0){

				//receiptArray.push(ESCPrint.constants.LF);

				// justify center
				//if (foundAlignment == '')
				receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));

				if (lastFontSize != 'p') {
					receiptArray.push(ESCPrint.constants.ESC, 0x45, settings.font ? (settings.font.default_bold == 1 ? 1 : 0) : 0);
					receiptArray = receiptArray.concat(ESCPrint.textSize(settings.font ? settings.font.default_vertical : 1, settings.font ? settings.font.default_horizontal : 1));
		        	receiptArray.push(ESCPrint.constants.ESC, 0x33, Math.floor(spaceScalingFactor * (settings.font ? settings.font.default_line : 30)));
					lastFontSize = 'p';
				}

				// add lines
				receiptArray = receiptArray.concat(ESCPrint._stringToBytes(encoder, '-'.repeat(Math.floor(settings.characters_per_line/horizontalSize))));

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

				// line feed
				//receiptArray.push(ESCPrint.constants.LF);

				// justify left
				//if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));
				if (foundAlignment != '')
					receiptArray = receiptArray.concat(ESCPrint.alignmentStyle(foundAlignment));
				else
					receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));

			// cut
			} else if (o.indexOf('>>>>>') === 0){

				receiptArray.push(ESCPrint.constants.GS);
				receiptArray.push(0x56); // cut
				receiptArray.push(0x42); // command 66 (feed to cut)
				receiptArray.push(0x0); // no extra feed

			// image keycode
			} else if (o.indexOf('[img') === 0){

				o = o.replace('[img', '').replace(']', '').trim().split(',');

				if (o.length == 2 && o[0] >= 32){

					if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));
					receiptArray.push(0x1D, 0x28, 0x4C, 0x06, 0x00, 0x30, 0x45, o[0].trim().toString(16), o[1].trim().toString(16), 0x01, 0x01);
					if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));

				} else {

					if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));
					receiptArray.push(0x1C, 0x70, o[0].trim().toString(16), o[1].trim().toString(16));
					if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));

				}

			// QRcode
			} else if (o.indexOf('[qrcode') === 0){

				o = o.replace('[qrcode', '').replace(']', '').trim().split(',');

				if (o.length == 2){

					if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('center'));

					let data = ESCPrint._stringToBytes(encoder, o[1]);
					let storeLen = data.length + 3;
					let pL = storeLen % 256;
					let pH = storeLen / 256;

					receiptArray.push(ESCPrint.constants.GS, 0x28, 0x6b, 0x04, 0x00, 0x31, 0x41, 0x32, 0x00); // https://www.epson-biz.com/modules/ref_escpos/index.php?content_id=140
					receiptArray.push(ESCPrint.constants.GS, 0x28, 0x6b, 0x03, 0x00, 0x31, 0x43, o[0].toString(16)); // https://www.epson-biz.com/modules/ref_escpos/index.php?content_id=141
					receiptArray.push(ESCPrint.constants.GS, 0x28, 0x6b, 0x03, 0x00, 0x31, 0x45, 0x30); // https://www.epson-biz.com/modules/ref_escpos/index.php?content_id=142
					receiptArray.push(ESCPrint.constants.GS, 0x28, 0x6b, pL, pH, 0x31, 0x50, 0x30);  // https://www.epson-biz.com/modules/ref_escpos/index.php?content_id=143
					receiptArray = receiptArray.concat(data);
					receiptArray.push(ESCPrint.constants.GS, 0x28, 0x6b, 0x03, 0x00, 0x31, 0x51, 0x30); // https://www.epson-biz.com/modules/ref_escpos/index.php?content_id=144
					if (foundAlignment == '') receiptArray = receiptArray.concat(ESCPrint.alignmentStyle('left'));

				}

			// new line
			} else if (o.trim() == ''){

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

			// standard text
			} else {

				if (lastFontSize != 'p') {
					receiptArray.push(ESCPrint.constants.ESC, 0x45, settings.font ? (settings.font.default_bold == 1 ? 1 : 0) : 0);
					receiptArray = receiptArray.concat(ESCPrint.textSize(settings.font ? settings.font.default_vertical : 1, settings.font ? settings.font.default_horizontal : 1));
		        	receiptArray.push(ESCPrint.constants.ESC, 0x33, Math.floor(spaceScalingFactor * (settings.font ? settings.font.default_line : 30)));
					lastFontSize = 'p';
				}

				// add string
				receiptArray = receiptArray.concat(ESCPrint._stringToBytes(encoder, o));

				// line feed
				receiptArray.push(ESCPrint.constants.LF);

			}

		};

		// lines after
		for (var i=0; i<settings.lines_after; i++){
			receiptArray.push(ESCPrint.constants.LF);
		}

		// if autocut after every
		if (settings.autocut != 2){

			receiptArray.push(ESCPrint.constants.GS);
			receiptArray.push(0x56); // cut
			receiptArray.push(0x42); // command 66 (feed to cut)
			receiptArray.push(0x0); // no extra feed

		}

		// # of copies
        let finalArray = [];
		for (var i=0; i<settings.copies; i++){
            finalArray = finalArray.concat(receiptArray);
        }

		// if autocut after every or last
		if (settings.autocut != 0){

			finalArray.push(ESCPrint.constants.GS);
			finalArray.push(0x56); // cut
			finalArray.push(0x42); // command 66 (feed to cut)
			finalArray.push(0x0); // no extra feed

		}

		const bytes = new Uint8Array(finalArray);

		// usb
		if (device.transferOut){
			response = await device.transferOut(endpoint.endpointNumber, bytes);

		// bluetooth
// 		} else if (device.writeValue){
//
// 			var MAX_DATA_SEND_SIZE = 200;
// 		    var chunkCount = Math.ceil(bytes.byteLength / MAX_DATA_SEND_SIZE);
// 		    var chunkTotal = chunkCount;
// 		    var index = 0;
// 		    var startTime = new Date();
//
// 		    var sendChunk = () => {
//
// 				if (!chunkCount)
// 		            return;
//
// 		        var chunk = bytes.slice(index, index + MAX_DATA_SEND_SIZE);
// 		        index += MAX_DATA_SEND_SIZE;
// 		        chunkCount--;
//
// 				device.writeValueWithResponse(chunk)
// 				.then(function(){
// 					sendChunk();
// 				});
//
// 		    }
//
// 			sendChunk();
//
// 			return;

		// ip
		} else {
			response = await device.send(bytes);
		}

		return response;

	},

	textSize: function(height, width){
		var c = (2 << 3) * (width - 1) + (height - 1);
		return [ESCPrint.constants.GS, 0x21, c];
	}

};
