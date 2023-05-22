@php
	$printers = array_get($button->config, 'printerList');
	$model = array_get($button->config, 'model');
@endphp
@if (count($printers) <= 1)
    <a class="btn btn-secondary" href="{{ admin_url('thoughtco/printer/printdocket?sale='.$model->order_id) }}">@lang('thoughtco.printer::default.btn_print')</a>
@else
	<div class="btn-group">
		<div class="dropdown">
	  	<button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">@lang('thoughtco.printer::default.btn_print')</button>
	  	<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
	    	<a class="dropdown-item" href="{{ admin_url('thoughtco/printer/printdocket?sale='.$model->order_id) }}">@lang('thoughtco.printer::default.btn_print_all')</a>
			{!! $printers->map(function($printer) use ($model) {
				return '<a class="dropdown-item" href="'.admin_url('thoughtco/printer/printdocket?sale='.$model->order_id.'&printer='.$printer->id).'">'.$printer->label.'</a>';
			})->join(' ') !!}
	  	</div>
		</div>
	</div>
@endif
