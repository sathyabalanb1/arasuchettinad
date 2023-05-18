@unless ($this->previewMode)
@php
    $fieldOptions = $field->options();
    $checkedValues = (array)$field->value;
@endphp
    <div
        id="{{ $this->getId() }}"
        class="control-recordeditor"
        data-control="record-editor"
        data-alias="{{ $this->alias }}"
    >
        <div
            class="input-group" data-toggle="modal"
            data-target="#{{ $this->getId('form-modal') }}"
        >
            @if ($addonLeft)
                <div class="input-group-prepend">{{ $addonLeft }}</div>
            @endif
            <select
                id="{{ $field->getId() }}"
                name="{{ $field->getName() }}"
                class="form-control"
                data-control="choose-record"
                {!! $field->getAttributes() !!}
            >
                @if ($fieldPlaceholder = $field->placeholder ?: $this->emptyOption)
                    <option value="0">@lang($fieldPlaceholder)</option>
                @endif
                @foreach ($fieldOptions as $value => $option)
                    @php if (!is_array($option)) $option = [$option] @endphp
                    <option
                        {!! $value == $field->value ? 'selected="selected"' : '' !!}
                        @isset($option[1]) data-{{ strpos($option[1], '.') ? 'image' : 'icon' }}="{{ $option[1] }}" @endisset
                        value="{{ $value }}"
                    >{{ is_lang_key($option[0]) ? lang($option[0]) : $option[0] }}</option>
                @endforeach
            </select>
            <div class="input-group-append ml-1">
                @if ($addonRight)
                    {!! $addonRight !!}
                @endif
            </div>
        </div>
    </div>
@endunless
