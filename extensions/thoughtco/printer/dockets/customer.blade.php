@php
    /* Config params */
    $showLocalInfo = false;
    $showOrderInfo = true;
    $showCustomerInfo = true;
    $showAddress = true;
    $showComment = true;
    $showQRCode = false;
    $showPaymentInfo= true;
    $showIssueMessage = true;

    /* Strings - General  */
    $stringTelephone = "Telephone no:";
    $stringReceiptTitle = "CUSTOMER COPY";
    $stringOrderNumber = "Bill No.:";
    $stringOrderFor = "Order for:";
    $stringName = "Name:";
    $stringCustomerPhone = "Phone number:";
    $stringDeliveryAddress = "Delivery address:";
    $stringQRPhrase = "Bring me there:";
    $stringNotPaid = "NOT PAID";
    $stringPaid = "PAID";
    $stringIssuePhrase = "Thanks & do visit again! :)";
    $stringComment = "Comment:";
    $stringItemTitle= "Item";
    $stringItemQty= "Qty.";
    $stringItemPrice= "Price";
    $stringItemAmount= "Amount";

    /* Strings - Restaurant Receipt */
    $stringItems = "Total Qty:";
    $stringOrderDate = "Date:";
    $stringDineIn = "Dine In:";
    $stringsubtotal="Sub Total";
    $stringgrandtotal="Grand Total";
@endphp
@php
    function menunamewrap2($str){
            $exp = explode(" ",$str);
            $display_string = "";
            foreach($exp AS $word){
             $length = strlen($display_string) + strlen($word);
             if($length >= 23){
              $display_string .= "<br/>".$word." ";
             }else{
              $display_string .= $word." ";
             }
            }
            return $display_string;
    }
@endphp
#### {{ $stringReceiptTitle }}
### {{ $site_name }}
@foreach(explode('<br />', $location_address) as $addr)
    ##### {{$addr}}
@endforeach

||@if ($showLocalInfo)
    #### {{ $location_name }}
    ##### {{ $location_address }}
@endif
-----
@if ($showCustomerInfo)
    {!! $stringName !!} {{ ucwords($customer_name) }} {{ '(M:'.$telephone.')' }}
@endif
@if ($order_type == 'delivery' && $showAddress)
    {!! $stringDeliveryAddress !!}
    {{ $order_address }}
@endif
-----
<|
{!! str_pad($stringOrderDate,5, ' ', STR_PAD_RIGHT) !!} {{ str_pad(date("d/m/Y", strtotime($order_date)),10, ' ', STR_PAD_RIGHT) }} {{ str_pad($order_time,6, ' ', STR_PAD_RIGHT) }} @isset($table_number) {!! str_pad($stringDineIn,8, ' ', STR_PAD_RIGHT) !!}  {{ str_pad($table_number,15, ' ', STR_PAD_RIGHT) }} @endisset

{!! str_pad($stringOrderFor,10, ' ', STR_PAD_RIGHT) !!} {{ str_pad($order_type,11, ' ', STR_PAD_RIGHT) }}   {!! str_pad($stringOrderNumber,9, ' ', STR_PAD_RIGHT) !!} {{ str_pad($order_id,14, ' ', STR_PAD_RIGHT) }}

-----
{{ str_pad($stringItemTitle, $charsPerRow - 25, ' ', STR_PAD_RIGHT) }}  {{ str_pad($stringItemQty,3, ' ', STR_PAD_LEFT) }} {{ str_pad($stringItemPrice,7, ' ', STR_PAD_LEFT) }}  {{ str_pad($stringItemAmount,9, ' ', STR_PAD_LEFT) }}
-----
@php

    function floatcast1($prices){
        $menu_price=str_replace(",","",str_replace("Rs","",$prices));
        $menu_prices=floatval($menu_price);
        return $menu_prices;
    }
    $full_order_menu = array();
    foreach($order_menus as $val){
        $full_order_menu_item = array();
        $iteratePrice = floatcast1($val['menu_price']);
        $iterateSubtotal = floatcast1($val['menu_subtotal']);
        if(array_key_exists($val['menu_name'],$full_order_menu) == TRUE){
            $full_order_menu_item = $full_order_menu[$val['menu_name']];
            $totalQuantity = $full_order_menu[$val['menu_name']]['menu_quantity']+$val['menu_quantity'];
            $iterateSubtotal = floatcast1($val['menu_subtotal']);
            $existingSubTotal = floatcast1($full_order_menu[$val['menu_name']]['menu_subtotal']);
            $subTotal = $iterateSubtotal + $existingSubTotal;
            $full_order_menu_item['menu_quantity']=$totalQuantity;
            $full_order_menu_item['menu_subtotal']=number_format($subTotal,2);
        }else{
            $full_order_menu_item = array("menu_quantity"=>$val['menu_quantity'],"menu_price" => floatcast1($iteratePrice),
    "menu_subtotal"=>number_format($iterateSubtotal,2),"menu_name"=>$val['menu_name'],"menu_options"=>$val['menu_options']);
        }
        $full_order_menu[$val['menu_name']] = $full_order_menu_item;

    }
