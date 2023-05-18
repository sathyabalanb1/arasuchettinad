@php
    /* Config params */
    $showLocalInfo = false;
    $showOrderInfo = true;
    $showCustomerInfo = true;
    $showAddress = true;
    $showComment = true;
    $showQRCode = false;
    $showPaymentInfo= false;
    $showIssueMessage = false;

    /* Strings - General  */
    $stringTelephone = "Telephone no:";
    $stringReceiptTitle = "KITCHEN COPY";
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
@if ($showCustomerInfo)
    #### {!! $stringName !!} {{ ucwords($customer_name) }}
    #### {!! $stringCustomerPhone !!} {{ $telephone }}
@endif
@if ($order_type == 'delivery' AND $showAddress)
    {!! $stringDeliveryAddress !!}
    {{ $order_address }}
@endif
@if (trim($order_comment) != '' AND $showComment)
    {!! $stringComment !!}
    {!! $order_comment !!}
@endif
@if ($order_type == 'delivery' AND $showQRCode)

    -----
    ### {!! $stringQRPhrase !!}
    [qrcode 5,https://www.google.com/maps/dir/?api=1&destination={{ urlencode($order_address) }}]
@endif
-----

<|@php $categoryName = ''; @endphp
@foreach ($order_menus as $menu)
    ##### {{ $menu['menu_quantity'].'x '.$menu['menu_name'] }}
    @if ($menu['menu_options'])@foreach ($menu['menu_options'] as $option)
        ##### +    {{ $option['menu_option_quantity'].'x '.$option['menu_option_name'] }}
    @endforeach @endif @if ($menu['menu_comment']){!! $menu['menu_comment'] !!}
    @endif
@endforeach

@if ($showPaymentInfo OR $showIssueMessage)
    -----
@endif

@if ($showPaymentInfo)
    #### {{ $order_payment }}
    ||
    ##@if (in_array($order_payment_code, ['none', 'cod'])) {!! $stringNotPaid !!} @else {!! $stringPaid !!} @endif

@endif
@if ($showIssueMessage)
    {!! $stringIssuePhrase !!}
    {{ $location_telephone }}
@endif
