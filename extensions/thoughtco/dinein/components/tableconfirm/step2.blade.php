    @isset($redirect)
        <script>location.href = '{{ $redirect }}';</script>
    @endisset
    <div class="card mb-1">
        <div class="card-body text-center">

            <form method="post" class="d-flex justify-content-center">

                <input type="hidden" name="table" value="{{ $table }}" />
                <input type="hidden" name="confirm" value="1" />
                <div class="col-3">


                    <div class="label label-light mb-3">
                        <span class="h6">
                            <i class="fa fa-exclamation"></i>&nbsp;
                            @lang('thoughtco.dinein::default.choose_table.text_allergies')
                        </span>
                    </div>

                    <h4>@lang('thoughtco.dinein::default.choose_table.text_allergies_more')</h4>

                    <button class="btn btn-primary mt-2" type="submit">@lang('thoughtco.dinein::default.choose_table.btn_continue')</button>

                </div>

            </form>

        </div>
    </div>
