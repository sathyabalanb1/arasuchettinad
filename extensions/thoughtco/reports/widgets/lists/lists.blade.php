	<div class="card-title">
		<h1 class="h4"><i class="stat-icon {{ $listIcon }}"></i> @lang($listLabel)</h1>
	</div>
	<div class="list-group list-group-flush">
    @foreach ($listItems as $item)
		<div class="list-group-item bg-transparent">
            <b>{{ $item->name }}</b> <em class="pull-right">
                @if(isset($item->value))
                    {{ $item->value }}
                @endif
                @if(isset($item->quantity))
                    {{ $item->quantity }}
                @endif
            </em>
		</div>
	@endforeach
	</div>
