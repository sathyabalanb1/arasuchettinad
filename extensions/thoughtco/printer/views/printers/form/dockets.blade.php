<div class="d-flex align-items-center">
    <div class="px-2">
        <p class="card-title font-weight-bold mb-1">{{ $item->option_name }} / <span class="text-muted">{{ $item->option_context }}</span></p>
        @foreach ($item->option_categories as $cat)
            <span class="badge border">{{ $cat }}</span>
        @endforeach
    </div>
</div>
