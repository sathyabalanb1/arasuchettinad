<?php namespace Diligentsquad\Touchfreewaitercustomization;

use Admin\Facades\AdminAuth;
use Illuminate\Support\Facades\Event;
use System\Classes\BaseExtension;
use Admin\Models\Orders_model;
use Admin\Models\Tables_model;

/**
 * customizationext Extension Information File
 */
class Extension extends BaseExtension
{
    /**
     * Register method, called when the extension is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    public function extensionMeta()
    {
        return [
            'name' => 'TouchFreeWaiter Customized Features',
            'author' => 'Diligentsquad',
            'description' => 'Provides customization done on other modules for TouchFreeWaiter Site Requirements',
            'icon' => 'fa fa-wrench',
        ];
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {
	    $this->extendTableNumberColumns();
	    $this->addTableNumberBeforeSaveOrder();
    }

    protected function extendTableNumberColumns(){
        Event::listen('admin.list.extendColumns', function (&$widget) {
        if ($widget->getController() instanceof \Admin\Controllers\Orders){
                $widget->addColumns(['table name' => [
                    'label' => 'lang:diligentsquad.touchfreewaitercustomization::default.column_table_name',
                    'type' => 'text',
                    'sortable' => TRUE,
                    'relation' => 'table_value',
                    'select' => 'table_name',
                ]]);
            }
        });
    }

    protected function addTableNumberBeforeSaveOrder(){
	    Event::listen('igniter.checkout.beforeSaveOrder', function(Orders_model &$order, &$data ){
		if(isset($data['table_number'])) {
			$order->setAttribute('table_number', $data['table_number']);
                }
	});
   }

    /**
     * Registers any front-end components implemented in this extension.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
// Remove this line and uncomment the line below to activate
//            'Diligentsquad\Customizationext\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any admin permissions used by this extension.
     *
     * @return array
     */
    public function registerPermissions()
    {
// Remove this line and uncomment block to activate
        return [
//            'Diligentsquad.Customizationext.SomePermission' => [
//                'description' => 'Some permission',
//                'group' => 'module',
//            ],
        ];
    }
}
