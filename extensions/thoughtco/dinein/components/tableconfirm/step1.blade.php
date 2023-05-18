    <div class="card mb-1">
        <div class="card-body text-center">

            <div class="label label-light mb-3">
                <span class="h6">
                    <i class="fa fa-question-circle"></i>&nbsp;
                    {{ $table ? $table->table_name : '' }}
                </span>
            </div>

            @isset($table)
            <form id="dinein-show-selected-table" method="post">
                <input type="hidden" name="table" value="{{ $table->table_id ?? '' }}" />
                <h3>@lang('thoughtco.dinein::default.choose_table.text_title', ['table' => $table ? $table->table_name : ''])</h3>
                <div class="mt-4">
                    <button class="btn btn-primary" type="submit">@lang('thoughtco.dinein::default.choose_table.btn_yes')</button>
                </div>
                <div class="mt-2">
                    <button class="btn btn-secondary" type="button" id="dinein-show-table-selector">@lang('thoughtco.dinein::default.choose_table.btn_no')</button>
                </div>
            </form>
            @endisset

            <form class="@isset($table)d-none @endisset" method="post" id="dinein-table-selector">
                <div class="d-flex justify-content-center">
                    <div class="col-3">
                        <h3>@lang('thoughtco.dinein::default.choose_table.text_select_table')</h3>
                        <div class="form-row my-4">
                            <select name="table" class="form-control">
                                @foreach ($tables as $table_row)
                                <option value="{{ $table_row->table_id }}" @if(isset($table) AND $table_row->table_id == $table->table_id) selected @endif>{{ $table_row->table_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-primary" type="submit">@lang('thoughtco.dinein::default.choose_table.btn_select')</button>
                    </div>
                </div>
            </form>

            <script>
            document.getElementById('dinein-show-table-selector')
            .addEventListener('click', function(){
                document.getElementById('dinein-table-selector').classList.remove('d-none');
                document.getElementById('dinein-show-selected-table').classList.add('d-none');
            });
            </script>

        </div>
    </div>
