# Printer

This extension enables printing of orders to thermal printers from TastyIgniter. It supports USB (ESC/POS), IP/Network Printing (ESC/POS), and Epson ePOS network web service. You can add multiple printers per location and can limit what is printed to each device by category selection.


## Usage

After installation a 'print docket' button will be added to the order view. There will also be the ability to add printers and dockets in Tools -> Printers. Dockets can be created and added to printers with restrictions on what categories it prints and in what contexts it prints out.

The extension also enables autoprinting of new orders as they arrive either through a dedicated autoprint page, or optionally anywhere in the admin panel.

## Configuration

The extension supports printers connected by:
1. USB
2. Network or IP
3. The Epson ePOS network web service

To start, plug in the printer, connect to your computer or tablet and follow the manufacturer's setup instructions.


### USB connection

This mode is only supported on browsers that support [WebUSB](https://caniuse.com/#feat=webusb) and on connections running over HTTPS. Make sure the browser you are using supports WebUSB and the installation is behind a secure certificate. 

On the add printer page, select "USB" as the type, and then choose "Setup USB device", select the device from the list and choose connect. When a printer is successfully added a notification will appear to let you know it has been added. Save your printer settings.

#### Usage on Windows devices

If you are on a Windows device you must force the system to use the WinUSB driver for the device. 

The easiest way to do this is to install [Zadig](https://zadig.akeo.ie), select the device, choose WinUSB as the driver and select reinstall.


### IP/Ethernet connection

Your printer should be connected to the same network as the computer managing the orders.

IP printers require the [proxy application](https://github.com/thoughtco/ti-print-proxy/releases) to be running so that websocket connections are converted to TCP packets and sent to your printer. Please install and run this program when you want to enable IP printing.

On the add printer screen in TastyIgniter choose type as IP/Network and enter the IP address of ***the computer running the proxy application*** (note: this can be a local IP such as 192.168.0.1). Enter the port you want the proxy application to listen for connections on.

In the proxy application, enter the same listening port as above, and also enter ***the printer's IP address*** (note: this can be a local IP) and the port it is listening on (usually 9100, but check with your hardware manufacturer for support). Start the proxy application - it must always be running for the extension to work.

If you are using multiple printers you may be interested [this command line application](https://github.com/BreakSecurity/ti-printers-proxy)


#### SSL warning

If your TastyIgniter instance is running over SSL you will need to add a self-signed SSL certificate to the proxy application, and to the trust store on the computer making the connection. Without this, the browser will display an insecure content warning.

Ensure your key is NOT encrypted and has no password associated with it, or the proxy application will not work.

The following command creates a self-signed certicate and key for localhost for 365 days. If you use it please ensure you do not enter a passphrase on the private key.

```
openssl req -x509 -days 365 -out localhost.crt -keyout localhost.key \
  -newkey rsa:2048 -nodes -sha256 \
  -subj '/CN=localhost' -extensions EXT -config <( \
   printf "[dn]\nCN=localhost\n[req]\ndistinguished_name = dn\n[EXT]\nsubjectAltName=DNS:localhost\nkeyUsage=digitalSignature\nextendedKeyUsage=serverAuth")
```

Alternatively use [this website](https://certificatetools.com) and ensure you choose the self-signed CSR option, before downloading the private key and PEM certificates that are generated.

Enter the certificate and key into the appropriate fields in the proxy application and start the server.

Now you will need to install the certificate to your browser/computer trust store. On a mac, simply double click on the certificate add add it to Keychain.

On Windows Edge: Export the cert and click on it. Click install certificate. Click “Place all certificates in the following store”, and then click “Browse”. Inside the dialog box, click “Trusted Root Certification Authorities”, and then click “OK”.

In Windows Chrome: Click on Settings -> Advanced settings -> More -> Manage certificates Click servers -> import -> select the certificate you exported.

If you are using Firefox, [follow instructions here](https://www.starnet.com/xwin32kb/installing-a-self-signed-certificate-on-firefox/)


### EPSON ePOS network connection

Some recent EPSON printers come bundled with an ePOS SDK for JavaScript allowing connections from a browser ([see here for information and hardware support](https://download.epson-biz.com/modules/pos/index.php?page=soft&scat=57)). 

If your device is supported, once you plug the printer in for the first time, it should print a docket with its IP address and some other details on it. Keep this as you’ll need the information later in the setup.

#### Utility software and settings

Then download and install the appropriate utility software for the model (e.g. TM20-III), or use the web interface provided on the printer.

If using the utility software and the printer doesnt appear, select “add port”. Within the new window select “Ethernet” and then search. Your printer should appear in the list, in which case select it and press “OK”. If not, enter the IP address on the docket into the boxes and select OK. The printer should appears in the original screen - select it and press “OK”, and a new window will load with the printer's settings.

Select Network > Basic Settings -> IP Address (Configuration -> Network -> TCP/IP in the web interface). Set to manual, using the same IP address on the docket. This is in order to permanently set the IP address to be the one on the print out, so that it doesn’t change and render the SSL certificate invalid.

Go to Network > Detailed Settings > ePOS-Print (Configuration -> ePOS-Print in the web interface). Enable ePOS print and make a note of the printer name (usually local_printer). If your see an ePOS-Device option, please also enable it.

##### Enabling SSL connections

Go to Network -> Detailed Settings > Certificate and click on self signed cert (Configuration -> Authentication -> Certificate List -> Create on the web interface). In the modal that opens enter the IP address for the common name. Set a validity period of 3 years. Click OK, then Set on the window you are returned to.

Browse to the IP address in Chrome or Edge. You will be given an insecure warning so click proceed any way. Click on the not secure warning in the address bar, click on certificate details and export.

Now you will need to install the certificate to your browser/computer trust store. On a mac, simply double click on the certificate add add it to Keychain.

On Windows Edge: Export the cert and click on it. Click install certificate. Click “Place all certificates in the following store”, and then click “Browse”. Inside the dialog box, click “Trusted Root Certification Authorities”, and then click “OK”.

In Windows Chrome: Click on Settings -> Advanced settings -> More -> Manage certificates Click servers -> import -> select the certificate you exported.

If you are using Firefox, [follow instructions here](https://www.starnet.com/xwin32kb/installing-a-self-signed-certificate-on-firefox/)

Browse to the IP address to ensure it has picked up the new certificate.

#### System settings

In the web app to go Tools -> Printer and click on add to add a printer. Add the IP address from the receipt slip, the port should be 8008 for non SSL connections and 8043 for SSL connections and the device name should be local_printer (unless you changed it in the configuration).

Go to an order and click “print docket”. The order will print and automatically be marked as complete.



### Receipt format

The receipts are formatted in a markdown-like format and converted to the corresponding code for the printer you select.

> Headings 1-6 (# - #####) are aligned center (unless a previous alignment command has been found). Font sizes and spacing can be configured for these styles.

> Paragraphs are aligned left (unless a previous alignment command has been found). Font sizes and spacing can be configured for by amending the 'default' style.

> Lines beginning ***** are assumed to be horizontal rules and a center aligned series of dashes is printed. Font size and spacing can be configured for by amending the 'default' style.

> Lines beginning >>>>> are assumed to be a request for a cut in the page

> Lines in the format [img x,y] allow you to print an image stored on the printer. If x and y are 32 or higher, it will assume a key code, for example [img 48,42] will print keycode 48, 42. If less than 32, then it will print the image found at storage space x (starting at 1), and y determines the width and height (0 is no adjustment, 1 is double width, 2 is double height, 3 is double with and height).Images will be centered (unless a previous alignment command has been found)

> Lines in the format [qrcode 3,https://yoururl.com] allow you to print an QR code with a link to the url. The first parameter is a size between 1 and 5, with 1 being the smallest. The QR will be centered (unless a previous alignment command has been found)

> Blank lines are treated as a feed line request

> Lines beginning <| will begin left alignment, all lines will be affected until a new alignment command is found

> Lines beginning |> will begin right alignment, all lines will be affected until a new alignment command is found

> Lines beginning || will begin center alignment, all lines will be affected until a new alignment command is found



### Sample receipts

Sample receipts are added on install. If you need to view these for any reason you can find them within `dockets` in the extension directory.


### Known limitations

When you have multiple tabs open simultenously while autoprinting or autoprinting everywhere the extension will print multiple copies of your dockets.

### Events

#### orderData
Listen for this event to add to or amend the data array that is passed to the printer.

```
Event::listen('thoughtco.printer.orderData', function($orderModel, &$data) {
    // ...
    // augment $data
});
```

### PrintHelper Facade

This extension provides a Facade to make it possible to print from other extensions / controllers.

Example of use:

```php
<?php

use PrintHelper;
use Thoughtco\Printer\Models\Printer;

$output = '';
foreach (PrintHelper::getJavascript() as $jsfile)
   $output .= '<script type="text/javascript" src="'.config('app.url').$jsfile.'"></script>';

$output .= '<script>'.PrintHelper::print(Printer::find(1), [], "## This is a test print\r\n### It worked!").'</script>';

echo $output;

```

