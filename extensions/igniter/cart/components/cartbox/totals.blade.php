@if ($cart->count())
    <div class="cart-total">
        <div class="table-responsive">
            <table class="table table-none">
                <tbody>

                <tr>
                    <td>
                    <span class="text-muted">
                        @lang('igniter.cart::default.text_sub_total'):
                   </span>
                    </td>
                    <td class="text-right">
                        {{ currency_format($cart->subtotal()) }}
                    </td>
                </tr>
                    <?php
                    $cartValue = $cart->conditions();
                    $orderValue = $cart->subtotal();
                    $str_cnt = 0;
                    if(isset($cartValue['delivery'])) {
                        $orderValue += $cartValue['delivery']->calculatedValue;
                    }
                    if(isset($cartValue['coupon'])){
                        if($cartValue['coupon']->calculatedValue > $orderValue){
                            $orderValue = 0.00;
                        }
                        else{
                            $orderValue -= number_format($cartValue['coupon']->calculatedValue, 2);
                        }
                    }
                    $orderValueTax = $orderValue;
                    if($orderValue != 0.00){
                        if (isset($cartValue['cgsttax'])) {
                            $orderValue += number_format($cartValue['cgsttax']->calculatedValue, 2);
                        }
                        if (isset($cartValue['sgsttax'])) {
                            $orderValue += number_format($cartValue['sgsttax']->calculatedValue, 2);
                        }
                        if (isset($cartValue['tax'])) {
                            $orderValue += number_format($cartValue['tax']->calculatedValue, 2);
                        }
                    }
                    ?>
                @foreach ($cartValue as $id => $condition)
                    <tr>
                        <td>
                        <span class="text-muted">
                            @php
                                if($condition->getLabel() != 'Delivery'){
                                    if(str_word_count($condition->getLabel())>1){
                                        $str_cnt = 1;
                                    }
                                }
                            @endphp
                            {{ $condition->getLabel() }}:
                            @if ($condition->removeable)
                                <button
                                    type="button"
                                    class="btn btn-sm"
                                    data-request="{{ $removeConditionEventHandler }}"
                                    data-request-data="conditionId: '{{ $id }}'"
                                    data-replace-loading="fa fa-spinner fa-spin"
                                ><i class="fa fa-times"></i></button>
                            @endif
                       </span>
                        </td>
                        <td class="text-right">

                            {{ is_numeric($result = $condition->getValue()) ? currency_format($result) : $result }}
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <td>
                    <span class="text-muted">
                        @lang('igniter.cart::default.text_order_total'):
                   </span>
                    </td>
                    <td class="text-right">
                        @php
                            if($str_cnt == 1){
                               if(isset($cartValue['tip'])){
                                   $orderValueTax += number_format($cartValue['tip']->calculatedValue, 2);
                               }
                               echo currency_format($orderValueTax);
                            }
                            else{
                                if(isset($cartValue['tip'])){
                                   $orderValue += number_format($cartValue['tip']->calculatedValue, 2);
                                }
                                echo currency_format($orderValue);
                            }
                        @endphp
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
@endif
