<?php
namespace Diligentsquad\Gst;

use System\Classes\BaseExtension;

/**
 * gst Extension Information File
 */
class Extension extends BaseExtension
{
    public function extensionMeta()
    {
        return [
            'name' => 'Goods and Services Tax (GST)',
            'author' => 'Diligentsquad',
            'description' => 'Add Goods and Services (GST) to the Order',
            'icon' => 'fa fa-money',
        ];
    }

    /**
     * Register method, called when the extension is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {

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
//            'Diligentsquad\Gst\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any admin permissions used by this extension.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'Diligentsquad.Gst.ManageSettings' => [
                'description' => 'Manage GST settings',
                'group' => 'module',
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'GST Settings',
                'description' => 'Manage GST tax settings.',
                'icon' => 'fa fa-money',
                'model' => \Diligentsquad\Gst\Models\GstSettings::class,
                'permissions' => ['Diligentsquad.Gst.ManageSettings'],
            ],
        ];
    }
    public function registerCartConditions()
    {
        return [
            \Diligentsquad\Gst\CartConditions\CgstTax::class => [
                'name' => 'cgsttax',
                'label' => 'lang:diligentsquad.gst::default.text_cgst',
                'description' => 'lang:igniter.cart::default.help_tax_condition',
            ],
            \Diligentsquad\Gst\CartConditions\SgstTax::class => [
                'name' => 'sgsttax',
                'label' => 'lang:diligentsquad.gst::default.text_sgst',
                'description' => 'lang:igniter.cart::default.help_tax_condition',
            ],
        ];
    }

}
