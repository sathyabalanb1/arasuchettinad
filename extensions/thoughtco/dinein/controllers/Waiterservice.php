<?php

namespace Thoughtco\Dinein\Controllers;

use AdminAuth;
use AdminMenu;
use Admin\Facades\AdminLocation;
use Admin\Models\Orders_model;
use Admin\Models\Tables_model;
use Admin\Widgets\Toolbar;
use ApplicationException;
use Carbon\Carbon;
use DB;
use Template;
use Admin\Models\Payments_model;
use Thoughtco\Printer\Models\Printer;

class Waiterservice extends \Admin\Classes\AdminController
{
    public $implement = [
        'Admin\Actions\ListController',
    ];

    public $listConfig = [
        'list' => [
            'model' => 'Admin\Models\Tables_model',
            'title' => 'lang:thoughtco.dinein::default.text_list_title',
            'emptyMessage' => 'lang:thoughtco.dinein::default.text_empty',
            'defaultSort' => ['id', 'DESC'],
            'configFile' => 'waiterservice',
        ],
    ];

    protected $requiredPermissions = 'Thoughtco.Dinein.*';

    public function __construct()
    {
        parent::__construct();
        AdminMenu::setContext('sales', 'waiter');
    }

    public function index()
    {
        $this->asExtension('ListController')->index();
    }