@endphp
@php $totalItems = 0; @endphp
@foreach ($full_order_menu as $menu) @php $totalItems += $menu['menu_quantity'];
$menuitems = menunamewrap2($menu['menu_name']);
$menuitemarr = explode('<br/>',$menuitems);
$menuarrcnt = count($menuitemarr );
$menuone = $menuitemarr[0]; @endphp
{{ str_pad(substr($menuone, 0, $charsPerRow - 25), $charsPerRow -25 , ' ', STR_PAD_RIGHT) }}  {{ str_pad($menu['menu_quantity'],3, ' ', STR_PAD_LEFT) }}  {{ str_pad(number_format( (float) $menu['menu_price'], 2, '.', ''), 7, ' ', STR_PAD_LEFT) }}  {{ str_pad($menu['menu_subtotal'], 9, ' ', STR_PAD_LEFT) }}
@if ($menuarrcnt > 1)
    @for ($i = 1; $i < $menuarrcnt; $i++)
        @php
            $menupart = $menuitemarr[$i];
        @endphp
        {{ str_pad(substr($menupart, 0, $charsPerRow - 25), $charsPerRow -41 , ' ', STR_PAD_RIGHT) }}
    @endfor
@endif
@if ($menu['menu_options'])@foreach ($menu['menu_options'] as $option)
    +    {{ str_pad('('.$option['menu_option_quantity'].'x '.$option['menu_option_name'].' - '.number_format(floatcast1($option['menu_option_price']),2).')',$charsPerRow-41,' ',STR_PAD_RIGHT) }}
@endforeach
@endif
@endforeach
-----
@php
    $subtot =$grandtot= 0;
    foreach ($order_totals as $total){
    if (in_array($total['order_total_code'], ['subtotal', 'total'])){
    if (in_array($total['order_total_code'], ['subtotal'])) $subtot=$total['order_total_value'];
    if (in_array($total['order_total_code'], ['total'])) $grandtot=$total['order_total_value'];
    } }
@endphp
|>
{{ str_pad($stringItems.' '.$totalItems,$charsPerRow - 22, ' ', STR_PAD_LEFT) }}  {{ str_pad($stringsubtotal,7, ' ', STR_PAD_LEFT) }} {{ str_pad(str_replace('Rs','',$subtot),9 , ' ', STR_PAD_LEFT) }}

@foreach ($order_totals as $total)
    @if (!in_array($total['order_total_code'], ['subtotal', 'total'])) {!! str_pad(substr(strtoupper(substr($total['order_total_title'], 0, strpos($total['order_total_title'], "["))), 0, 11), $charsPerRow - 22, ' ', STR_PAD_LEFT) !!}   {!! str_pad(preg_replace('/[^0-9\.]/', '',$total['order_total_title']).'%',7 , ' ', STR_PAD_LEFT) !!}   {!! str_pad(number_format(floatcast1($total['order_total_value']) , 2), 9, ' ', STR_PAD_LEFT) !!}
    @endif
@endforeach

@foreach ($order_totals as $total)
    @if (in_array($total['order_total_title'], ['tip'])) {!! str_pad($total['order_total_title'].': ',33 , ' ', STR_PAD_LEFT) !!}  {!! str_pad(number_format(floatcast1($total['order_total_value']) , 2), 9, ' ', STR_PAD_LEFT) !!} @endif
@endforeach
-----
@php
    $currency_unicode = "&#8377;";
@endphp
### {{ $stringgrandtotal }}  {!! str_replace('Rs','Rs ',$grandtot) !!}

@if ($showPaymentInfo OR $showIssueMessage)
    -----
    ||
    #### {!! $stringIssuePhrase !!}
    -----
@endif
||
@if ($showPaymentInfo)
    #### {{ $order_payment }}

    ##@if (in_array($order_payment_code, ['none', 'cod'])) {!! $stringNotPaid !!} @else {!! $stringPaid !!} @endif

@endif
