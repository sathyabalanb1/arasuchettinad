
<button onclick="BtnRemoveItem(this,{{ $menuItem->menu_id }},{{ $menuItem->minimum_qty }})"
        class="btn btn-light btn-sm btn-add-to-cart btn-cart{{ $menuItemObject->mealtimeIsNotAvailable ? ' disabled' : '' }}"
        @if (!$menuItemObject->mealtimeIsNotAvailable)
            @if ($menuItemObject->hasOptions)
                data-cart-control="load-item"
        data-menu-id="{{ $menuItem->menu_id }}"
        data-quantity="{{ $menuItem->minimum_qty }}"
        @else
            data-request-data="rowId: '',menuId: '{{ $menuItem->menu_id }}', quantity: '{{ $menuItem->minimum_qty }}'"
            data-request="{{ $removeCartItemEventHandler }}"
        data-replace-loading="fa fa-spinner fa-spin"
        @endif
        @else
            title="{{ implode("\r\n", $menuItemObject->mealtimeTitles) }}"
    @endif
>

    <i class="fa fa-{{ $menuItemObject->mealtimeIsNotAvailable ? 'clock-o' : 'minus' }}"></i>
</button>
