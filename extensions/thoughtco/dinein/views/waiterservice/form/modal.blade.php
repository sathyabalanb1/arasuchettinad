<div
    id="waiter-modal"
    class="modal fade"
    tabindex="-1"
    role="dialog"
    aria-labelledby="#waiter-modal"
    aria-hidden="true"
>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4
                    class="modal-title"
                    data-modal-text="title"
                ></h4>
            </div>
            <form class="modal-body">
                <div class="form-group">
                    <label>@lang('thoughtco.dinein::default.label_table_count')</label>
                    <input data-modal-input="table_count" type="text" class="form-control" name="table_count" />
                </div>
            </form>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >@lang('admin::lang.button_close')</button>
                <button
                    type="button"
                    class="btn btn-primary"
                    data-request="onClose"
                    data-request-form="#waiter-modal form"
                >@lang('admin::lang.button_save')</button>
            </div>
        </div>
    </div>
</div>
<div
    id="dinein-payment-modal"
    class="modal fade"
    tabindex="-1"
    role="dialog"
    aria-labelledby="#dinein-payment-modal"
    aria-hidden="true"
>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4
                    class="modal-title"
                    data-modal-text="title"
                ></h4>
            </div>
            <form class="modal-body">
                <div class="form-group">
                    <label>@lang('thoughtco.dinein::default.label_choose_payment')</label>
                    <select data-modal-input="payment_mode" class="form-control" name="payment_mode" required="required">
                        <option value="">Select</option>
                        @foreach ($payments as $k => $arr)
                            <option value="{{ $k }}">{{ $arr[0] }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >@lang('admin::lang.button_close')</button>
                <button
                    type="submit"
                    class="btn btn-primary"
                    data-request="onWsPay"
                    data-request-form="#dinein-payment-modal form"
                >@lang('admin::lang.button_save')</button>
            </div>
        </div>
    </div>
</div>
