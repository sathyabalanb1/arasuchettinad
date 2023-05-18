@if (count($field->value))
    @foreach ($field->value as $key => $value)
        <div class="card border-0">
            <div class="card-body">
                <h5 class="card-title">{{ $key }}</h5>
                @foreach ($value as $variable)
                    <span
                        class="label label-pill label-primary"
                        title="{{ $variable['name'] }}"
                    >{{ $variable['var'] }}</span>
                @endforeach
            </div>
        </div>
    @endforeach
@endif
