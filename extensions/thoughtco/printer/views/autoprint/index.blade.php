<div class="row-fluid">
    <div class="page-x-spacer">

	    <h1>@lang('thoughtco.printer::default.autoprint')</h1>
	    <p><@lang('thoughtco.printer::default.instructions')</p>

	    <p><br /><strong>@lang('thoughtco.printer::default.last_order_printed')</strong> <span data-lastid></span></p>

	    {!! $this->renderAutoprint(); !!}

    </div>
</div>
