@php
    $printerList = array_get($button->config, 'printerList');
@endphp
<div
    class="btn-group"
    data-control="form-save-actions"
>
    <button
        type="button"
        tabindex="0"
        {!! $button->getAttributes() !!}
    >{!! $button->label ?: $button->name !!}</button>
    <button
        type="button"
        class="{{ $button->cssClass }} dropdown-toggle dropdown-toggle-split"
        data-bs-toggle="dropdown"
        data-display="static"
        aria-haspopup="true"
        aria-expanded="false"
    ><span class="sr-only">Toggle Dropdown</span></button>
    <div class="dropdown-menu dropdown-menu-left">
        @foreach ($printerList as $printInfo)
            <div class="dropdown-item px-2" >
                <div class="custom-control">
                    <a href="{{ admin_url($print_url.'?printer='.$printInfo->id) }}"> <label
                            for="toolbar-button-save-action-{{$printInfo->label}}"
                        >{{$printInfo->label}}</label>
                    </a>
                </div>
            </div>

        @endforeach
    </div>
</div>
