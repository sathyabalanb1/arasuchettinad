@php
    /* Config params */
    $showLocalInfo = false;
    $showOrderInfo = true;
    $showCustomerInfo = true;
    $showAddress = true;
    $showComment = true;
    $showQRCode = false;
    $showPaymentInfo= true;
    $showIssueMessage = false;

    /* Strings - General  */
    $stringTelephone = "Telephone no:";
    $stringReceiptTitle = "RESTAURANT COPY";
    $stringOrderNumber = "ORDER #:";
    $stringOrderFor = "Order for:";
    $stringName = "Name:";
    $stringCustomerPhone = "Phone number:";
    $stringDeliveryAddress = "Delivery address:";
    $stringQRPhrase = "Bring me there:";
    $stringNotPaid = "NOT PAID";
    $stringPaid = "PAID";
    $stringIssuePhrase = "Any issues with your order please contact";
    $stringComment = "Comment:";

    /* Strings - Restaurant Receipt */
    $stringItems = "items";

@endphp

||@if ($showLocalInfo)
    #### {{ $location_name }}
    ##### {{ $location_address }}
    ##### {!! $stringTelephone !!} {{ $location_telephone }}
    ##### {{ $site_url }}

@endif
#### {!! $stringReceiptTitle !!}

@if ($showOrderInfo)
    ### {!! $stringOrderNumber !!} {{ $order_id }}
    ### @isset($table_number)
        {{ $table_number }}
    @endisset
    ##### {!! $stringOrderFor !!} {{ ucfirst($order_type) }}

@endif
<|@if ($showOrderInfo)-----
### {{ $order_date }} {{ $order_time }}
@endif
-----
@if ($showCustomerInfo)
    {!! $stringName !!} {{ ucwords($customer_name) }}
    {!! $stringCustomerPhone !!} {{ $telephone }}
@endif
@if ($order_type == 'delivery' && $showAddress)
    {!! $stringDeliveryAddress !!}
    {{ $order_address }}
@endif
@if (trim($order_comment) != '' && $showComment)
    {!! $stringComment !!}
    {!! $order_comment !!}
@endif
-----
@if ($order_type == 'delivery' && $showQRCode)

    ### {!! $stringQRPhrase !!}
    [qrcode 5,https://www.google.com/maps/dir/?api=1&destination={{ urlencode($order_address) }}]

    -----
@endif
@php
    function floatcast($prices){
        $menu_price=str_replace(",","",str_replace("Rs","",$prices));
        $menu_prices=floatval($menu_price);
        return $menu_prices;
    }
    function menunamewrap($str){
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
    $full_order_menu = array();
    foreach($order_menus as $val){
        $full_order_menu_item = array();
        $iteratePrice = $val['menu_price'];
        $iterateSubtotal = floatcast($val['menu_subtotal']);
        if(array_key_exists($val['menu_name'],$full_order_menu) == TRUE){
            $full_order_menu_item = $full_order_menu[$val['menu_name']];
            $totalQuantity = $full_order_menu[$val['menu_name']]['menu_quantity']+$val['menu_quantity'];
            $iterateSubtotal = floatcast($val['menu_subtotal']);
            $existingSubTotal = floatcast($full_order_menu[$val['menu_name']]['menu_subtotal']);
            $subTotal = $iterateSubtotal + $existingSubTotal;
            $full_order_menu_item['menu_quantity']=$totalQuantity;
            $full_order_menu_item['menu_subtotal']='Rs '.number_format($subTotal,2);
        }else{
            $full_order_menu_item = array("menu_quantity"=>$val['menu_quantity'],"menu_price" => $iteratePrice,"menu_subtotal"=>"Rs ".number_format($iterateSubtotal,2),"menu_name"=>$val['menu_name'],
    "menu_options"=>$val['menu_options']
        );
        }
        $full_order_menu[$val['menu_name']] = $full_order_menu_item;

    }
@endphp
@php $totalItems = 0; @endphp
@foreach ($full_order_menu as $menu) @php $totalItems += $menu['menu_quantity'];
$menuitems = menunamewrap($menu['menu_name']);
$menuitemarr = explode('<br/>',$menuitems);
$menuarrcnt = count($menuitemarr );
$menuone = $menuitemarr[0];@endphp
#### {{ str_pad(substr($menu['menu_quantity'].'x '.$menuone, 0, $charsPerRow - 16), $charsPerRow - 16, ' ', STR_PAD_RIGHT) }}    {{ str_pad($menu['menu_subtotal'], 12, ' ', STR_PAD_LEFT) }}
@if ($menuarrcnt > 1)
    @for ($i = 1; $i < $menuarrcnt; $i++)
        @php
            $menupart = $menuitemarr[$i];
            $strlen = strlen($menupart)+3;
        @endphp
        {{ str_pad($menupart,$strlen," ",STR_PAD_LEFT) }}
    @endfor
@endif
@if ($menu['menu_options'])@foreach ($menu['menu_options'] as $option)
    +    {{ str_pad('('.$option['menu_option_quantity'].'x '.$option['menu_option_name'].' - '.number_format(floatcast($option['menu_option_price']),2).')',$charsPerRow-41,' ',STR_PAD_RIGHT) }}
@endforeach
@endif
@endforeach

{{ $totalItems }} {!! $stringItems !!}
@foreach ($order_totals as $total)
    @if (in_array($total['order_total_code'], ['subtotal', 'total']))#### @endif{!! str_pad(substr(strtoupper($total['order_total_title']), 0, $charsPerRow - 16), $charsPerRow - 16, ' ', STR_PAD_RIGHT) !!}    {!! str_pad(str_replace("Rs","Rs ",$total['order_total_value']), 12, ' ', STR_PAD_LEFT) !!}
@endforeach

@foreach ($order_totals as $total)
    @if (in_array($total['order_total_title'], ['tip']))### {!! $total['order_total_title'].': '.$total['order_total_value'] !!}@endif
@endforeach
@if ($showPaymentInfo OR $showIssueMessage)
    -----
@endif
||
@if ($showPaymentInfo)
    #### {{ $order_payment }}

    ##@if (in_array($order_payment_code, ['none', 'cod'])) {!! $stringNotPaid !!} @else {!! $stringPaid !!} @endif

@endif
@if ($showIssueMessage )
    {!! $stringIssuePhrase !!}
    {{ $location_telephone }}
@endif
