<div class="menu-items">

    @forelse ($menuItems as $menuItemObject)
        @partial('@item', ['menuItem' => $menuItemObject->model, 'menuItemObject' => $menuItemObject])
    @empty
        <p>@lang('igniter.local::default.text_empty')</p>
    @endforelse
</div>
<script>
    function BtnRemoveItem(obj, menuid, qty) {
        var rowid = '';
        $('.name-image').each(function () {
            if (menuid == $(this).attr('data-menu-id')) {
                rowid = $(this).attr('data-row-id');
                $('#moreqty_' + menuid).find('button').attr('data-request-data', "rowId: '" + rowid + "',menuId: '" + menuid + "', quantity: '" + qty + "'");
                var inputValue = $('#menu_loc_' + menuid).val();
                var update = --inputValue;
                if (update > 0) {
                    $("#moreqty_" + menuid).css("display", "block");
                    $("#moreqty_" + menuid + "_qty").css("display", "block");
                } else {
                    $("#moreqty_" + menuid).css("display", "none");
                    $("#moreqty_" + menuid + "_qty").css("display", "none");
                }
                $("#menu_qty_" + menuid).html(update);
                $("#menu_loc_" + menuid).val(update);
                return;
            }
        });
    }

    function BtnRemove(rowId, menuid) {
        $('#moreqty_' + menuid).find('button').attr('data-request-data', "rowId: '" + rowId + "',menuId: '" + menuid + "', quantity: '1'");
        var inputValue = $('#menu_loc_' + menuid).val();
        var update = --inputValue;
        if (update > 0) {
            $("#moreqty_" + menuid).css("display", "block");
            $("#moreqty_" + menuid + "_qty").css("display", "block");
        } else {
            $("#moreqty_" + menuid).css("display", "none");
            $("#moreqty_" + menuid + "_qty").css("display", "none");
        }
        $("#menu_qty_" + menuid).html(update);
        $("#menu_loc_" + menuid).val(update);
    }

    function onUpdateCard(menuid) {
        var lastval = $("#menu_loc_" + menuid).val();
        var update = ++lastval;
        if (update > 0) {
            $("#moreqty_" + menuid).css("display", "block");
            $("#moreqty_" + menuid + "_qty").css("display", "block");
        } else {
            $("#moreqty_" + menuid).css("display", "none");
            $("#moreqty_" + menuid + "_qty").css("display", "none");
        }
        $("#menu_qty_" + menuid).html(update);
        $("#menu_loc_" + menuid).val(update);
    }
</script>
