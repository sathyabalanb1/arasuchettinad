@if ($order->isDineinType() OR $order->isWaiterServiceType())

    @if (count($tables))
        <div class="row @if($hideField) d-none @endif">
            <div class="col-sm-8">
                <div class="form-group">
                    <label for="fld-tables">@lang('thoughtco.dinein::default.label_table_number')</label><br>
                    <div class="input-group">
                        <select
                            class="form-control"
                            name="table_number"
                        >
			 <option value="" selected>Select the Table</option>
                            @foreach ($tables as $table_id => $table)
                                <option value="{{ $table_id }}"@if ($selectedTable == $table_id) selected @endif>{{ $table }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @isset($autofillData)
    <script type="text/javascript">
    @if ($autofillData['first_name'] != '') document.querySelector('[name="first_name"]').value = '{{ $autofillData['first_name'] }}'; @endif
    @if ($autofillData['last_name'] != '') document.querySelector('[name="last_name"]').value = '{{ $autofillData['last_name'] }}'; @endif
    @if ($autofillData['email'] != '') document.querySelector('[name="email"]').value = '{{ $autofillData['email'] }}'; @endif
    @if ($autofillData['telephone'] != '') document.querySelector('[name="telephone"]').value = '{{ $autofillData['telephone'] }}'; @endif
    </script>
    @endisset

@endif