    public function close($context, $id)
    {
        if (!AdminAuth::user()->hasPermission('Thoughtco.Dinein.WaiterService'))
            throw new ApplicationException('Permission denied');

        Template::setTitle(__('lang:thoughtco.dinein::default.text_list_title'));

        $table = Tables_model::find($id);

        $payment = Payments_model::listDropdownOptions();

        $open_orders = $this->openOrdersQuery($id);

        if (!$open_orders->count() OR !$table)
            return redirect(admin_url('thoughtco/dinein/waiterservice'));

        $this->vars['table'] = $table;
        $this->vars['menuItems'] = [];
        $this->vars['totalItems'] = 0;
        $this->vars['orderTotal'] = 0;
        $this->vars['orderTotals'] = [];
        $this->vars['payments'] = $payment;
        $this->vars['print_url'] = 'thoughtco/dinein/waiterservice/print/'.$id;

        foreach ($open_orders as $order) {

            foreach ($order->getOrderMenusWithOptions() as $menu) {

                $menu_options = $menu->menu_options->map(function($option) {
                    return [
                        'menu_id' => $option->menu_id,
                        'option_name' => $option->order_option_name,
                        'option_price' => $option->order_option_price,
                        'menu_option_id' => $option->order_menu_option_id,
                        'option_value_id' => $option->menu_option_value_id,
                        'option_quantity' => $option->quantity,
                        'option_category' => $option->order_option_category,
                    ];
                });

                $key = md5($menu->menu_id.$menu_options->toJson());

                if (isset($this->vars['menuItems'][$key])){
                    $this->vars['menuItems'][$key]->quantity += $menu->quantity;
                    $this->vars['menuItems'][$key]->subtotal += $menu->subtotal;
                } else {
                    $this->vars['menuItems'][$key] = $menu;
                }

                $this->vars['totalItems'] += $menu->quantity;
            }

            foreach ($order->getOrderTotals() as $total) {

                $found = false;

                foreach ($this->vars['orderTotals'] as $order_total) {
                    if ($total->code == $order_total->code) {
                        $order_total->value += $total->value;
                        $found = true;
                    }
                }

                if (!$found)
                    $this->vars['orderTotals'][] = $total;
            }

        }

        $order_status = $order->status_id;
        $toolbar_config = [];
        foreach ($this->vars['orderTotals'] as $total)
            if ($total->code == 'total')
                $this->vars['orderTotal'] = $total->value;
        $toolbar_config['buttons']['back'] = ['label' => 'lang:admin::lang.button_icon_back', 'class' => 'btn btn-default', 'href' => 'thoughtco/dinein/waiterservice'];
        if($order_status == 10 || $order_status == 5 || $order_status == 9) {
            $toolbar_config['buttons']['saveClose'] = ['label' => 'lang:thoughtco.dinein::default.btn_close_table',
                'class' => 'btn btn-primary',
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#waiter-modal',];
        }
        else
        {
            $toolbar_config['buttons']['makePay'] = ['label' => 'lang:thoughtco.dinein::default.btn_make_payment_table',
                'class' => 'btn btn-primary',
                'data-bs-toggle' => 'modal',
                'data-bs-target' => '#dinein-payment-modal',];
        }
        $toolbar_config['buttons']['printall'] = [
            'label' => 'lang:admin::lang.button_print',
            'partial' => 'print/toolbar_print_button',
            'printerList' => $this->getPrinterList(),
            'class' => 'btn btn-primary',
            'data-request' => 'onSave',
            'data-progress-indicator' => 'admin::lang.text_saving',
        ];
      /*  if (!\System\Classes\ExtensionManager::instance()->isDisabled('thoughtco.printer')) {

            $toolbar_config['buttons']['print'] = [
                'label' => 'lang:thoughtco.dinein::default.btn_print',
                'class' => 'btn btn-secondary',
                'href' => admin_url('thoughtco/dinein/waiterservice/print/'.$id),
            ];

        }*/

        $this->vars['toolbarWidget'] = $this->makeWidget('Admin\Widgets\Toolbar', $toolbar_config);

        return $this->makeView('waiterservice/close');
    }

    public function getPrinterList(){
        $printerList = Printer::where(['is_enabled' => true])
            ->get()
            ->map(function($printer) {
                return (object)[
                    'id' => $printer->id,
                    'location' => $printer->location->location_id,
                    'label' => $printer->label,
                ];
            });
        return $printerList;
    }

    public function close_onClose($context, $id)
    {
        if (!AdminAuth::user()->hasPermission('Thoughtco.Dinein.WaiterService'))
            throw new ApplicationException('Permission denied');

        $completed_statuses = setting('completed_order_status');

        $open_orders = $this->openOrdersQuery($id);

        $first_order_id = 0;
        $order_totals = [];
        foreach ($open_orders as $idx => $order) {

            foreach ($order->getOrderTotals() as $total) {

                $found = false;

                foreach ($order_totals as $order_total) {
                    if ($total->code == $order_total->code) {
                        $order_total->value += $total->value;
                        $found = true;
                    }
                }

                if (!$found)
                    $order_totals[] = $total;
            }

            if ($idx == 0) {
                $first_order_id = $order->order_id;
            } else {

                DB::table("order_menus")
                    ->where('order_id', $order->order_id)
                    ->update(['order_id' => $first_order_id ]);

                DB::table("order_menu_options")
                    ->where('order_id', $order->order_id)
                    ->update(['order_id' => $first_order_id ]);

                $order->delete();

            }

        }

        $order = Orders_model::find($first_order_id);
        $order->table_count = request()->input('table_count', '');
        $order->table_closed_at = Carbon::now();
        $order->save();

        $order->addOrderTotals(json_decode(json_encode($order_totals), true));
        $order->updateOrderStatus(array_shift($completed_statuses));

        return redirect(admin_url('thoughtco/dinein/waiterservice'));

    }

    public function print($context, $id)
    {
        if (!AdminAuth::user()->hasPermission('Thoughtco.Dinein.WaiterService'))
            throw new ApplicationException('Permission denied');

        Template::setTitle(__('lang:thoughtco.dinein::default.text_list_title'));

        $table = Tables_model::find($id);

        $open_orders = $this->openOrdersQuery($id);

        if (!$open_orders->count() OR !$table)
            return redirect(admin_url('thoughtco/dinein/waiterservice'));

        $order_menus = collect([]);
        $order_menu_options = collect([]);
        $order_totals = [];
        $first_order_id = 0;
        foreach ($open_orders as $idx => $order) {

            foreach ($order->getOrderTotals() as $total) {

                $found = false;

                foreach ($order_totals as $order_total) {
                    if ($total->code == $order_total->code) {
                        $order_total->value += $total->value;
                        $found = true;
                    }
                }

                if (!$found)
                    $order_totals[] = $total;
            }

            if ($idx == 0) {
                $first_order_id = $order->order_id;
            }

            $order_menus = $order_menus->merge($order->getOrderMenus());
            $order_menu_options = $order_menu_options->merge($order->getOrderMenuOptions());

        }

        $order->printer_menus = $order_menus;
        $order->printer_menu_options = $order_menu_options;
        $order->printer_totals = collect($order_totals);

        // hand off to print docket so we dont need to recreate logic around dockets
        $print_docket = new \Thoughtco\Printer\Controllers\Printdocket;

        $js = '';
        foreach (\PrintHelper::getJavascript() as $jsfile)
            $js .= '<script type="text/javascript" src="'.config('app.url').$jsfile.'"></script>';

        return $js.$print_docket->renderPrintdocket($order);
    }
    public function close_onWsPay($context, $id)
    {

        if (!AdminAuth::user()->hasPermission('Thoughtco.Dinein.WaiterService'))
            throw new ApplicationException('Permission denied');

        Template::setTitle(__('lang:thoughtco.dinein::default.text_list_title'));

        $table = Tables_model::find($id);

        $open_orders = $this->openOrdersQuery($id);
        if( $pay = request()->input('payment_mode')  == ''){
            return redirect(admin_url('thoughtco/dinein/waiterservice/close/'.$id));
        }
        if (!$open_orders->count() OR !$table)
            return redirect(admin_url('thoughtco/dinein/waiterservice'));
        foreach ($open_orders as $idx => $order) {
            $order = Orders_model::find($order->order_id);
            $order->payment = request()->input('payment_mode','');
            $order->status_id = 10;
            $order->table_closed_at = Carbon::now();
            $order->save();
        }
        return redirect(admin_url('thoughtco/dinein/waiterservice/close/'.$id));
    }

    private function openOrdersQuery($id)
    {
        $location_id = AdminLocation::getId() ?? AdminLocation::getDefaultLocation();

        return Orders_model::where([
            'location_id' => $location_id,
            'table_number' => $id,
            'order_type' => 'waiter',
        ])
            ->whereNotIn('status_id', setting('completed_order_status'))
            ->get();
    }

    public function listExtendQuery($query)
    {
        if ($locationId = $this->getLocationId()){
            $query->whereHasLocation($locationId);
        }
    }
}
