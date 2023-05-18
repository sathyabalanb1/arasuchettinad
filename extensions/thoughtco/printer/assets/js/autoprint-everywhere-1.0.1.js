
// add iframe for each printer
app.thoughtco_printer.forEach((printer) => {
	
	var iframe = document.createElement('iframe');
	iframe.style.display = "none";
	iframe.src = app.thoughtco_printer_base + printer;
	document.body.appendChild(iframe);
	
});

// add bottom bar for status and errors
var printerBar = document.createElement('div');
printerBar.className = 'thoughtco_printerbar';
printerBar.innerHTML = `
<p><i class="fa fa-print"></i><span class="printer-status">Idle</span></p>
`;
document.body.appendChild(printerBar);

// function to update status bar
app.thoughtco_printer.updateStatusBar = function(error, type){
	printerBar.setAttribute('data-status', type);
	printerBar.querySelector('.printer-status').innerHTML = error;
}
