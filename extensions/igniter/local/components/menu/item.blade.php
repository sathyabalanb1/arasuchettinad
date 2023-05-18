<div id="menu{{ $menuItem->menu_id }}" class="menu-item">
    <div class="d-flex flex-row">
        @if ($showMenuImages == 1 && $menuItemObject->hasThumb)
            <div
                class="col-3 p-0 mr-3 menu-item-image align-self-center"
                style="
                    background: url('{{ $menuItem->getThumb() }}') no-repeat center center;
                    background-size: cover;
                    width: {{$menuImageWidth}}px;
                    height: {{$menuImageHeight}}px;
                    ">
            </div>
        @endif

        <div class="menu-content flex-grow-1 mr-3">
            <h6 class="menu-name">{{ $menuItem->menu_name }}</h6>
            <p class="menu-desc text-muted mb-0">
                {!! nl2br($menuItem->menu_description) !!}
            </p>
        </div>
        <div class="menu-detail d-flex justify-content-end col-3 p-0">
            @if ($menuItemObject->specialIsActive)
                <div class="menu-meta text-muted pr-2">
                    <i
                        class="fa fa-star text-warning"
                        title="{!! sprintf(lang('igniter.local::default.text_end_elapsed'), $menuItemObject->specialDaysRemaining) !!}"
                    ></i>
                </div>
            @endif
            <div class="menu-price pr-3">
                @if ($menuItemObject->specialIsActive)
                    <s>{!! currency_format($menuItemObject->menuPriceBeforeSpecial) !!}</s>
                @endif
                <b>{!! $menuItemObject->menuPrice > 0 ? currency_format($menuItemObject->menuPrice) : lang('main::lang.text_free') !!}</b>
            </div>
            @php
                $menu_qty=0;
                foreach ($cart->content()->reverse() as $cartItem) if ($cartItem->id == $menuItem->menu_id) $menu_qty=$cartItem->qty;
            @endphp
            <div class="menu-price pr-3" id="moreqty_{{$menuItem->menu_id}}"
                 @if($menu_qty==0) style="display: none;" @endif>
                @isset ($updateCartItemEventHandler)
                    <div class="menu-button">
                        @partial('@minusbtn', ['menuItem' => $menuItem, 'menuItemObject' => $menuItemObject ])
                    </div>
                @endisset
            </div>
            <div class="menu-price pr-3" id="moreqty_{{$menuItem->menu_id}}_qty"
                 @if($menu_qty==0) style="display: none;" @endif>
                  <span id="menu_qty_{{$menuItem->menu_id}}" class="quantity font-weight-bold">
                   {{ $menu_qty }}
                  </span>
            </div>
            @isset ($updateCartItemEventHandler)
                <div class="menu-button" id="moreqty_{{$menuItem->menu_id}}">
                    @partial('@button', ['menuItem' => $menuItem, 'menuItemObject' => $menuItemObject ])
                </div>
            @endisset
            <input type="hidden" value="<?php echo $menu_qty; ?>" id="menu_loc_{{$menuItem->menu_id}}"/>
        </div>
    </div>
    <div class="d-flex flex-wrap align-items-center allergens">
        @partial('@allergens', ['menuItem' => $menuItem, 'menuItemObject' => $menuItemObject])
    </div>

</div>
